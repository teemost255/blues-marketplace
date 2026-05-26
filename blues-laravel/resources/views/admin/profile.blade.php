@extends('layouts.admin')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Profile Info --}}
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-sky-600 flex items-center justify-center text-white text-2xl font-bold shrink-0">
                {{ strtoupper(substr($admin->display_name ?? $admin->email, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">{{ $admin->display_name ?? 'Admin' }}</h2>
                <p class="text-slate-400 text-sm">{{ $admin->email }}</p>
                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $admin->role === 'moderator' ? 'bg-yellow-500/15 text-yellow-400' : 'bg-sky-500/15 text-sky-400' }}">
                    {{ ucfirst($admin->role ?: 'admin') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Display Name</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $admin->display_name) }}"
                        placeholder="Your name" required>
                    @error('display_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                        placeholder="you@example.com" required>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
        <h3 class="text-base font-semibold text-white mb-5 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            Change Password
        </h3>
        <form method="POST" action="{{ route('admin.profile.password') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                    @error('current_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                    <input type="password" name="password" placeholder="Min. 8 characters" required>
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Repeat new password" required>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary">Update Password</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Account Info --}}
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
        <h3 class="text-base font-semibold text-white mb-4">Account Details</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">Account ID</span>
                <span class="text-white font-mono">#{{ $admin->id }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Role</span>
                <span class="text-white">{{ ucfirst($admin->role ?: 'Admin') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Status</span>
                <span class="{{ $admin->is_active ? 'text-green-400' : 'text-red-400' }}">
                    {{ $admin->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Last Login</span>
                <span class="text-white">{{ $admin->last_login ? \Carbon\Carbon::parse($admin->last_login)->diffForHumans() : 'Never' }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
