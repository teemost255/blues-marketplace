@extends('layouts.app')

@section('title', 'Terms of Service — BluesMarketplace')

@section('content')
<div class="container mx-auto px-4 py-16">
    <div class="space-y-6 rounded-3xl border border-border bg-card p-8 shadow-elevated">
        <h1 class="text-3xl font-bold tracking-tight">Terms of Service</h1>
        <p class="text-muted-foreground">These terms explain what you can expect when using BluesMarketplace.</p>
        <div class="space-y-6 text-sm leading-7 text-foreground">
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Acceptable Use</h2>
                <p>Users agree to respect all content guidelines and avoid posting prohibited or unsafe material.</p>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Account Responsibility</h2>
                <p>Account owners are responsible for all activity performed with their credentials.</p>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Payments & Refunds</h2>
                <p>Transactions are processed through third-party payment providers and are subject to their terms.</p>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Modifications</h2>
                <p>We may update these terms and notify users when material changes are made.</p>
            </section>
        </div>
    </div>
</div>
@endsection
