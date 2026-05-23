@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-brand rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Forgot your password?</h1>
            <p class="text-slate-400 text-sm mt-1">Enter your email and we'll send a reset link</p>
        </div>

        @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 mb-5 text-sm flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('reset_link'))
        <div class="bg-brand/10 border border-brand/30 rounded-xl px-4 py-3 mb-5">
            <p class="text-xs font-semibold text-brand mb-2">Your password reset link (valid for 60 minutes):</p>
            <a href="{{ session('reset_link') }}" class="text-sky-300 text-xs break-all hover:underline">{{ session('reset_link') }}</a>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8">
            <form method="POST" action="{{ route('forgot-password.send') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('email') border-red-500 @enderror"
                        placeholder="you@example.com">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Send Reset Link
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-400 mt-6">
            Remember your password?
            <a href="{{ route('login') }}" class="text-brand hover:text-sky-300 font-medium">Sign in</a>
        </p>
    </div>
</div>
@endsection
