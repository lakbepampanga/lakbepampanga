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
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            // Attempt login
            if (Auth::attempt($request->only('email', 'password'))) {
                // Check if email is verified
                if (!Auth::user()->hasVerifiedEmail()) {
                    Auth::logout();
                    
                    // For AJAX requests
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Please verify your email address first.',
                            'redirect' => route('verification.notice')
                        ], 403);
                    }
                    
                    return redirect()->route('verification.notice')
                        ->with('error', 'Please verify your email address first.');
                }

                // Log the successful login for debugging
                logger('User logged in: ' . Auth::user()->email);
    
                // For AJAX requests
                if ($request->wantsJson()) {
                    // Return different responses based on user role
                    if (Auth::user()->role === 'admin') {
                        return response()->json([
                            'success' => true,
                            'redirect' => route('admin.dashboard'),
                            'message' => 'Welcome Admin!'
                        ]);
                    }
    
                    return response()->json([
                        'success' => true,
                        'redirect' => route('user-home'),
                        'message' => 'Login successful!'
                    ]);
                }
    
                // For regular form submissions
                if (Auth::user()->role === 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'Welcome Admin!');
                }
    
                return redirect()->route('user-home')->with('success', 'Login successful!');
            }
    
            // Log the failed login attempt
            logger('Login failed for email: ' . $request->email);
    
            // Return error response based on request type
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid email or password.'
                ], 422);
            }
    
            return back()->withErrors(['email' => 'Invalid email or password.']);
    
        } catch (\Exception $e) {
            logger('Login error: ' . $e->getMessage());
    
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'An error occurred during login. Please try again.'
                ], 500);
            }
    
            return back()->withErrors(['email' => 'An error occurred during login. Please try again.']);
        }
    }

    /**
     * Helper method for role-based redirection after authentication
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return redirect()->route('verification.notice')
                ->with('error', 'Please verify your email address first.');
        }

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