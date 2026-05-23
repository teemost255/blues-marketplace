<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    public function show()
    {
        if (session('admin_id')) return redirect()->route('admin.dashboard');
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = AdminUser::findByEmail($request->email);

        if (!$admin || !Hash::check($request->password, $admin->password_hash)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $admin->update(['last_login' => now()]);
        session(['admin_id' => $admin->id, 'admin_email' => $admin->email, 'admin_name' => $admin->display_name]);
        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->forget(['admin_id', 'admin_email', 'admin_name']);
        return redirect()->route('admin.login');
    }
}
