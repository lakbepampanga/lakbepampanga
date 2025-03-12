<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'gender' => 'required|string|in:male,female',
                'age' => 'required|integer|min:1|max:120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'age' => $request->age,
                'role' => 'user', // Set default role
                'email_verified_at' => null
            ]);

            // Log user creation
            \Log::info('User created successfully', ['user_id' => $user->id]);

            // Trigger the Registered event
            event(new Registered($user));

            // Log event triggered
            \Log::info('Registered event triggered', ['user_id' => $user->id]);

            // Log in the user
            auth()->login($user);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please check your email for verification.',
                'redirect' => route('verification.notice')
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration. Please try again.',
                'debug_message' => $e->getMessage() // Remove this in production
            ], 500);
        }
    }
}