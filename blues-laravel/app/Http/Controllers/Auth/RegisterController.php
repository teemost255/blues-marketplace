<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Profile, Wallet};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Profile::create([
            'user_id'       => $user->id,
            'display_name'  => $request->name,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        Wallet::create(['user_id' => $user->id, 'balance' => 0]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')->with('success', 'Welcome to BluesMarketplace!');
    }
}
