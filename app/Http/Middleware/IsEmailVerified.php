<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IsEmailVerified extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed   ...$guards
     * @return RedirectResponse|mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (auth()->user()->email_verified_at !== null) {
            return $next($request);
        }
        return redirect()->to('login')->withErrors([
            'logout_message' => __('Your user is not active yet.'),
        ]);
    }
}
