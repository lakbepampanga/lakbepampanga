<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate email input
        $request->validate(['email' => 'required|email']);

        // Attempt to send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            // Send a custom email instead of Laravel's default email
            $this->sendCustomResetEmail($request->email);
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    protected function sendCustomResetEmail($email)
    {
        // Get the user and generate a password reset token
        $user = User::where('email', $email)->first();

        if (!$user) {
            return;
        }

        $token = Password::createToken($user);

        // Send a custom email
        Mail::send('emails.custom_reset', ['token' => $token, 'email' => $email], function ($message) use ($email) {
            $message->to($email)
                    ->subject('🔒 Reset Your Password - Lakbe Pampanga');
        });
    }
}
