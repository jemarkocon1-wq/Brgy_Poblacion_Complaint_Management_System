<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\BarangayInformation;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceController extends Controller
{
    public function dashboard(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['search'] ?? '');
        $recentComplaints = Complaint::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            }))
            ->with('resident', 'assignedStaff')
            ->latest();

        return view('admin.dashboard', [
            'counts' => [
                'pending' => Complaint::where('status', 'pending')->count(),
                'in_progress' => Complaint::where('status', 'approved')->count(),
                'resolved' => Complaint::where('status', 'resolved')->count(),
                'unsettled' => Complaint::where('status', 'unsettled')->count(),
                'closed' => Complaint::where('status', 'closed')->count(),
                'residents' => User::where('role', 'resident')->count(),
                'staff' => User::where('role', 'staff')->count(),
            ],
            'recentComplaints' => $search === ''
                ? $recentComplaints->limit(8)->get()
                : $recentComplaints->paginate(8)->withQueryString(),
            'recentActivities' => ComplaintActivity::with('actor', 'complaint')->latest()->limit(8)->get(),
            'search' => $search,
        ]);
    }

    public function complaints(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,resolved,unsettled,closed'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($filters['search'] ?? '');

        return view('admin.complaints', [
            'complaints' => Complaint::query()
                ->with(['resident', 'assignedStaff'])
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('resident', fn ($residentQuery) => $residentQuery->where('name', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
            'status' => $filters['status'] ?? '',
        ]);
    }

    public function users(Request $request): View
    {
        $filters = $request->validate([
            'role' => ['nullable', 'in:admin,resident,staff'],
            'search' => ['nullable', 'string', 'max:80'],
        ]);

        $users = User::query()
            ->whereIn('role', ['admin', 'resident', 'staff'])
            ->withCount(['complaints', 'assignedComplaints'])
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', ['users' => $users, 'filters' => $filters]);
    }

    public function storeUser(ManageUserRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'phone', 'role', 'password']) + [
            'is_active' => true,
        ]);

        if (in_array($user->role, ['admin', 'staff'], true)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $this->log($request, 'user.created', "Created {$user->role} account {$user->email}.");

        return back()->with('status', 'User account created.');
    }

    public function updateUser(ManageUserRequest $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 403);
        $data = $request->safe()->only(['name', 'email', 'phone', 'role']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);

        $this->log($request, 'user.updated', "Updated account {$user->email}.");

        return back()->with('status', 'User account updated.');
    }

    public function toggleUser(Request $request, User $user): RedirectResponse
    {
        $request->validate(['is_active' => ['required', 'boolean']]);
        abort_if($user->role === 'admin' || $user->id === Auth::guard('admin')->id(), 403);
        $user->update(['is_active' => $request->boolean('is_active')]);
        $this->log($request, 'user.status', ($user->is_active ? 'Enabled ' : 'Disabled ').$user->email.'.');

        return back()->with('status', 'User access updated.');
    }

    public function reports(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,resolved,unsettled,closed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $complaints = Complaint::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, string $startDate) => $query->whereDate('created_at', '>=', $startDate))
            ->when($filters['end_date'] ?? null, fn ($query, string $endDate) => $query->whereDate('created_at', '<=', $endDate));

        return view('reports.index', [
            'title' => 'Barangay reports',
            'filters' => $filters,
            'complaints' => $complaints->clone()->with(['resident', 'assignedStaff'])->latest()->paginate(15)->withQueryString(),
            'counts' => $complaints->clone()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'categories' => $complaints->clone()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category'),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,resolved,unsettled,closed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Reference', 'Title', 'Complainant', 'Respondent(s)', 'Witnesses', 'Category', 'Status', 'Assigned staff', 'Submitted at']);

            Complaint::query()
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->when($filters['start_date'] ?? null, fn ($query, string $startDate) => $query->whereDate('created_at', '>=', $startDate))
                ->when($filters['end_date'] ?? null, fn ($query, string $endDate) => $query->whereDate('created_at', '<=', $endDate))
                ->with(['resident', 'assignedStaff'])
                ->orderBy('id')
                ->chunk(250, function ($complaints) use ($output): void {
                    foreach ($complaints as $complaint) {
                        fputcsv($output, [
                            $complaint->reference,
                            $complaint->title,
                            $complaint->resident?->name,
                            $complaint->respondents ?: '—',
                            $complaint->witnesses ?: '—',
                            str_replace('_', ' ', ucfirst($complaint->category)),
                            $complaint->statusLabel(),
                            $complaint->assignedStaff?->name ?? '—',
                            $complaint->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($output);
        }, 'barangay-complaints.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,resolved,unsettled,closed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $complaints = Complaint::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, string $startDate) => $query->whereDate('created_at', '>=', $startDate))
            ->when($filters['end_date'] ?? null, fn ($query, string $endDate) => $query->whereDate('created_at', '<=', $endDate))
            ->with(['resident', 'assignedStaff'])
            ->latest()
            ->get();

        return Pdf::loadView('reports.pdf', [
            'complaints' => $complaints,
            'generatedAt' => now(),
        ])->download('barangay-complaints.pdf');
    }

    public function information(): View
    {
        $information = BarangayInformation::query()->firstOrCreate([], ['name' => 'Barangay Poblacion']);

        return view('admin.information', compact('information'));
    }

    public function updateInformation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'office_hours' => ['nullable', 'string', 'max:1000'],
        ]);
        $information = BarangayInformation::query()->firstOrCreate([], ['name' => 'Barangay Poblacion']);
        $information->update($validated);
        $this->log($request, 'barangay.updated', 'Updated barangay contact information.');

        return back()->with('status', 'Barangay information saved.');
    }

    public function activity(Request $request): View
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:80'],
            'search' => ['nullable', 'string', 'max:80'],
        ]);

        $activities = ComplaintActivity::query()
            ->with(['actor', 'complaint'])
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', 'like', "%{$action}%"))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhereHas('actor', fn ($actorQuery) => $actorQuery->where('name', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity', [
            'activities' => $activities,
            'filters' => $filters,
            'actions' => ComplaintActivity::query()->distinct()->pluck('action')->filter()->sort()->values(),
        ]);
    }

    public function settings(): View
    {
        return view('admin.settings', ['profile' => auth('admin')->user()]);
    }

    public function updateSettings(UpdateProfileRequest $request): RedirectResponse
    {
        $admin = $request->user('admin');
        $admin->update($request->safe()->only(['name', 'email', 'phone']));

        if ($request->hasFile('avatar')) {
            if ($admin->avatar_path) {
                Storage::disk('public')->delete($admin->avatar_path);
            }

            $admin->forceFill([
                'avatar_path' => $request->file('avatar')->store('avatars', 'public'),
            ]);
            $admin->save();
        }

        $this->log($request, 'admin.profile', 'Updated administrator profile.');

        return back()->with('status', 'Settings saved.');
    }

    private function log(Request $request, string $action, string $description): void
    {
        ComplaintActivity::create([
            'actor_id' => $request->user('admin')->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
