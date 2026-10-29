<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintSummonsServiceRequest;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintSummonsService;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ComplaintSummonsServiceController extends Controller
{
    public function store(StoreComplaintSummonsServiceRequest $request, Complaint $complaint): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $file = $request->file('proof');
        abort_unless($file !== null, 422);
        $path = $file->store('complaints/'.$complaint->id.'/summons-service', 'private');

        try {
            DB::transaction(function () use ($complaint, $actor, $request, $file, $path): void {
                $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
                abort_unless($lockedComplaint->summons_ready_at !== null, 403);
                $details = $request->safe()->only([
                    'served_at',
                    'server_name',
                    'recipient_name',
                    'result',
                    'signer_type',
                    'signer_name',
                    'refusal_response',
                ]);
                $details['served_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $details['served_at']);

                $service = $lockedComplaint->summonsServices()->create($details + [
                    'recorded_by_id' => $actor->id,
                    'proof_path' => $path,
                    'proof_original_name' => $file->getClientOriginalName(),
                    'proof_mime_type' => $file->getMimeType(),
                    'proof_size' => $file->getSize(),
                ]);

                ComplaintActivity::create([
                    'complaint_id' => $lockedComplaint->id,
                    'actor_id' => $actor->id,
                    'action' => 'summons_service_recorded',
                    'description' => 'Summons service attempt recorded as '.str_replace('_', ' ', $service->result).'.',
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($path);

            throw $exception;
        }

        $complaint->refresh();
        $complaint->resident->notify(new ComplaintAlert(
            $complaint,
            'The summons service record for complaint '.$complaint->reference.' was updated.',
        ));

        return back()->with('status', 'Summons service record saved.');
    }

    public function proof(Request $request, ComplaintSummonsService $service): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        Gate::forUser($user)->authorize('viewEvidence', $service->complaint);

        return Storage::disk('private')->response(
            $service->proof_path,
            $service->proof_original_name,
            [
                'Content-Type' => $service->proof_mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
            ],
            'inline',
        );
    }

    public function print(ComplaintSummonsService $service): View
    {
        $this->authorize('viewEvidence', $service->complaint);

        return view('complaints.summons-service-print', [
            'service' => $service->loadMissing('complaint.resident', 'recordedBy'),
        ]);
    }
}
