<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ModeratorsController extends Controller
{
    public function index()
    {
        $moderators = AdminUser::where('role', 'moderator')->latest()->get();
        $admins     = AdminUser::where('role', 'admin')->orWhereNull('role')->latest()->get();
        return view('admin.moderators', compact('moderators', 'admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'email'        => 'required|email|unique:admins_users,email',
            'password'     => 'required|string|min:6',
        ]);

        $mod = AdminUser::create([
            'email'         => strtolower(trim($request->email)),
            'password_hash' => Hash::make($request->password),
            'display_name'  => $request->display_name,
            'role'          => 'moderator',
            'is_active'     => true,
        ]);

        \App\Helpers\AuditHelper::log("Created moderator account: {$request->display_name} ({$request->email})", 'moderator', $mod->id);
        return back()->with('success', "Moderator {$request->display_name} created successfully.");
    }

    public function assignRole(Request $request, AdminUser $admin)
    {
        $role = $request->role;
        if (!in_array($role, ['admin', 'moderator'])) {
            return back()->with('error', 'Invalid role.');
        }
        // Prevent demoting yourself
        if ($admin->id == session('admin_id') && $role !== 'admin') {
            return back()->with('error', 'You cannot change your own role.');
        }
        $admin->update(['role' => $role]);
        $label = $role === 'admin' ? 'promoted to Admin' : 'set as Moderator';
        \App\Helpers\AuditHelper::log("Role change: {$admin->display_name} ({$admin->email}) {$label}", 'admin_user', $admin->id);
        return back()->with('success', "{$admin->display_name} has been {$label}.");
    }

    public function destroy(AdminUser $admin)
    {
        if ($admin->id == session('admin_id')) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        \App\Helpers\AuditHelper::log("Deleted account: {$admin->display_name} ({$admin->email})", 'admin_user', $admin->id);
        $admin->delete();
        return back()->with('success', 'Account removed.');
    }
}
