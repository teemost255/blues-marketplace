<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — BluesMarketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#0ea5e9' } } } } }</script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center px-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
            <div class="w-9 h-9 bg-brand rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="font-bold text-white text-xl">Blues<span class="text-brand">Market</span></span>
        </a>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-xl">
        <div class="flex justify-center mb-5">
            <div class="w-16 h-16 rounded-2xl bg-brand/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <h1 class="text-xl font-bold text-white text-center mb-2">Verify your email</h1>
        <p class="text-slate-400 text-sm text-center mb-6">
            We sent a verification link to <span class="text-white font-medium">{{ auth()->user()->email }}</span>. Click the link in the email to activate your account.
        </p>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm text-center">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm text-center">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-slate-700/40 border border-slate-600 rounded-xl p-4 mb-6 text-sm text-slate-400 space-y-1">
            <p class="flex items-start gap-2"><span class="text-brand mt-0.5">•</span> Check your inbox and spam folder</p>
            <p class="flex items-start gap-2"><span class="text-brand mt-0.5">•</span> The link expires in 60 minutes</p>
            <p class="flex items-start gap-2"><span class="text-brand mt-0.5">•</span> Click the link to access your dashboard</p>
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="w-full py-3 bg-brand hover:bg-brand/80 text-white font-bold rounded-xl transition-colors text-sm">
                Resend Verification Email
            </button>
        </form>

        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hover:text-white transition-colors">Sign out</button>
            </form>
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">← Back to home</a>
        </div>
    </div>
</div>
</body>
</html>
