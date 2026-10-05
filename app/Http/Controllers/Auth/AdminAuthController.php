<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login', ['role' => 'admin', 'loginUrl' => route('admin.login.store')]);
    }

    public function storeLogin(RoleLoginRequest $request): RedirectResponse
    {
        if (! Auth::guard('admin')->attempt(
            $request->safe()->only(['email', 'password']) + ['role' => 'admin', 'is_active' => true],
            $request->boolean('remember'),
        )) {
            return back()->withErrors(['email' => 'These credentials do not match an active admin account.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
