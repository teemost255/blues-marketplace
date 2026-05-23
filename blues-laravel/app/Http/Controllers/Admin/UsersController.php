<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->search) $query->where('email', 'like', "%{$request->search}%")->orWhere('name', 'like', "%{$request->search}%");
        $users = $query->latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
