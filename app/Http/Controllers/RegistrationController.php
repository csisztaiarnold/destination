<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Mail\RegistrationMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    /**
     * Handle user registration action.
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function registration(): RedirectResponse
    {
        request()->validate([
            'password' => 'required|same:password_confirm|min:8',
            'username' => 'required|min:3|max:30|unique:users',
            'email' => 'required|email|unique:users',
        ]);
        $email = request()->input('email');
        $username = request()->input('username');
        $unique_id = generateRandomString(60);
        $id = User::insertGetId([
            'email' => $email,
            'username' => $username,
            'password' => Hash::make(request()->input('password')),
            'unique_id' => $unique_id,
            'created_at' => Carbon::now(),
        ]);

        // Send a mail with the URL to confirm the registration.
        Mail::to($email)->send(new RegistrationMail([
            'username' => $username,
            'user_id' => $id,
            'unique_id' => $unique_id,
        ]));

        session()->flash('message', __('We have sent an email with a link to confirm your registration.'));
        return redirect()->back();
    }

    /**
     * Handle registration confirmation action via URL.
     *
     * @param string $user_id   The user ID
     * @param string $unique_id The user's unique ID
     * @return RedirectResponse
     */
    public function confirmRegistration(string $user_id, string $unique_id): RedirectResponse
    {
        // Check if the user haven't confirmed the registration already.
        $already_confirmed_user = User::where('id', $user_id)
            ->where('unique_id', $unique_id)
            ->where('email_verified_at', '!=', null)
            ->first();

        if (isset($already_confirmed_user->id)) {
            session()->flash('message', __('Your registration has been already confirmed!'));
            return redirect()->to('register');
        }

        // Check for unverified user.
        $user_query = User::where('id', $user_id)
            ->where('unique_id', $unique_id)
            ->where('email_verified_at', null);
        $user = $user_query->first();
        if (isset($user->id)) {
            $user_query->update([
                'email_verified_at' => Carbon::now(),
            ]);
            session()->flash('message', __('Thank you! Your registration has been confirmed! You could login now.'));
            return redirect()->to('login');
        }

        return redirect()->to('register')->withErrors([
            'registration_confirm_failed' => __('Sorry, something went wrong.'),
        ]);
    }

    /**
     * Handle password reset action.
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function sendPasswordResetEmail()
    {
        // Check for user.
        $user_query = User::where('email', request()->input('email'));
        $user = $user_query->first();

        if (isset($user->id)) {
            $unique_id = generateRandomString(60);
            // Update the unique_id first.
            $user->update([
                'unique_id' => $unique_id,
            ]);

            // Send a mail with the URL to confirm the password reset.
            Mail::to($user->email)->send(new PasswordResetMail([
                'username' => $user->username,
                'user_id' => $user->id,
                'unique_id' => $unique_id,
            ]));
            session()->flash('message', __('We have sent an email with a link to confirm your password reset.'));
            return redirect()->back();
        }

        return redirect()->to('reset-password')->withErrors([
            'email_doesnt_exists' => __('Sorry, this email doesn\'t exists.'),
        ]);
    }

    /**
     * Confirming password reset.
     *
     * @param string $user_id   The user ID
     * @param string $unique_id The user's unique  ID
     * @return Application|Factory|View|RedirectResponse
     * @throws Exception
     */
    public function confirmPasswordReset(string $user_id, string $unique_id)
    {
        // Check for user.
        $user_query = User::where('id', $user_id)
            ->where('unique_id', $unique_id);
        $user = $user_query->first();

        if (isset($user->id)) {
            $tmp_password = generateRandomString(20);
            $user_query->update([
                'password' => Hash::make($tmp_password),
                'email_verified_at' => Carbon::now(),
                'unique_id' => generateRandomString(60),
            ]);

            session()->flash(
                'warning',
                __(
                    'We have created a new password for you. Please change it as soon as you login to your dashboard.<br /> <strong>You will be able to use this link only once.</strong>'
                )
            );
            return view('user.login', [
                'email' => $user->email,
                'tmp_password' => $tmp_password,
                'title' => __('Login'),
                'page_class' => 'login',
            ]);
        }

        return redirect()->to('reset-password')->withErrors([
            'password_reset_confirm_failed' => __('Sorry, something went wrong.'),
        ]);
    }
}
