<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function login(): RedirectResponse
    {
        return to_route('login');
    }

    public function storeLogin(RoleLoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        foreach (['admin', 'staff'] as $role) {
            if (Auth::guard($role)->attempt(
                $credentials + ['role' => $role, 'is_active' => true],
                $request->boolean('remember'),
            )) {
                $request->session()->regenerate();
                $request->session()->forget('url.intended');

                return to_route($role.'.dashboard');
            }
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match an active staff or administrator account.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
