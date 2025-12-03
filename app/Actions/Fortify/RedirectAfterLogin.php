<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;

class RedirectAfterLogin implements LoginResponse
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/');
    }
}
