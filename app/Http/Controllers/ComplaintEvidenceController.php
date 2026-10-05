<?php

namespace App\Http\Controllers;

use App\Models\ComplaintEvidence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintEvidenceController extends Controller
{
    public function show(Request $request, ComplaintEvidence $evidence): StreamedResponse
    {
        $guard = $request->query('guard', 'resident');
        abort_unless(in_array($guard, ['resident', 'staff', 'admin'], true), 403);
        $user = Auth::guard($guard)->user();
        abort_unless($user !== null, 403);
        Gate::forUser($user)->authorize('view', $evidence->complaint);

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
}
