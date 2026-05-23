@extends('layouts.dashboard')
@section('title', 'Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-2xl">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-8">
        {{-- Avatar + info --}}
        <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-700">
            <div class="w-16 h-16 rounded-2xl bg-brand flex items-center justify-center text-white text-2xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">{{ $profile->display_name ?? $user->name }}</h2>
                <p class="text-slate-400 text-sm">{{ $user->email }}</p>
                @if($profile->referral_code)
                    <p class="text-xs text-slate-500 mt-1">Referral code: <span class="text-brand font-mono font-medium">{{ $profile->referral_code }}</span></p>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('dashboard.profile.update') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Display Name</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Email Address</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-500 text-sm cursor-not-allowed">
                <p class="text-xs text-slate-500 mt-1">Email cannot be changed.</p>
            </div>

            <div class="pt-4 border-t border-slate-700">
                <h3 class="text-sm font-semibold text-white mb-4">Change Password</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">Current Password</label>
                        <input type="password" name="current_password"
                            class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('current_password') border-red-500 @enderror"
                            placeholder="Leave blank to keep current password">
                        @error('current_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                            <input type="password" name="password"
                                class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('password') border-red-500 @enderror"
                                placeholder="Min. 8 characters">
                            @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm"
                                placeholder="Repeat new password">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-3 rounded-lg text-sm transition-colors">Save Changes</button>
        </form>
    </div>
</div>
@endsection
