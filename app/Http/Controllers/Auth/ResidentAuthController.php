<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentRegistrationRequest;
use App\Http\Requests\RoleLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ResidentAuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login', ['role' => 'resident', 'loginUrl' => route('login.store')]);
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function storeLogin(RoleLoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        foreach (['resident', 'staff', 'admin'] as $role) {
            if (Auth::guard($role)->attempt(
                $credentials + ['role' => $role, 'is_active' => true],
                $request->boolean('remember'),
            )) {
                $request->session()->regenerate();
                $request->session()->forget('url.intended');

                return to_route($role.'.dashboard');
            }
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
    }

    public function storeRegistration(ResidentRegistrationRequest $request): RedirectResponse
    {
        $resident = User::create($request->safe()->only(['name', 'email', 'phone', 'password']) + [
            'role' => 'resident',
            'is_active' => true,
            'appearance' => 'light',
        ]);

        Auth::guard('resident')->login($resident);
        $request->session()->regenerate();

        return redirect()->route('resident.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('resident')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function forgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email:rfc,filter', 'ends_with:@gmail.com']]);
        $status = Password::broker()->sendResetLink([
            'email' => $validated['email'],
            'role' => 'resident',
        ]);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'status' : 'error',
            __($status),
        );
    }

    public function resetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email:rfc,filter', 'ends_with:@gmail.com'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker()->reset(
            $validated + ['role' => 'resident'],
            function (User $resident, string $password): void {
                $resident->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
