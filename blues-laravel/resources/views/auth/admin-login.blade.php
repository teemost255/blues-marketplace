<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In — BluesMarketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 text-white">
<div class="min-h-screen grid md:grid-cols-2">
    {{-- Left panel --}}
    <div class="hidden md:flex flex-col justify-between p-12 bg-gradient-to-br from-slate-900 via-slate-800 to-sky-900">
        <div class="flex items-center gap-2 font-semibold text-white">
            <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            BluesMarketplace Admin
        </div>
        <div>
            <h2 class="text-3xl font-bold leading-tight">Admin portal.</h2>
            <p class="mt-3 text-slate-400">Sign in with your admin credentials to manage the platform.</p>
        </div>
        <p class="text-sm text-slate-500">Restricted access · Staff only</p>
    </div>

    {{-- Right panel --}}
    <div class="flex items-center justify-center p-6 bg-slate-900">
        <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-xl">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h1 class="text-2xl font-bold">Admin sign in</h1>
            </div>
            <p class="text-sm text-slate-400 mb-6">Enter your admin credentials to access the dashboard.</p>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>
                <button type="submit"
                    class="w-full bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors mt-2">
                    Sign in
                </button>
            </form>

            <div class="mt-6 text-center space-y-2 text-sm text-slate-400">
                <p>Don't have an account? <a href="{{ route('admin.register') }}" class="text-sky-400 hover:underline">Register</a></p>
                <p><a href="/" class="text-sky-400 hover:underline">← Back to marketplace</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
