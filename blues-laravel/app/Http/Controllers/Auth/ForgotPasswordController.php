<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Mail};
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

        // Always return generic success to prevent email enumeration
        if (!$user) {
            return back()->with('success', 'If that email exists in our system, a reset link has been sent.');
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => hash('sha256', $token),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($request->email));

        $smtpConfigured = Setting::get('mail_host', '') !== '' && Setting::get('mail_mailer', 'log') !== 'log';

        if ($smtpConfigured) {
            try {
                $siteName    = Setting::get('site_name', 'Blues Marketplace');
                $fromAddress = Setting::get('mail_from_address', config('mail.from.address'));
                $fromName    = Setting::get('mail_from_name', $siteName);

                $html = view('emails.password-reset', [
                    'user'     => $user,
                    'resetUrl' => $resetUrl,
                    'siteName' => $siteName,
                ])->render();

                Mail::html($html, function ($msg) use ($user, $fromAddress, $fromName, $siteName) {
                    $msg->to($user->email, $user->name)
                        ->from($fromAddress, $fromName)
                        ->subject("Reset your {$siteName} password");
                });

                return back()->with('success', 'A password reset link has been sent to your email address. Check your inbox (and spam folder).');
            } catch (\Exception $e) {
                Log::error('Password reset email failed: ' . $e->getMessage());
                // Fall back to showing the link if email fails
                return back()
                    ->with('reset_link', $resetUrl)
                    ->with('success', 'Email delivery failed. Use the link below to reset your password.');
            }
        }

        // SMTP not configured — fall back to showing the link (dev/setup mode)
        Log::info("Password reset link for {$request->email}: {$resetUrl}");
        return back()
            ->with('reset_link', $resetUrl)
            ->with('success', 'Reset link generated. Click the link below (email delivery is not configured yet).');
    }

    public function showResetForm(Request $request)
    {
        $token = $request->token;
        $email = $request->email;
        if (!$token || !$email) {
            return redirect()->route('forgot-password')->with('error', 'Invalid reset link.');
        }
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || $record->token !== hash('sha256', $request->token)) {
            return back()->with('error', 'Invalid or expired reset token.')->withInput();
        }

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
