@extends('layouts.dashboard')
@section('title', 'Wishlist')
@section('page-title', 'My Wishlist')

@section('content')
@if($wishlists->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <p class="text-lg font-medium text-slate-400">Your wishlist is empty</p>
        <p class="text-sm mt-1">Save listings you're interested in for later</p>
        <a href="{{ route('marketplace') }}" class="mt-4 inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition-colors">Browse Marketplace</a>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($wishlists as $item)
        @if($item->listing)
        <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-brand/50 transition-all group flex flex-col">
            <a href="{{ route('marketplace.show', $item->listing->id) }}" class="block">
                <div class="h-32 bg-slate-700 flex items-center justify-center overflow-hidden">
                    @if($item->listing->image_url)
                        <img src="{{ $item->listing->image_url }}" alt="{{ $item->listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    @else
                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    @endif
                </div>
            </a>
            <div class="p-4 flex flex-col flex-1">
                @if($item->listing->category)
                    <span class="text-xs text-brand font-medium">{{ $item->listing->category }}</span>
                @endif
                <a href="{{ route('marketplace.show', $item->listing->id) }}">
                    <h3 class="font-semibold text-white text-sm mt-1 line-clamp-2 group-hover:text-brand transition-colors">{{ $item->listing->title }}</h3>
                </a>
                <div class="mt-auto pt-3 border-t border-slate-700 flex items-center justify-between">
                    <span class="text-lg font-bold text-white">${{ number_format($item->listing->price, 2) }}</span>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('dashboard.wishlist.destroy', $item->listing->id) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 border border-red-900/50 hover:border-red-700 px-2 py-1.5 rounded-lg transition-colors">Remove</button>
                        </form>
                        <a href="{{ route('marketplace.show', $item->listing->id) }}" class="bg-brand hover:bg-brand-dark text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Buy</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
@endif
@endsection
