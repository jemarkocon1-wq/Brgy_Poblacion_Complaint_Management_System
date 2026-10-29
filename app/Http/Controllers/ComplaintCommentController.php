<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintCommentRequest;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ComplaintCommentController extends Controller
{
    public function store(StoreComplaintCommentRequest $request, Complaint $complaint): RedirectResponse
    {
        $uploadedPaths = [];
        $author = $request->user();
        abort_unless($author instanceof User, 403);

        try {
            DB::transaction(function () use ($request, $complaint, $author, &$uploadedPaths): void {
                $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
                $comment = $lockedComplaint->comments()->create([
                    'author_id' => $author->id,
                    'body' => $request->validated('body'),
                ]);

                foreach ($request->file('attachments', []) as $file) {
                    $path = $file->store('complaints/'.$lockedComplaint->id.'/comments/'.$comment->id, 'private');
                    $uploadedPaths[] = $path;

                    $lockedComplaint->evidence()->create([
                        'complaint_comment_id' => $comment->id,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);
                }

                if (in_array($author->role, ['staff', 'admin'], true)
                    && $lockedComplaint->status === 'approved'
                    && $lockedComplaint->staff_responded_at === null) {
                    $lockedComplaint->forceFill(['staff_responded_at' => now()])->save();
                }

                ComplaintActivity::create([
                    'complaint_id' => $lockedComplaint->id,
                    'actor_id' => $author->id,
                    'action' => 'commented',
                    'description' => $author->name.' added to the complaint discussion.',
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($uploadedPaths);

            throw $exception;
        }

        $complaint->refresh();
        $recipients = $author->role === 'resident'
            ? User::query()->whereIn('role', ['staff', 'admin'])->where('is_active', true)->get()
            : collect([$complaint->resident]);
        Notification::send($recipients, new ComplaintAlert(
            $complaint,
            $author->role === 'resident'
                ? 'A resident added a message to complaint '.$complaint->reference.'.'
                : 'Staff added a response to complaint '.$complaint->reference.'.',
        ));

        return redirect()
            ->route($author->role.'.complaints.show', $complaint)
            ->with('status', 'Comment added to the complaint discussion.');
    }
}
