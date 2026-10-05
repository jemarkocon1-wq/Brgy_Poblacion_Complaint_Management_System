<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = $request->input('guard') ?? match (true) {
            $request->is('admin/*') => 'admin',
            $request->is('staff/*') => 'staff',
            $request->is('resident/*') => 'resident',
            default => null,
        };
        $user = $guard ? $request->user($guard) : $request->user();

        if (! $user) {
            foreach (['resident', 'staff', 'admin'] as $guard) {
                $user = $request->user($guard);

                if ($user) {
                    break;
                }
            }
        }

        View::share('appearance', $user?->appearance ?? $request->cookie('appearance') ?? 'light');

        return $next($request);
    }
}
