@extends('layouts.dashboard')
@section('title', $listing->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8">
        <a href="{{ route('dashboard.marketplace') }}" class="hover:text-white">Marketplace</a>
        <span>/</span>
        @if($listing->category)
            <a href="{{ route('dashboard.marketplace') }}?category={{ urlencode($listing->category) }}" class="hover:text-white">{{ $listing->category }}</a>
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
                    <span class="text-3xl font-extrabold text-white">₦{{ number_format($listing->price, 2) }}</span>
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
                    <form method="POST" action="{{ route('dashboard.marketplace.buy', $listing->id) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3.5 rounded-xl text-base transition-colors">
                            Buy Now — ₦{{ number_format($listing->price, 2) }}
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

    {{-- Ratings Summary on product card --}}
    @if($avgRating)
    <div class="flex items-center gap-2 mt-2 mb-4">
        <div class="flex items-center gap-0.5">
            @for($s = 1; $s <= 5; $s++)
                <svg class="w-4 h-4 {{ $s <= round($avgRating) ? 'text-yellow-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
        </div>
        <span class="text-yellow-400 font-semibold text-sm">{{ $avgRating }}</span>
        <span class="text-slate-400 text-xs">({{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }})</span>
    </div>
    @endif

    {{-- Reviews Section --}}
    <div class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-white">
                Customer Reviews
                @if($reviews->isNotEmpty())
                    <span class="text-slate-400 text-base font-normal ml-2">({{ $reviews->count() }})</span>
                @endif
            </h2>
        </div>

        {{-- Leave a review --}}
        @if($userReviewedPurchaseId)
        <div class="bg-slate-800 border border-brand/30 rounded-xl p-5 mb-6">
            <p class="text-white font-medium mb-3 text-sm">You purchased this — leave a review!</p>
            <form method="POST" action="{{ route('orders.review', $userReviewedPurchaseId) }}" id="reviewForm">
                @csrf
                <div class="flex items-center gap-1 mb-3" id="starRating">
                    @for($s = 1; $s <= 5; $s++)
                    <button type="button" onclick="setRating({{ $s }})" class="star-btn text-slate-600 hover:text-yellow-400 transition-colors" data-star="{{ $s }}">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" required>
                <textarea name="comment" rows="2" placeholder="Share your experience (optional)..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand mb-3 resize-none"></textarea>
                <button type="submit" class="bg-brand hover:bg-brand-dark text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors">Submit Review</button>
            </form>
        </div>
        @endif

        {{-- Existing reviews --}}
        @if($reviews->isEmpty())
            <p class="text-slate-500 text-sm">No reviews yet. Be the first to review this listing.</p>
        @else
        <div class="space-y-4">
            @foreach($reviews as $review)
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand flex items-center justify-center text-xs font-bold text-white">
                            {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <span class="text-white text-sm font-medium">{{ $review->user?->name ?? 'User' }}</span>
                    </div>
                    <div class="flex items-center gap-0.5">
                        @for($s = 1; $s <= 5; $s++)
                            <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'text-yellow-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                </div>
                @if($review->comment)
                    <p class="text-slate-300 text-sm leading-relaxed">{{ $review->comment }}</p>
                @endif
                <p class="text-slate-500 text-xs mt-2">{{ $review->created_at->format('M j, Y') }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <div>
        <h2 class="text-xl font-bold text-white mb-5">Related Listings</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($related as $item)
            <a href="{{ route('dashboard.marketplace.show', $item->id) }}" class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-brand/50 transition-all group">
                <div class="h-28 bg-slate-700 flex items-center justify-center overflow-hidden">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    @endif
                </div>
                <div class="p-3">
                    <p class="text-white text-xs font-medium line-clamp-2 group-hover:text-brand transition-colors">{{ $item->title }}</p>
                    <p class="text-brand font-bold text-sm mt-1">₦{{ number_format($item->price, 2) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
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
