@extends('layouts.app')
@section('title', 'Marketplace')
@section('content')
<div class="container mx-auto px-4 py-12">
  <h1 class="text-2xl font-bold">Marketplace</h1>
  @if(isset($section) && $section === 'show')
    <p>Showing listing ID: {{ $id ?? 'n/a' }}</p>
  @else
    <p>Listing index — add server-side listing loops later.</p>
  @endif
</div>
@endsection
@extends('layouts.app')

@section('title', $section === 'show' ? "Product #{$id} — BluesMarketplace" : 'Marketplace — BluesMarketplace')

@section('content')
<div class="container mx-auto px-4 py-16">
    <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Marketplace</h1>
            <p class="mt-2 text-sm text-muted-foreground">Browse digital products from trusted sellers.</p>
        </div>
        @if ($section === 'index')
            <div class="flex flex-wrap gap-3">
                <span class="rounded-full bg-muted px-4 py-2 text-sm">All categories</span>
                <span class="rounded-full bg-muted px-4 py-2 text-sm">Newest</span>
                <span class="rounded-full bg-muted px-4 py-2 text-sm">Best sellers</span>
            </div>
        @endif
    </div>

    @if ($section === 'show')
        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6 rounded-[2rem] border border-border bg-card p-8 shadow-elevated">
                <div class="h-72 rounded-3xl bg-muted"></div>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-sm uppercase tracking-[0.25em] text-secondary font-semibold">Marketplace</div>
                    <h2 class="text-3xl font-bold">Product #{{ $id }}</h2>
                    <p class="text-muted-foreground">A premium digital product with secure checkout and excellent seller support.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-border bg-background/80 p-5">
                        <h3 class="text-sm font-semibold text-foreground">Category</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Digital assets</p>
                    </div>
                    <div class="rounded-3xl border border-border bg-background/80 p-5">
                        <h3 class="text-sm font-semibold text-foreground">Price</h3>
                        <p class="mt-2 text-sm font-semibold text-foreground">₦12,000</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-foreground">
                    <h3 class="font-semibold">About this listing</h3>
                    <p>Detailed description of the product, usage rights, and purchase features. This listing page matches the marketplace detail experience from the frontend app.</p>
                </div>
                <a href="/marketplace" class="inline-flex rounded-full bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Back to marketplace</a>
            </div>
            <aside class="space-y-6 rounded-[2rem] border border-border bg-background/90 p-6 shadow-elevated">
                <div class="rounded-3xl bg-muted p-5">
                    <p class="text-sm uppercase tracking-[0.25em] text-secondary">Seller</p>
                    <p class="mt-3 text-lg font-semibold">Marketplace Seller</p>
                </div>
                <div class="rounded-3xl border border-border bg-card p-5">
                    <p class="text-sm text-muted-foreground">Secure checkout, instant access, and seller support.</p>
                </div>
            </aside>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(range(1, 6) as $item)
                <a href="/marketplace/{{ $item }}" class="group rounded-[1.75rem] border border-border bg-card p-5 shadow-elevated transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="aspect-[16/10] rounded-3xl bg-muted"></div>
                    <div class="mt-4 space-y-2">
                        <div class="text-xs uppercase tracking-[0.25em] text-secondary">Category</div>
                        <h2 class="text-xl font-semibold">Listing #{{ $item }}</h2>
                        <p class="text-sm leading-6 text-muted-foreground">High-quality digital product with premium support and instant download.</p>
                        <div class="mt-3 flex items-center justify-between text-sm font-semibold text-foreground">
                            <span>₦{{ number_format(8000 + $item * 500) }}</span>
                            <span class="rounded-full bg-muted px-3 py-1">View</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
