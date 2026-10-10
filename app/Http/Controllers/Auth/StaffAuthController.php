<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffAuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login', ['role' => 'staff', 'loginUrl' => route('staff.login.store')]);
    }

    public function storeLogin(RoleLoginRequest $request): RedirectResponse
    {
        if (! Auth::guard('staff')->attempt(
            $request->safe()->only(['email', 'password']) + ['role' => 'staff', 'is_active' => true],
            $request->boolean('remember'),
        )) {
            return back()->withErrors(['email' => 'These credentials do not match an active staff account.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return to_route('staff.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
