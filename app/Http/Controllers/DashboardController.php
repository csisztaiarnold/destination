<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DashboardController
{
    /**
     * Show the user's dashboard.
     *
     * @return Application|Factory|View
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function show()
    {
        return view('user.dashboard', [
            'user' => User::where('id', auth()->id())->first(),
            'from_password_reset' => session()->get('from_password_reset'),
            'tmp_password' => session()->get('tmp_password'),
            'page_class' => 'admin',
        ]);
    }

}
