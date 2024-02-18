<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IsAdminOrEditor extends Middleware
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
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'editor') {
            return $next($request);
        }
        return redirect()->to('login')->withErrors([
            'logout_message' => __('You are not an admin or an editor.'),
        ]);
    }
}
