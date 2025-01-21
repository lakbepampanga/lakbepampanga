<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Redirect to the home page after resetting the password.
     *
     * @return string
     */
    protected function redirectPath()
    {
        // Flash success message
        session()->flash('success', 'Password reset successful!');

        // Redirect to the home page
        return '/';
    }
}
