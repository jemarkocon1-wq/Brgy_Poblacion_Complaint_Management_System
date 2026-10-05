<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\ComplaintActivity;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function dashboard(): View
    {
        return view('staff.dashboard', [
            'counts' => [
                'pending' => Complaint::where('status', 'pending')->count(),
                'in_progress' => Complaint::where('status', 'approved')->count(),
                'resolved' => Complaint::where('status', 'resolved')->count(),
                'assigned' => Complaint::where('assigned_staff_id', auth('staff')->id())->where('status', 'approved')->count(),
            ],
            'recentComplaints' => Complaint::with('resident', 'assignedStaff')->latest()->limit(8)->get(),
        ]);
    }

    public function complaints(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,resolved'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $complaints = Complaint::query()
            ->with(['resident', 'assignedStaff'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('reference', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('staff.complaints', compact('complaints'));
    }

    public function assigned(): View
    {
        $complaints = Complaint::query()
            ->where('assigned_staff_id', auth('staff')->id())
            ->with(['resident', 'assignedStaff'])
            ->latest()
            ->paginate(12);

        return view('staff.complaints', ['complaints' => $complaints, 'assignedOnly' => true]);
    }

    public function residents(): View
    {
        $residents = User::query()
            ->where('role', 'resident')
            ->withCount('complaints')
            ->orderBy('name')
            ->paginate(15);

        return view('staff.residents', compact('residents'));
    }

    public function reports(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,resolved'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $complaints = Complaint::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, string $startDate) => $query->whereDate('created_at', '>=', $startDate))
            ->when($filters['end_date'] ?? null, fn ($query, string $endDate) => $query->whereDate('created_at', '<=', $endDate));

        return view('reports.index', [
            'title' => 'Staff reports',
            'filters' => $filters,
            'counts' => $complaints->clone()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'categories' => $complaints->clone()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category'),
        ]);
    }

    public function settings(): View
    {
        return view('profile.edit', ['profile' => auth('staff')->user()]);
    }

    public function updateSettings(UpdateProfileRequest $request): RedirectResponse
    {
        $staff = $request->user('staff');
        $staff->update($request->safe()->only(['name', 'email', 'phone']));
        ComplaintActivity::create([
            'actor_id' => $request->user('staff')->id,
            'action' => 'staff.profile',
            'description' => 'Updated staff profile.',
        ]);

        return back()->with('status', 'Settings saved.');
    }
}
