@extends('layouts.app')
@section('title', 'Create Account')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-brand rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Create your account</h1>
            <p class="text-slate-400 text-sm mt-1">Join BluesMarketplace for free</p>
        </div>

        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8">
            <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('name') border-red-500 @enderror"
                        placeholder="Your full name">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('email') border-red-500 @enderror"
                        placeholder="you@example.com">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('password') border-red-500 @enderror"
                        placeholder="Minimum 8 characters">
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm"
                        placeholder="Repeat your password">
                </div>

                <p class="text-xs text-slate-500">By registering, you agree to our <a href="{{ route('terms') }}" class="text-brand hover:underline">Terms of Service</a> and <a href="{{ route('privacy') }}" class="text-brand hover:underline">Privacy Policy</a>.</p>

                <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Create Account
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-400 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-brand hover:text-sky-300 font-medium">Sign in</a>
        </p>
    </div>
</div>
@endsection
