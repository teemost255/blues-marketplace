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
        $user    = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id], ['display_name' => $user->name]);
        return view('dashboard.profile', compact('user', 'profile'));
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
}
