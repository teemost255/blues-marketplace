@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-brand rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Set new password</h1>
            <p class="text-slate-400 text-sm mt-1">Choose a strong password for your account</p>
        </div>

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8">
            <form method="POST" action="{{ route('reset-password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('password') border-red-500 @enderror"
                        placeholder="At least 6 characters">
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm"
                        placeholder="Repeat password">
                </div>

                <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Reset Password
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-400 mt-6">
            <a href="{{ route('login') }}" class="text-brand hover:text-sky-300 font-medium">← Back to sign in</a>
        </p>
    </div>
</div>
@endsection
