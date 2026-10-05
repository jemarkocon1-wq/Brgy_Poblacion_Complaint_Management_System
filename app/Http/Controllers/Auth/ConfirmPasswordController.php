<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ConfirmPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($validated['password'], $request->user('resident')->password)) {
            return back()->withErrors(['password' => 'The provided password does not match your account.']);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('resident.dashboard'));
    }
}
