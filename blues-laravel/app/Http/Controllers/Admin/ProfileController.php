<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $admin = AdminUser::findOrFail(session('admin_id'));
        return view('admin.profile', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = AdminUser::findOrFail(session('admin_id'));

        $request->validate([
            'display_name' => 'required|string|max:100',
            'email'        => 'required|email|unique:admins_users,email,' . $admin->id,
        ]);

        $admin->update([
            'display_name' => $request->display_name,
            'email'        => strtolower(trim($request->email)),
        ]);

        session(['admin_name' => $request->display_name, 'admin_email' => strtolower(trim($request->email))]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = AdminUser::findOrFail(session('admin_id'));

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password_hash)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $admin->update(['password_hash' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function pendingCount()
    {
        $userConfirmed = \App\Models\BankTransferPayment::where('status', 'pending')
            ->whereNotNull('user_confirmed_at')
            ->count();

        $totalPending = \App\Models\BankTransferPayment::where('status', 'pending')->count();

        return response()->json([
            'user_confirmed' => $userConfirmed,
            'total_pending'  => $totalPending,
        ]);
    }
}
