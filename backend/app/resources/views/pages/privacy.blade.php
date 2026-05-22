@extends('layouts.app')

@section('title', 'Privacy Policy — BluesMarketplace')

@section('content')
<div class="container mx-auto px-4 py-16">
    <div class="space-y-6 rounded-3xl border border-border bg-card p-8 shadow-elevated">
        <h1 class="text-3xl font-bold tracking-tight">Privacy Policy</h1>
        <p class="text-muted-foreground">BluesMarketplace is committed to protecting your privacy. This page explains how we collect and use your data.</p>
        <div class="space-y-6 text-sm leading-7 text-foreground">
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Information We Collect</h2>
                <p>We collect the information you provide when you create an account, log in, place orders, or send support requests.</p>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">How We Use Your Data</h2>
                <p>Your data is used to manage your account, process transactions, and improve your marketplace experience.</p>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Cookies and Tracking</h2>
                <p>We may use cookies to maintain session state and offer improved navigation across the app.</p>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Contact</h2>
                <p>If you have questions about this policy, please contact support via the dashboard or support page.</p>
            </section>
        </div>
    </div>
</div>
@endsection
