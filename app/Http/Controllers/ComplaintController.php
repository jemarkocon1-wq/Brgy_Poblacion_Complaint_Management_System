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

    public function create(): View
    {
        return view('resident.complaints.create');
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $uploadedPaths = [];

        try {
            $complaint = DB::transaction(function () use ($request, &$uploadedPaths): Complaint {
                $complaint = Complaint::create($request->safe()->only([
                    'title',
                    'category',
                    'description',
                    'location',
                ]) + [
                    'reference' => 'BRGY-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                    'resident_id' => $request->user('resident')->id,
                    'status' => 'pending',
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
                    'actor_id' => $request->user('resident')->id,
                    'action' => 'submitted',
                    'to_status' => 'pending',
                    'description' => 'Complaint submitted by resident.',
                ]);

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

        return redirect()->route('resident.complaints.show', $complaint)->with('status', 'Your complaint has been submitted.');
    }

    public function show(Request $request, Complaint $complaint): View
    {
        $this->authorize('view', $complaint);
        $complaint->load(['resident', 'assignedStaff', 'evidence', 'activities.actor']);

        return view('complaints.show', [
            'complaint' => $complaint,
            'staffMembers' => in_array($request->user()->role, ['staff', 'admin'], true)
                ? User::query()->where('role', 'staff')->where('is_active', true)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
