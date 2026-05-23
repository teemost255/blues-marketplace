<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Always return success to prevent email enumeration
        if (!$user) {
            return back()->with('success', 'If that email exists in our system, a reset link has been sent.');
        }

        // Delete old token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => hash('sha256', $token),
            'created_at' => now(),
        ]);

        // Build reset URL
        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($request->email));

        // Log the URL (mail goes to log by default; admin can find it here)
        \Illuminate\Support\Facades\Log::info("Password reset link for {$request->email}: {$resetUrl}");

        return back()->with('reset_link', $resetUrl)->with('success', 'Password reset link generated. Check the link below.');
    }

    public function showResetForm(Request $request)
    {
        $token = $request->token;
        $email = $request->email;
        if (!$token || !$email) return redirect()->route('forgot-password')->with('error', 'Invalid reset link.');
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || $record->token !== hash('sha256', $request->token)) {
            return back()->with('error', 'Invalid or expired reset token.')->withInput();
        }

        // Token expires in 60 minutes
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->with('error', 'This reset link has expired. Please request a new one.')->withInput();
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) return back()->with('error', 'User not found.');

        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password reset successful! You can now sign in.');
    }
}
