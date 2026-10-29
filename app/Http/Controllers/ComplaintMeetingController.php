<?php

namespace App\Http\Controllers;

use App\Events\ComplaintStatusChanged;
use App\Http\Requests\StoreComplaintMeetingRequest;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintMeeting;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComplaintMeetingController extends Controller
{
    public function store(StoreComplaintMeetingRequest $request, Complaint $complaint): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $validated = $request->validated();
        $outcome = $validated['outcome'];
        $meeting = DB::transaction(function () use ($complaint, $actor, $validated, $outcome): ComplaintMeeting {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);

            if (! in_array($lockedComplaint->status, ['approved', 'unsettled'], true)) {
                throw ValidationException::withMessages([
                    'meeting_date' => 'Minutes can only be recorded while the complaint is in progress or unsettled.',
                ]);
            }

            $previousStatus = $lockedComplaint->status;
            $nextStatus = $outcome === 'amicable_settlement' ? 'resolved' : 'unsettled';
            $meeting = $lockedComplaint->meetings()->create($validated + [
                'recorded_by_id' => $actor->id,
            ]);

            $lockedComplaint->forceFill([
                'status' => $nextStatus,
                'resolved_at' => $nextStatus === 'resolved' ? now() : null,
            ])->save();

            ComplaintActivity::create([
                'complaint_id' => $lockedComplaint->id,
                'actor_id' => $actor->id,
                'action' => 'meeting_recorded',
                'from_status' => $previousStatus === $nextStatus ? null : $previousStatus,
                'to_status' => $previousStatus === $nextStatus ? null : $nextStatus,
                'description' => ucfirst($validated['meeting_type']).' minutes recorded; outcome: '.($outcome === 'amicable_settlement' ? 'amicable settlement.' : 'unsettled.'),
            ]);

            if ($previousStatus !== $nextStatus) {
                event(new ComplaintStatusChanged($lockedComplaint, $actor, $previousStatus));
            }

            return $meeting;
        });

        $complaint->refresh();
        $recipients = User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->where('is_active', true)
            ->get()
            ->push($complaint->resident);
        Notification::send($recipients, new ComplaintAlert(
            $complaint,
            'New '.($outcome === 'amicable_settlement' ? 'amicable settlement' : 'unsettled hearing').' minutes were recorded for complaint '.$complaint->reference.'.',
        ));

        return redirect()
            ->route($actor->role.'.complaints.show', $complaint)
            ->with('status', 'Meeting minutes saved and case status updated.');
    }

    public function print(Complaint $complaint, ComplaintMeeting $meeting): View
    {
        abort_unless($meeting->complaint_id === $complaint->id, 404);
        $this->authorize('view', $complaint);

        return view('complaints.meeting-print', [
            'complaint' => $complaint->loadMissing('resident'),
            'meeting' => $meeting->loadMissing('recordedBy'),
        ]);
    }
}
