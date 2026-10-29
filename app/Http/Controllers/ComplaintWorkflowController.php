<?php

namespace App\Http\Controllers;

use App\Events\ComplaintStatusChanged;
use App\Http\Requests\ResolveComplaintRequest;
use App\Http\Requests\SaveComplaintSummonsRequest;
use App\Http\Requests\SubmitComplaintReceiptRequest;
use App\Http\Requests\TransitionComplaintRequest;
use App\Models\BarangayInformation;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LogicException;
use Throwable;

class ComplaintWorkflowController extends Controller
{
    public function approve(TransitionComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        return $this->transition($request, $complaint, 'approved', $request->validated());
    }

    public function reject(TransitionComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        return $this->transition($request, $complaint, 'rejected', $request->validated());
    }

    public function resolve(ResolveComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        return $this->transition($request, $complaint, 'resolved');
    }

    public function submitReceipt(SubmitComplaintReceiptRequest $request, Complaint $complaint): RedirectResponse
    {
        $resident = $request->user();
        abort_unless($resident instanceof User, 403);

        $file = $request->file('receipt_proof');
        abort_unless($file !== null, 422);

        $path = $file->store('complaints/'.$complaint->id.'/receipt', 'private');

        try {
            $previousPath = DB::transaction(function () use ($complaint, $resident, $file, $path): ?string {
                $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);

                if ($lockedComplaint->status !== 'approved' || $lockedComplaint->receipt_confirmed_at !== null) {
                    throw ValidationException::withMessages([
                        'receipt_proof' => 'Receipt proof can only be submitted before staff confirms it.',
                    ]);
                }

                $previousPath = $lockedComplaint->receipt_proof_path;
                $lockedComplaint->forceFill([
                    'receipt_proof_path' => $path,
                    'receipt_proof_original_name' => $file->getClientOriginalName(),
                    'receipt_proof_mime_type' => $file->getMimeType(),
                    'receipt_proof_size' => $file->getSize(),
                    'receipt_uploaded_at' => now(),
                ])->save();

                ComplaintActivity::create([
                    'complaint_id' => $lockedComplaint->id,
                    'actor_id' => $resident->id,
                    'action' => 'receipt_uploaded',
                    'description' => 'Resident uploaded receipt proof for staff confirmation.',
                ]);

                return $previousPath;
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($path);

            throw $exception;
        }

        if ($previousPath !== null) {
            Storage::disk('private')->delete($previousPath);
        }

        $complaint->refresh();
        $staffRecipients = User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->where('is_active', true)
            ->get();
        Notification::send($staffRecipients, new ComplaintAlert(
            $complaint,
            'Receipt proof was uploaded for complaint '.$complaint->reference.'. Confirm it before preparing the summons.',
        ));

        return back()->with('status', 'Receipt proof uploaded. Staff will confirm it before preparing the summons.');
    }

    public function confirmReceipt(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('confirmReceipt', $complaint);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        $confirmed = DB::transaction(function () use ($complaint, $actor): bool {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);

            if ($lockedComplaint->status !== 'approved'
                || $lockedComplaint->receipt_proof_path === null
                || $lockedComplaint->receipt_confirmed_at !== null) {
                return false;
            }

            $confirmedAt = now();
            $lockedComplaint->forceFill([
                'receipt_confirmed_at' => $confirmedAt,
                'staff_responded_at' => $lockedComplaint->staff_responded_at ?? $confirmedAt,
            ])->save();

            ComplaintActivity::create([
                'complaint_id' => $lockedComplaint->id,
                'actor_id' => $actor->id,
                'action' => 'receipt_confirmed',
                'description' => 'Staff confirmed the resident receipt proof.',
            ]);

            return true;
        });

        if (! $confirmed) {
            return back()->with('error', 'Receipt proof is missing or has already been confirmed.');
        }

        $complaint->refresh();
        $complaint->resident->notify(new ComplaintAlert(
            $complaint,
            'Staff confirmed your receipt for complaint '.$complaint->reference.'. The summons is now being prepared.',
        ));

        return redirect()
            ->route($actor->role.'.complaints.summons', $complaint)
            ->with('status', 'Receipt confirmed. Complete the summons details below.');
    }

