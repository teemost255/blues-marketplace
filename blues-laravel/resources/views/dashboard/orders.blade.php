@extends('layouts.dashboard')
@section('title', 'My Orders')
@section('page-title', 'My Orders')
@section('content')

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h2 class="font-semibold text-white">Order History</h2>
        <span class="text-xs text-slate-400">{{ $orders->total() }} total</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Item</th>
                <th class="px-6 py-3 text-left">Category</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Date</th>
                <th class="px-6 py-3 text-left">Login Details</th>
                <th class="px-6 py-3 text-left">Review</th>
            </tr></thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium">{{ $order->listing?->title ?? 'Deleted listing' }}</p>
                    </td>
                    <td class="px-6 py-4 text-slate-400">{{ $order->listing?->category ?? '—' }}</td>
                    <td class="px-6 py-4 text-white font-semibold">₦{{ number_format($order->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        @php
                            $badge = match($order->status) {
                                'completed' => 'bg-green-900/50 text-green-400 border-green-700/50',
                                'pending'   => 'bg-yellow-900/50 text-yellow-400 border-yellow-700/50',
                                'refunded'  => 'bg-blue-900/50 text-blue-400 border-blue-700/50',
                                default     => 'bg-red-900/50 text-red-400 border-red-700/50',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 whitespace-nowrap">{{ $order->created_at->format('M j, Y') }}</td>
                    <td class="px-6 py-4">
                        @if($order->status === 'completed' && $order->delivery_data)
                            <button onclick="openDetailsModal('details-{{ $order->id }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-900/30 hover:bg-green-900/50 text-green-400 border border-green-700/40 rounded-lg text-xs font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                View Details
                            </button>
                        @elseif($order->status === 'completed')
                            <span class="text-xs text-slate-500 italic">No details attached</span>
                        @else
                            <span class="text-xs text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($order->status === 'completed')
                            @if($order->review)
                                <div class="flex items-center gap-0.5">
                                    @for($s = 1; $s <= 5; $s++)
                                        <svg class="w-3.5 h-3.5 {{ $s <= $order->review->rating ? 'text-yellow-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                            @else
                                <button onclick="openRateModal('rate-{{ $order->id }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-yellow-900/20 hover:bg-yellow-900/40 text-yellow-400 border border-yellow-700/30 rounded-lg text-xs font-medium transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Rate
                                </button>
                            @endif
                        @else
                            <span class="text-xs text-slate-600">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="font-medium">No orders yet.</p>
                    <a href="{{ route('dashboard.marketplace') }}" class="text-brand hover:underline text-sm mt-1 inline-block">Browse the marketplace →</a>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
</div>

{{-- Login Detail Modals --}}
@foreach($orders as $order)
    @if($order->status === 'completed' && $order->delivery_data)
    <div id="details-{{ $order->id }}"
         class="details-modal fixed inset-0 z-50 items-center justify-center p-4"
         style="display:none; background:rgba(0,0,0,0.75);">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">Login Details</p>
                        <p class="text-xs text-slate-400">{{ $order->listing?->title ?? 'Order #'.$order->id }}</p>
                    </div>
                </div>
                <button onclick="closeDetailsModal('details-{{ $order->id }}')" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            <div class="mx-6 mt-4 px-4 py-3 bg-yellow-900/20 border border-yellow-700/30 rounded-lg flex gap-2 text-xs text-yellow-300">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Keep these credentials private. Do not share with anyone.
            </div>

            <div class="px-6 py-4">
                <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 relative">
                    <pre id="creds-{{ $order->id }}" class="text-green-300 text-xs font-mono whitespace-pre-wrap break-all leading-relaxed">{{ $order->delivery_data }}</pre>
                    <button onclick="copyDetails({{ $order->id }})"
                        class="absolute top-3 right-3 flex items-center gap-1 text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded px-2 py-1 transition-colors"
                        id="copy-btn-{{ $order->id }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copy
                    </button>
                </div>
            </div>

            <div class="px-6 pb-5 flex items-center justify-between">
                <p class="text-xs text-slate-500">Purchased {{ $order->created_at->format('M j, Y') }}</p>
                <button onclick="closeDetailsModal('details-{{ $order->id }}')"
                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- Rating Modals --}}
@foreach($orders as $order)
    @if($order->status === 'completed' && !$order->review)
    <div id="rate-{{ $order->id }}"
         class="rate-modal fixed inset-0 z-50 items-center justify-center p-4"
         style="display:none; background:rgba(0,0,0,0.75);">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700">
                <div>
                    <p class="text-white font-semibold text-sm">Rate Your Purchase</p>
                    <p class="text-slate-400 text-xs mt-0.5 truncate max-w-[220px]">{{ $order->listing?->title ?? 'Order #'.$order->id }}</p>
                </div>
                <button onclick="closeRateModal('rate-{{ $order->id }}')" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('dashboard.orders.review', $order->id) }}" class="px-6 py-5">
                @csrf
                <p class="text-slate-400 text-xs mb-3">How would you rate this listing?</p>
                <div class="flex items-center justify-center gap-2 mb-4" id="modal-stars-{{ $order->id }}">
                    @for($s = 1; $s <= 5; $s++)
                    <button type="button"
                        onclick="setModalRating('{{ $order->id }}', {{ $s }})"
                        class="modal-star-btn text-slate-600 hover:text-yellow-400 transition-colors"
                        data-order="{{ $order->id }}" data-star="{{ $s }}">
                        <svg class="w-9 h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="modal-rating-{{ $order->id }}" required>
                <textarea name="comment" rows="2" placeholder="Optional comment..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand mb-4 resize-none"></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-brand hover:bg-brand-dark text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">Submit Review</button>
                    <button type="button" onclick="closeRateModal('rate-{{ $order->id }}')"
                        class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

<script>
function openRateModal(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; }
    document.body.style.overflow = 'hidden';
}
function closeRateModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
    document.body.style.overflow = '';
}
function setModalRating(orderId, n) {
    document.getElementById('modal-rating-' + orderId).value = n;
    document.querySelectorAll('[data-order="' + orderId + '"]').forEach((btn, i) => {
        btn.classList.toggle('text-yellow-400', i < n);
        btn.classList.toggle('text-slate-600', i >= n);
    });
}
function openDetailsModal(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; }
    document.body.style.overflow = 'hidden';
}
function closeDetailsModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
    document.body.style.overflow = '';
}
function copyDetails(orderId) {
    const text = document.getElementById('creds-' + orderId)?.textContent ?? '';
    const btn  = document.getElementById('copy-btn-' + orderId);
    const origHtml = '<svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>Copy';
    function markCopied() {
        if (btn) { btn.textContent = '✓ Copied'; setTimeout(() => { btn.innerHTML = origHtml; }, 2000); }
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(markCopied).catch(() => fallbackCopy(text, markCopied));
    } else {
        fallbackCopy(text, markCopied);
    }
}
function fallbackCopy(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); cb(); } catch(e) {}
    document.body.removeChild(ta);
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.details-modal').forEach(el => { el.style.display = 'none'; });
        document.body.style.overflow = '';
    }
});
</script>
@endsection
