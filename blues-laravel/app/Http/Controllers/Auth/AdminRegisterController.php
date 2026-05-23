<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminRegisterController extends Controller
{
    public function show()
    {
        if (session('admin_id')) return redirect()->route('admin.dashboard');
        return view('auth.admin-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'display_name' => 'nullable|string|max:100',
            'email'        => 'required|email|unique:admins_users,email',
            'password'     => 'required|string|min:6|confirmed',
        ]);

        $admin = AdminUser::create([
            'email'        => strtolower(trim($request->email)),
            'password_hash'=> Hash::make($request->password),
            'display_name' => $request->display_name ?: null,
        ]);

        session(['admin_id' => $admin->id, 'admin_email' => $admin->email, 'admin_name' => $admin->display_name]);
        return redirect()->route('admin.dashboard')->with('success', 'Account created. Welcome!');
    }
}
