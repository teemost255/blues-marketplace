@extends('layouts.admin')
@section('title','Users')
@section('page-title','Users')
@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl mb-4 flex items-center gap-3 p-4">
    <form method="GET" class="flex gap-2 w-full max-w-md">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email…"
            class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-sky-500">
        <button class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Search</button>
    </form>
</div>
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">ID</th>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3 text-left">Email</th>
                <th class="px-6 py-3 text-left">Registered</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-6 py-3 text-slate-400">#{{ $user->id }}</td>
                    <td class="px-6 py-3 text-white">{{ $user->name }}</td>
                    <td class="px-6 py-3 text-slate-300">{{ $user->email }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ $user->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-3">
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No users found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $users->links() }}</div>
</div>
@endsection