    public function showSummons(Request $request, Complaint $complaint): View|RedirectResponse
    {
        $this->authorize('view', $complaint);
        $role = $request->user()?->role ?? 'resident';

        if ($complaint->receipt_confirmed_at === null) {
            return redirect()
                ->route($role.'.complaints.show', $complaint)
                ->with('error', 'Staff must confirm the receipt proof before the summons can be prepared.');
        }

        $isStaff = in_array($role, ['staff', 'admin'], true);

        if (! $isStaff && $complaint->summons_ready_at === null) {
            return redirect()
                ->route($role.'.complaints.show', $complaint)
                ->with('status', 'The receipt has been confirmed. Staff will prepare the summons next.');
        }

        return view('complaints.summons', [
            'complaint' => $complaint->loadMissing('resident'),
            'barangayInformation' => BarangayInformation::query()->first(),
            'role' => $role,
            'editable' => $isStaff,
        ]);
    }

    public function saveSummons(SaveComplaintSummonsRequest $request, Complaint $complaint): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($request, $complaint, $actor): void {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
            abort_unless($lockedComplaint->receipt_confirmed_at !== null, 403);

            $lockedComplaint->update($request->validated() + ['summons_ready_at' => now()]);

            ComplaintActivity::create([
                'complaint_id' => $lockedComplaint->id,
                'actor_id' => $actor->id,
                'action' => 'summons_prepared',
                'description' => 'Summons details prepared by '.$actor->name.'.',
            ]);
        });

        $complaint->refresh();
        $complaint->resident->notify(new ComplaintAlert(
            $complaint,
            'The summons for complaint '.$complaint->reference.' is ready to view.',
        ));

        return redirect()
            ->route($actor->role.'.complaints.summons', $complaint)
            ->with('status', 'Summons details saved. You can now print the form.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function transition(Request $request, Complaint $complaint, string $targetStatus, array $validated = []): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $previousStatus = $targetStatus === 'resolved' ? 'approved' : 'pending';
        $rejectionReason = is_string($validated['reason'] ?? null) ? $validated['reason'] : null;
        $assignedStaffId = isset($validated['assigned_staff_id']) ? (int) $validated['assigned_staff_id'] : null;
        $conflict = DB::transaction(function () use ($complaint, $actor, $targetStatus, $previousStatus, $rejectionReason, $assignedStaffId): ?array {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
            $expectedStatus = $previousStatus;

            if ($lockedComplaint->status !== $expectedStatus) {
                if ($targetStatus === 'resolved' && $lockedComplaint->status === 'pending') {
                    return ['not_approved' => true, 'handled_by' => null];
                }

                return [
                    'not_approved' => false,
                    'handled_by' => $lockedComplaint->activities()->with('actor')->latest('id')->first()?->actor->name ?? 'another staff member',
                ];
            }

            $attributes = [
                'status' => $targetStatus,
                'rejection_reason' => $targetStatus === 'rejected' ? $rejectionReason : null,
            ];

            if ($targetStatus === 'approved') {
                $attributes['assigned_staff_id'] = $assignedStaffId;
                $attributes['approved_at'] = now();
                $attributes['staff_responded_at'] = now();
                $attributes['closed_at'] = null;
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
                    'handled_by' => $lockedComplaint->activities()->with('actor')->latest('id')->first()?->actor->name ?? 'another staff member',
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
                    'resolved' => 'Staff marked the complaint as resolved.',
                    default => throw new LogicException("Unsupported complaint transition: {$targetStatus}."),
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
        $staffRecipients = User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->where('is_active', true)
            ->get();

        if ($targetStatus === 'approved') {
            Notification::send($staffRecipients, new ComplaintAlert(
                $complaint,
                'Complaint '.$complaint->reference.' was approved. The resident was asked to collect a receipt.',
            ));
            $complaint->resident->notify(new ComplaintAlert(
                $complaint,
                'Your complaint '.$complaint->reference.' was approved. Please visit the barangay office to collect your receipt, then upload a photo or PDF of it here for staff confirmation.',
            ));
        } else {
            Notification::send($staffRecipients->push($complaint->resident), new ComplaintAlert(
                $complaint,
                $targetStatus === 'resolved'
                    ? 'Staff marked complaint '.$complaint->reference.' as solved.'
                    : 'Complaint '.$complaint->reference.' is now '.$complaint->statusLabel().'.',
            ));
        }

        $response = $targetStatus === 'resolved'
            ? redirect()->route('staff.complaints.show', $complaint)
            : back();

        return $response->with('status', 'Complaint status updated.');
    }
}
