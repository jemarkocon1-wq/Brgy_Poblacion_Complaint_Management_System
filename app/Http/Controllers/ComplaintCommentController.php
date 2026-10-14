<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintCommentRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;

class ComplaintCommentController extends Controller
{
    public function store(StoreComplaintCommentRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->comments()->create([
            'author_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route($request->user()->role.'.complaints.show', $complaint)
            ->with('status', 'Comment added to the complaint discussion.');
    }
}
