@extends('layouts.dashboard')
@section('title', $listing->title)
@section('page-title', 'Marketplace')

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ route('dashboard.marketplace') }}" class="hover:text-white transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Marketplace
        </a>
        <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        @if($listing->category)
            <a href="{{ route('dashboard.marketplace') }}?category={{ urlencode($listing->category) }}" class="hover:text-white transition-colors">{{ $listing->category }}</a>
            <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        @endif
        <span class="text-slate-300 truncate max-w-[200px]">{{ $listing->title }}</span>
    </nav>

    {{-- Main product area --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-10">

        {{-- Left: Image --}}
        <div class="lg:col-span-3">
            <div class="bg-slate-800 border border-slate-700/60 rounded-2xl overflow-hidden aspect-video flex items-center justify-center relative">
                @php $showImage = $listing->image ?? ($listingCategory?->image ?? null); @endphp
                @if($showImage)
                    <img src="{{ $showImage }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                @else
                    <div class="flex flex-col items-center gap-3 text-slate-600">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-sm">No image available</span>
                    </div>
                @endif

                {{-- Out of stock overlay --}}
                @if($listing->stock <= 0)
                <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                    <span class="text-xl font-bold text-white border-2 border-white/40 rounded-xl px-6 py-3 backdrop-blur-sm">Out of Stock</span>
                </div>
                @endif
            </div>

            {{-- Tags row --}}
            <div class="flex flex-wrap gap-2 mt-4">
                @if($listing->category)
                <span class="flex items-center gap-1.5 px-3 py-1.5 bg-brand/10 border border-brand/20 text-brand text-xs font-semibold rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    {{ $listing->category }}
                </span>
                @endif
                <span class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500/10 border border-green-500/20 text-green-400 text-xs font-semibold rounded-full">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Instant Delivery
                </span>
                <span class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-700/60 border border-slate-600/50 text-slate-300 text-xs font-semibold rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Verified Account
                </span>
            </div>
        </div>

        {{-- Right: Purchase card --}}
        <div class="lg:col-span-2 flex flex-col gap-4">

            {{-- Title + rating --}}
            <div>
                <h1 class="text-xl font-extrabold text-white leading-snug mb-2">{{ $listing->title }}</h1>
                @if($avgRating)
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        @for($s = 1; $s <= 5; $s++)
                            <svg class="w-4 h-4 {{ $s <= round($avgRating) ? 'text-yellow-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <span class="text-yellow-400 font-bold text-sm">{{ $avgRating }}</span>
                    <span class="text-slate-500 text-xs">({{ $reviews->count() }} review{{ $reviews->count() !== 1 ? 's' : '' }})</span>
                </div>
                @endif
            </div>

            {{-- Description --}}
            @if($listing->description)
            <p class="text-sm text-slate-400 leading-relaxed">{{ $listing->description }}</p>
            @endif

            {{-- Price + stock card --}}
            <div class="bg-slate-800 border border-slate-700/60 rounded-2xl p-5">
                <div class="flex items-end justify-between mb-4">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Price</p>
                        <span class="text-4xl font-extrabold text-white">₦{{ number_format($listing->price, 0) }}</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500 mb-1">Availability</p>
                        @if($listing->stock > 0)
                            <span class="text-sm font-bold {{ $listing->stock <= 5 ? 'text-orange-400' : 'text-green-400' }}">
                                {{ $listing->stock }} in stock
                            </span>
                        @else
                            <span class="text-sm font-bold text-red-400">Out of stock</span>
                        @endif
                    </div>
                </div>

                @auth
                    @if($listing->stock > 0)
                        <form method="POST" action="{{ route('dashboard.marketplace.buy', $listing->id) }}" class="mb-3">
                            @csrf
                            <button type="submit"
                                class="w-full py-3.5 rounded-xl font-bold text-white text-base transition-all flex items-center justify-center gap-2"
                                style="background: linear-gradient(135deg, #0ea5e9, #6366f1)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Buy Now — ₦{{ number_format($listing->price, 2) }}
                            </button>
                        </form>
                        <p class="text-xs text-slate-600 text-center mb-3">Deducted from wallet · Delivered instantly</p>
                    @else
                        <button disabled class="w-full py-3.5 bg-slate-700 text-slate-500 font-bold rounded-xl text-base cursor-not-allowed mb-3">
                            Out of Stock
                        </button>
                    @endif

                    <form method="POST" action="{{ route('dashboard.wishlist.store') }}">
                        @csrf
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <button type="submit" class="w-full border border-slate-600 hover:border-pink-400/50 text-slate-400 hover:text-pink-400 font-semibold py-2.5 rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            {{ $inWishlist ? '❤️ In Wishlist' : 'Add to Wishlist' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}"
                        class="block w-full py-3.5 rounded-xl font-bold text-white text-base transition-all text-center mb-2"
                        style="background: linear-gradient(135deg, #0ea5e9, #6366f1)">
                        Sign In to Purchase
                    </a>
                    <p class="text-xs text-slate-500 text-center">New here? <a href="{{ route('register') }}" class="text-brand hover:underline">Create a free account</a></p>
                @endauth
            </div>

            {{-- Trust badges --}}
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-slate-800/60 border border-slate-700/40 rounded-xl p-2.5 text-center">
                    <svg class="w-4 h-4 text-green-400 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="text-[10px] text-slate-400 font-medium leading-tight">Verified</p>
                </div>
                <div class="bg-slate-800/60 border border-slate-700/40 rounded-xl p-2.5 text-center">
                    <svg class="w-4 h-4 text-blue-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <p class="text-[10px] text-slate-400 font-medium leading-tight">Instant</p>
                </div>
                <div class="bg-slate-800/60 border border-slate-700/40 rounded-xl p-2.5 text-center">
                    <svg class="w-4 h-4 text-yellow-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <p class="text-[10px] text-slate-400 font-medium leading-tight">Secure</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="lg:col-span-2">
            <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                Customer Reviews
                @if($reviews->isNotEmpty())
                <span class="text-sm font-normal text-slate-400">({{ $reviews->count() }})</span>
                @endif
            </h2>

            {{-- Leave a review --}}
            @if($userReviewedPurchaseId)
            <div class="bg-slate-800 border border-brand/20 rounded-2xl p-5 mb-5">
                <p class="text-white font-semibold mb-1 text-sm">You purchased this — share your experience!</p>
                <p class="text-xs text-slate-500 mb-4">Your review helps other buyers make informed decisions.</p>
                <form method="POST" action="{{ route('dashboard.orders.review', $userReviewedPurchaseId) }}" id="reviewForm">
                    @csrf
                    <div class="flex items-center gap-1.5 mb-4" id="starRating">
                        @for($s = 1; $s <= 5; $s++)
                        <button type="button" onclick="setRating({{ $s }})" class="star-btn text-slate-600 hover:text-yellow-400 transition-colors" data-star="{{ $s }}">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" required>
                    <textarea name="comment" rows="2" placeholder="Share your experience (optional)…"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand mb-3 resize-none"></textarea>
                    <button type="submit" class="bg-brand hover:bg-brand-dark text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-colors">Submit Review</button>
                </form>
            </div>
            @endif

            {{-- Reviews list --}}
            @if($reviews->isEmpty())
                <div class="bg-slate-800/40 border border-slate-700/40 rounded-2xl p-8 text-center">
                    <p class="text-slate-400 text-sm">No reviews yet. Be the first to review!</p>
                </div>
            @else
            <div class="space-y-3">
                @foreach($reviews as $review)
                <div class="bg-slate-800 border border-slate-700/40 rounded-2xl p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-xs font-bold text-white shrink-0">
                                {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white text-sm font-semibold">{{ $review->user?->name ?? 'User' }}</p>
                                <p class="text-slate-600 text-xs">{{ $review->created_at->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-0.5">
                            @for($s = 1; $s <= 5; $s++)
                                <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'text-yellow-400' : 'text-slate-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                    </div>
                    @if($review->comment)
                        <p class="text-slate-300 text-sm leading-relaxed">{{ $review->comment }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Related listings --}}
        @if($related->isNotEmpty())
        <div>
            <h2 class="text-lg font-bold text-white mb-5">More Like This</h2>
            <div class="space-y-3">
                @foreach($related as $item)
                <a href="{{ route('dashboard.marketplace.show', $item->id) }}"
                    class="flex items-center gap-3 bg-slate-800 border border-slate-700/40 hover:border-brand/30 rounded-xl p-3 group transition-all">
                    @php $relatedImage = $item->image ?? ($listingCategory?->image ?? null); @endphp
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-700 shrink-0 flex items-center justify-center">
                        @if($relatedImage)
                            <img src="{{ $relatedImage }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-white text-xs font-semibold line-clamp-2 group-hover:text-brand transition-colors leading-snug">{{ $item->title }}</p>
                        <p class="text-brand font-bold text-sm mt-0.5">₦{{ number_format($item->price, 0) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
let selectedRating = 0;
function setRating(n) {
    selectedRating = n;
    document.getElementById('ratingInput').value = n;
    document.querySelectorAll('.star-btn').forEach((btn, i) => {
        btn.classList.toggle('text-yellow-400', i < n);
        btn.classList.toggle('text-slate-600', i >= n);
    });
}
</script>
@endpush
