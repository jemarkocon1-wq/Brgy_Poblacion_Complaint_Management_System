<?php

namespace App\Http\Controllers;

use App\Events\ComplaintStatusChanged;
use App\Http\Requests\TransitionComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ComplaintWorkflowController extends Controller
{
    public function approve(TransitionComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        return $this->transition($request, $complaint, 'approved');
    }

    public function reject(TransitionComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        return $this->transition($request, $complaint, 'rejected');
    }

    public function resolve(TransitionComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        return $this->transition($request, $complaint, 'resolved');
    }

    private function transition(TransitionComplaintRequest $request, Complaint $complaint, string $targetStatus): RedirectResponse
    {
        $actor = $request->user();
        $previousStatus = $targetStatus === 'resolved' ? 'approved' : 'pending';
        $rejectionReason = $request->validated('reason');
        $assignedStaffId = $request->validated('assigned_staff_id');
        $conflict = DB::transaction(function () use ($complaint, $actor, $targetStatus, $previousStatus, $rejectionReason, $assignedStaffId): ?array {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
            $expectedStatus = $previousStatus;

            if ($lockedComplaint->status !== $expectedStatus) {
                if ($targetStatus === 'resolved' && $lockedComplaint->status === 'pending') {
                    return ['not_approved' => true, 'handled_by' => null];
                }

                return [
                    'not_approved' => false,
                    'handled_by' => $lockedComplaint->activities()->with('actor')->latest('id')->first()?->actor?->name ?? 'another staff member',
                ];
            }

            $attributes = [
                'status' => $targetStatus,
                'rejection_reason' => $targetStatus === 'rejected' ? $rejectionReason : null,
            ];

            if ($targetStatus === 'approved') {
                $attributes['assigned_staff_id'] = $assignedStaffId;
            }

            if ($targetStatus === 'resolved') {
                $attributes['resolved_at'] = now();
            }

            $updated = Complaint::query()
                ->whereKey($lockedComplaint->id)
                ->where('status', $expectedStatus)
                ->update($attributes);

            if ($updated !== 1) {
                return [
                    'not_approved' => false,
                    'handled_by' => $lockedComplaint->activities()->with('actor')->latest('id')->first()?->actor?->name ?? 'another staff member',
                ];
            }

            ComplaintActivity::create([
                'complaint_id' => $lockedComplaint->id,
                'actor_id' => $actor->id,
                'action' => $targetStatus,
                'from_status' => $expectedStatus,
                'to_status' => $targetStatus,
                'description' => match ($targetStatus) {
                    'approved' => 'Complaint approved'.($assignedStaffId ? ' and assigned to '.User::find($assignedStaffId)?->name : '').'.',
                    'rejected' => 'Complaint rejected: '.$rejectionReason,
                    'resolved' => 'Complaint marked as resolved.',
                },
            ]);

            return null;
        });

        if ($conflict !== null) {
            if ($conflict['not_approved']) {
                return back()->with('error', 'A complaint must be approved before it can be resolved.');
            }

            return back()->with('error', "Already handled by {$conflict['handled_by']}.");
        }

        $complaint->refresh();
        event(new ComplaintStatusChanged($complaint, $actor, $previousStatus));
        $recipients = User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->where('is_active', true)
            ->get()
            ->push($complaint->resident);
        Notification::send($recipients, new ComplaintAlert(
            $complaint,
            'Complaint '.$complaint->reference.' is now '.$complaint->statusLabel().'.',
        ));

        return back()->with('status', 'Complaint status updated.');
    }
}
