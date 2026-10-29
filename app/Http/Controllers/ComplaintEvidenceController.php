<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintEvidenceController extends Controller
{
    public function show(Request $request, ComplaintEvidence $evidence): StreamedResponse
    {
        $guard = $request->query('guard', 'resident');
        abort_unless(in_array($guard, ['resident', 'staff', 'admin'], true), 403);
        $user = Auth::guard($guard)->user();
        abort_unless($user !== null, 403);
        Gate::forUser($user)->authorize('viewEvidence', $evidence->complaint);

        return Storage::disk('private')->response(
            $evidence->path,
            $evidence->original_name,
            [
                'Content-Type' => $evidence->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
            ],
            'inline',
        );
    }

    public function receipt(Request $request, Complaint $complaint): StreamedResponse
    {
        $guard = $request->query('guard', 'resident');
        abort_unless(in_array($guard, ['resident', 'staff', 'admin'], true), 403);
        $user = Auth::guard($guard)->user();
        abort_unless($user !== null, 403);
        Gate::forUser($user)->authorize('viewEvidence', $complaint);
        abort_unless($complaint->receipt_proof_path !== null, 404);

        return Storage::disk('private')->response(
            $complaint->receipt_proof_path,
            $complaint->receipt_proof_original_name,
            [
                'Content-Type' => $complaint->receipt_proof_mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
            ],
            'inline',
        );
    }

    public function printReceipt(Request $request, Complaint $complaint): View
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        Gate::forUser($user)->authorize('view', $complaint);
        abort_unless($complaint->receipt_uploaded_at !== null, 404);

        return view('complaints.receipt-print', [
            'complaint' => $complaint->loadMissing('resident'),
        ]);
    }
}
