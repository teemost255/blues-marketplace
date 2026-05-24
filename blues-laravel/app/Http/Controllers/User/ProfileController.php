<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};

class ProfileController extends Controller
{
    public function index()
    {
        $user          = Auth::user()->load('referrals');
        $profile       = Profile::firstOrCreate(['user_id' => $user->id], ['display_name' => $user->name]);
        $referralCount = $user->referrals()->count();
        return view('dashboard.profile', compact('user', 'profile', 'referralCount'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:100',
            'display_name'     => 'nullable|string|max:100',
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        $user->update(['name' => $request->name]);

        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        $profile->update(['display_name' => $request->display_name ?: $request->name]);

        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'email_notifications' => $request->boolean('email_notifications'),
        ]);
        return back()->with('success', 'Notification preferences saved.');
    }
}
