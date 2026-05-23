@extends('layouts.app')
@section('title', 'Sign In')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-brand rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Welcome back</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to your BluesMarketplace account</p>
        </div>

        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8">
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('email') border-red-500 @enderror"
                        placeholder="you@example.com">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('password') border-red-500 @enderror"
                        placeholder="••••••••">
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-brand focus:ring-brand">
                    <label for="remember" class="ml-2 text-sm text-slate-400">Remember me</label>
                </div>

                <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-400 mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-brand hover:text-sky-300 font-medium">Create one free</a>
        </p>
    </div>
</div>
@endsection
