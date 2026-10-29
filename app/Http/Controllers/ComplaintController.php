<?php

namespace App\Http\Controllers;

use App\Events\ComplaintSubmitted;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintEvidence;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $complaints = Complaint::query()
            ->whereBelongsTo($request->user('resident'), 'resident')
            ->with(['assignedStaff', 'activities.actor'])
            ->latest()
            ->paginate(10);

        return view('resident.complaints.index', compact('complaints'));
    }

    public function create(Request $request): View
    {
        $role = $request->user()->role;

        return view('resident.complaints.create', [
            'role' => $role,
            'residents' => in_array($role, ['staff', 'admin'], true)
                ? User::query()->where('role', 'resident')->where('is_active', true)->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $uploadedPaths = [];
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        try {
            $complaint = DB::transaction(function () use ($request, $actor, &$uploadedPaths): Complaint {
                $residentId = $actor->role === 'resident'
                    ? $actor->id
                    : (int) $request->validated('resident_id');

                $status = $request->input('status', 'pending');
                $assignedStaffId = $request->filled('assigned_staff_id') ? (int) $request->validated('assigned_staff_id') : null;

                $complaint = Complaint::create($request->safe()->only([
                    'title',
                    'category',
                    'respondents',
                    'case_subject',
                    'incident_date',
                    'description',
                    'location',
                    'relief_sought',
                    'witnesses',
                    'approved_at',
                    'staff_responded_at',
                    'resolved_at',
                    'closed_at',
                    'receipt_confirmed_at',
                    'summons_ready_at',
                ]) + [
                    'reference' => 'BRGY-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                    'resident_id' => $residentId,
                    'assigned_staff_id' => $assignedStaffId,
                    'status' => $status,
                ]);

                foreach ($request->file('attachments', []) as $file) {
                    $path = $file->store('complaints/'.$complaint->id, 'private');
                    $uploadedPaths[] = $path;

                    ComplaintEvidence::create([
                        'complaint_id' => $complaint->id,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);
                }

                ComplaintActivity::create([
                    'complaint_id' => $complaint->id,
                    'actor_id' => $actor->id,
                    'action' => 'submitted',
                    'to_status' => $status,
                    'description' => $actor->role === 'resident'
                        ? 'Complaint submitted by resident.'
                        : 'Complaint encoded by '.$actor->role.'.',
                ]);

                if ($status !== 'pending' && $status !== 'closed') {
                    ComplaintActivity::create([
                        'complaint_id' => $complaint->id,
                        'actor_id' => $actor->id,
                        'action' => 'status_restored',
                        'to_status' => $status,
                        'description' => 'Legacy complaint restored to '.$status.' status during re-encoding.',
                    ]);
                }

                return $complaint;
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($uploadedPaths);

            throw $exception;
        }

        $recipients = User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->where('is_active', true)
            ->get();

        Notification::send($recipients, new ComplaintAlert($complaint, 'A new complaint has been submitted.'));
        event(new ComplaintSubmitted($complaint));

        $response = $actor->role === 'resident'
            ? 'Your complaint has been submitted.'
            : 'Complaint encoded successfully.';

        return redirect()->route($actor->role.'.complaints.show', $complaint)->with('status', $response);
    }

    public function show(Request $request, Complaint $complaint): View
    {
        $this->authorize('view', $complaint);
        $canViewEvidence = in_array($request->user()->role, ['staff', 'admin'], true);
        $complaint->load(['resident', 'assignedStaff', 'activities.actor', 'comments.author']);

        if ($canViewEvidence) {
            $complaint->load(['evidence', 'comments.evidence', 'summonsServices.recordedBy']);
        }

        $complaint->load(['meetings.recordedBy']);

        return view('complaints.show', [
            'complaint' => $complaint,
            'canViewEvidence' => $canViewEvidence,
            'staffMembers' => in_array($request->user()->role, ['staff', 'admin'], true)
                ? User::query()->where('role', 'staff')->where('is_active', true)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
