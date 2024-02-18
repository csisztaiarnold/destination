<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserActionsController
{
    /**
     * Updates the user's password.
     *
     * @return RedirectResponse
     */
    public function update_password(): RedirectResponse
    {
        $user = User::where('id', auth()->id())->first();
        if (!Hash::check(request()->input('old_password'), $user->password)) {
            return back()->withErrors([
                'old_password' => __('Sorry, your old password doesn\'t match our records.'),
            ]);
        }
        request()->validate([
            'old_password' => 'required',
            'password' => 'required|same:password_confirm|min:8',
        ]);
        User::where('id', auth()->id())->update([
            'password' => Hash::make(request()->input('password')),
        ]);
        session()->flash('message', __('You have successfully changed your password!'));
        return redirect()->back();
    }
}
