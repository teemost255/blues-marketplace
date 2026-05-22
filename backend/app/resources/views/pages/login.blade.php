@extends('layouts.app')

@section('title', 'Login — BluesMarketplace')

@section('content')
<div class="mx-auto max-w-md px-4 py-16">
    <div class="space-y-6 rounded-3xl border border-border bg-card p-8 shadow-elevated">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Log in to BluesMarketplace</h1>
            <p class="mt-2 text-sm text-muted-foreground">Enter your email and password to continue.</p>
        </div>

        <form action="#" method="POST" class="space-y-4">
            <label class="block text-sm font-medium text-foreground">
                Email
                <input type="email" name="email" placeholder="you@example.com" class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary" />
            </label>
            <label class="block text-sm font-medium text-foreground">
                Password
                <input type="password" name="password" placeholder="••••••••" class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary" />
            </label>
            <button type="submit" class="w-full rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Sign in</button>
        </form>

        <div class="flex items-center justify-between text-sm text-muted-foreground">
            <a href="/register" class="transition hover:text-primary">Create account</a>
            <a href="/privacy" class="transition hover:text-primary">Privacy policy</a>
        </div>
    </div>
</div>
@endsection
