@extends('layouts.app')
@section('title', 'Create Account')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <img src="/images/logo.jpeg" alt="Blues Marketplace" class="h-16 w-auto">
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
