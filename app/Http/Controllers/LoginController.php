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

            // Check if user is admin and redirect accordingly
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome Admin!');
            }

            // If not admin, redirect to index
            return redirect()->route('user-home')->with('success', 'Login successful!');
        }

        // Log the failed login attempt
        logger('Login failed for email: ' . $request->email);

        // Redirect back with error message
        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    /**
     * Helper method for role-based redirection after authentication
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect('user-home');
    }

    /**
     * Handle user logout
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'You have been logged out.');
    }
}