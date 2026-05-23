@extends('layouts.app')
@section('title', $listing->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8">
        <a href="{{ route('marketplace') }}" class="hover:text-white">Marketplace</a>
        <span>/</span>
        @if($listing->category)
            <a href="{{ route('marketplace') }}?category={{ urlencode($listing->category) }}" class="hover:text-white">{{ $listing->category }}</a>
            <span>/</span>
        @endif
        <span class="text-slate-200 truncate">{{ $listing->title }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        {{-- Image --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden h-72 md:h-80 flex items-center justify-center">
            @if($listing->image_url)
                <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
            @else
                <svg class="w-16 h-16 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            @endif
        </div>

        {{-- Details --}}
        <div class="flex flex-col">
            @if($listing->category)
                <span class="inline-block bg-brand/10 border border-brand/30 text-brand text-xs font-semibold px-2.5 py-1 rounded-full w-fit mb-3">{{ $listing->category }}</span>
            @endif
            <h1 class="text-2xl font-bold text-white mb-3">{{ $listing->title }}</h1>

            @if($listing->description)
                <p class="text-slate-400 text-sm leading-relaxed mb-6">{{ $listing->description }}</p>
            @endif

            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-slate-400 text-sm">Price</span>
                    <span class="text-3xl font-extrabold text-white">${{ number_format($listing->price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400">Availability</span>
                    @if($listing->stock > 0)
                        <span class="text-green-400 font-medium">{{ $listing->stock }} in stock</span>
                    @else
                        <span class="text-red-400 font-medium">Out of stock</span>
                    @endif
                </div>
            </div>

            @auth
                @if($listing->stock > 0)
                    <form method="POST" action="{{ route('marketplace.buy', $listing->id) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3.5 rounded-xl text-base transition-colors">
                            Buy Now — ${{ number_format($listing->price, 2) }}
                        </button>
                    </form>
                    <p class="text-xs text-slate-500 text-center">Deducted from your wallet balance. Delivery is instant.</p>
                @else
                    <button disabled class="w-full bg-slate-700 text-slate-500 font-bold py-3.5 rounded-xl text-base cursor-not-allowed">Out of Stock</button>
                @endif

                <form method="POST" action="{{ route('dashboard.wishlist.store') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                    <button type="submit" class="w-full border border-slate-600 hover:border-pink-400 text-slate-400 hover:text-pink-400 font-medium py-2.5 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        {{ $inWishlist ? 'In Wishlist' : 'Add to Wishlist' }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="block w-full bg-brand hover:bg-brand-dark text-white font-bold py-3.5 rounded-xl text-base transition-colors text-center">
                    Sign in to Purchase
                </a>
                <p class="text-xs text-slate-500 text-center mt-2">New here? <a href="{{ route('register') }}" class="text-brand hover:underline">Create a free account</a></p>
            @endauth
        </div>
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <div>
        <h2 class="text-xl font-bold text-white mb-5">Related Listings</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($related as $item)
            <a href="{{ route('marketplace.show', $item->id) }}" class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-brand/50 transition-all group">
                <div class="h-28 bg-slate-700 flex items-center justify-center overflow-hidden">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    @endif
                </div>
                <div class="p-3">
                    <p class="text-white text-xs font-medium line-clamp-2 group-hover:text-brand transition-colors">{{ $item->title }}</p>
                    <p class="text-brand font-bold text-sm mt-1">${{ number_format($item->price, 2) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
