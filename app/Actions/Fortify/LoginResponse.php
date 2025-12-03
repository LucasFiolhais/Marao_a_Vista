<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // Se for Admin → vai para admin dashboard
        if ($user->hasRole('admin')) {
            return redirect()->intended('/admin');
        }

        // Se for Cliente → vai para Home
        return redirect()->intended('/');
    }
}
