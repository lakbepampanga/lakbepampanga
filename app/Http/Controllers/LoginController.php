<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        // Validate login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login
        if (Auth::attempt($request->only('email', 'password'))) {
            // Log the successful login for debugging
            logger('User logged in: ' . Auth::user()->email);

            // Redirect to index with success message
            return redirect()->route('index')->with('success', 'Login successful!');
        }

        // Log the failed login attempt
        logger('Login failed for email: ' . $request->email);

        // Redirect back with error message
        return back()->withErrors(['email' => 'Invalid email or password.']);
    }
}
