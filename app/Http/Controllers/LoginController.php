<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     *
     * @return RedirectResponse
     */
    public function authenticate(): RedirectResponse
    {
        $credentials = request()->only('email', 'password');

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();
            if (request()->input('from_password_reset') !== null) {
                session()->flash('from_password_reset', true);
                session()->flash('tmp_password', request()->input('password'));
            }
            if (request()->input('redirect_to_route')) {
                return redirect()->to(request()->input('redirect_to_route'));
            }
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'error' => __('Hibás email vagy jelszó.'),
        ])->withInput(request()->except('password'));
    }

    /**
     * Handle logout.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        session()->flush();
        auth()->logout();
        session()->flash('message', __('Sikeres kilépés.'));
        return redirect()->to('login');
    }
}
