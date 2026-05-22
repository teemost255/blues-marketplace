@extends('layouts.app')

@section('title', 'BluesMarketplace — Home')

@section('content')
<section class="relative overflow-hidden">
  <div class="absolute inset-0 -z-10 opacity-90" style="background: var(--gradient-hero)"></div>
  <div class="container mx-auto px-4 py-16 md:py-28">
    <div class="max-w-3xl">
      <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur">
        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
        Facebook · Instagram · TikTok · 2nd Numbers
      </div>
      <h1 class="text-4xl font-bold tracking-tight text-white md:text-6xl">
        The trusted marketplace for
        <span class="bg-gradient-to-r from-[oklch(0.74_0.08_190)] to-white bg-clip-text text-transparent"> digital accounts</span>
      </h1>
      <p class="mt-6 max-w-xl text-base text-white/80 md:text-lg">Buy verified accounts and second numbers from vetted sellers. Wallet-backed, instantly delivered, and protected by Paystack checkout.</p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="/marketplace" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-primary font-medium">Browse marketplace</a>
        <a href="/register" class="inline-flex items-center rounded-md border border-white/20 bg-white/10 px-4 py-2 text-white">Create free account</a>
      </div>
    </div>
  </div>
  <div class="bg-blobs" aria-hidden="true"><span></span></div>
</section>

<section class="border-b bg-card">
  <div class="container mx-auto grid grid-cols-2 gap-6 px-4 py-8 md:grid-cols-4">
    <div class="flex items-center gap-3">
      <div class="grid h-11 w-11 place-items-center rounded-lg bg-accent/10 text-accent">★</div>
      <div>
        <div class="text-2xl font-bold tracking-tight">0</div>
        <div class="text-xs text-muted-foreground">Active listings</div>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="grid h-11 w-11 place-items-center rounded-lg bg-accent/10 text-accent">👥</div>
      <div>
        <div class="text-2xl font-bold tracking-tight">0</div>
        <div class="text-xs text-muted-foreground">Registered users</div>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="grid h-11 w-11 place-items-center rounded-lg bg-accent/10 text-accent">⬆️</div>
      <div>
        <div class="text-2xl font-bold tracking-tight">0</div>
        <div class="text-xs text-muted-foreground">Orders processed</div>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="grid h-11 w-11 place-items-center rounded-lg bg-accent/10 text-accent">★</div>
      <div>
        <div class="text-2xl font-bold tracking-tight">4.9★</div>
        <div class="text-xs text-muted-foreground">Avg rating</div>
      </div>
    </div>
  </div>
</section>

@endsection
@extends('layouts.app')

@section('title', 'BluesMarketplace — Buy & sell digital products')

@section('content')
<div class="relative overflow-hidden bg-background py-16">
    <div class="bg-blobs"></div>
    <div class="container mx-auto px-4">
        <div class="grid gap-12 lg:grid-cols-[1.3fr_1fr] lg:items-center">
            <section class="space-y-8 text-left">
                <div class="inline-flex items-center gap-2 rounded-full bg-secondary/10 px-4 py-2 text-xs uppercase tracking-[0.28em] text-secondary font-semibold">
                    Digital marketplace
                </div>
                <div class="space-y-4">
                    <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-foreground sm:text-5xl">Buy and sell digital products with confidence</h1>
                    <p class="max-w-2xl text-base leading-8 text-muted-foreground">BluesMarketplace brings secure Paystack checkout, verified listings, and seller tools together in one modern marketplace experience.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="/marketplace" class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Browse marketplace</a>
                    <a href="/register" class="inline-flex items-center justify-center rounded-full border border-border px-6 py-3 text-sm font-semibold transition hover:bg-muted">Create an account</a>
                </div>
            </section>

            <section class="rounded-[2rem] bg-card p-8 shadow-elevated ring-1 ring-border">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <p class="text-sm uppercase tracking-[0.18em] text-secondary">Trusted by sellers</p>
                        <h2 class="text-2xl font-semibold text-foreground">Launch your digital storefront</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-3xl border border-border bg-background/80 p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Secure payout</p>
                            <p class="mt-3 text-sm leading-7 text-muted-foreground">Collect payments reliably with fast merchant settlement.</p>
                        </div>
                        <div class="rounded-3xl border border-border bg-background/80 p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Verified listings</p>
                            <p class="mt-3 text-sm leading-7 text-muted-foreground">Showcase trusted digital products with clean listing cards.</p>
                        </div>
                        <div class="rounded-3xl border border-border bg-background/80 p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Buyer support</p>
                            <p class="mt-3 text-sm leading-7 text-muted-foreground">Manage tickets, notifications, and order history in one place.</p>
                        </div>
                        <div class="rounded-3xl border border-border bg-background/80 p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Modern admin</p>
                            <p class="mt-3 text-sm leading-7 text-muted-foreground">Review listings, users, and transactions from a clean admin dashboard.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
