@extends('layouts.admin')
@section('title', 'Listing Reviews')
@section('page-title', 'Listing Reviews')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Reviews</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Average Rating</p>
        <div class="flex items-baseline gap-2 mt-1">
            <p class="text-3xl font-bold text-yellow-400">{{ $stats['average'] }}</p>
            <span class="text-yellow-400">★</span>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">5-Star Reviews</p>
        <p class="text-3xl font-bold text-green-400 mt-1">{{ number_format($stats['five_star']) }}</p>
    </div>
</div>

{{-- Reviews table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700">
        <h2 class="font-semibold text-white">All Reviews</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-6 py-3 text-left">User</th>
                    <th class="px-6 py-3 text-left">Listing</th>
                    <th class="px-6 py-3 text-left">Rating</th>
                    <th class="px-6 py-3 text-left">Comment</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($reviews as $review)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-3">
                        <p class="text-white font-medium text-xs">{{ $review->user?->name ?? 'Deleted user' }}</p>
                        <p class="text-slate-500 text-xs">{{ $review->user?->email }}</p>
                    </td>
                    <td class="px-6 py-3 text-slate-300 text-xs max-w-xs truncate">
                        {{ $review->listing?->title ?? 'Deleted listing' }}
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-0.5">
                            @for($s = 1; $s <= 5; $s++)
                                <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'text-yellow-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                            <span class="text-slate-400 text-xs ml-1">{{ $review->rating }}/5</span>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-slate-400 text-xs max-w-xs truncate">{{ $review->comment ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-400 text-xs whitespace-nowrap">{{ $review->created_at->format('M j, Y') }}</td>
                    <td class="px-6 py-3">
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" onsubmit="return confirm('Delete this review?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-16 text-center text-slate-500">No reviews yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
