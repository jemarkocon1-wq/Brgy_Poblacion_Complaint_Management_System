<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppearanceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function update(UpdateAppearanceRequest $request): JsonResponse|RedirectResponse
    {
        $appearance = $request->validated('appearance');
        $guard = $request->validated('guard');
        $user = $guard ? Auth::guard($guard)->user() : $request->user();
        abort_unless($user && ($guard === null || $user->isRole($guard)), 403);
        $user->update(['appearance' => $appearance]);

        if ($request->expectsJson()) {
            return response()->json(['appearance' => $appearance]);
        }

        return back();
    }
}
