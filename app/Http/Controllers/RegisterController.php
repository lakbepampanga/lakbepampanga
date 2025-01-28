<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Ensure unique email
            'password' => 'required|string|min:8|confirmed', // Confirmed ensures `password_confirmation` matches
            'gender' => 'required|string|in:male,female', // Validate gender
            'age' => 'required|integer|min:1|max:120', // Validate age
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender, // Add gender
            'age' => $request->age,       // Add age
        ]);

        // Log in the user
        auth()->login($user);

        // Redirect to index with success message
        return redirect()->route('home')->with('success', 'Registration successful!');
    }
}