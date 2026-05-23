@extends('layouts.dashboard')
@section('title', 'My Orders')
@section('page-title', 'My Orders')

@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700">
        <h2 class="font-semibold text-white">Order History</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Item</th>
                <th class="px-6 py-3 text-left">Category</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-slate-700/50">
                    <td class="px-6 py-3">
                        <p class="text-white font-medium">{{ $order->listing?->title ?? 'Deleted listing' }}</p>
                        @if($order->status === 'completed' && $order->delivery_data)
                            <p class="text-xs text-green-400 mt-0.5">{{ \Illuminate\Support\Str::limit($order->delivery_data, 60) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $order->listing?->category ?? '—' }}</td>
                    <td class="px-6 py-3 text-white font-semibold">${{ number_format($order->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ match($order->status) { 'completed'=>'bg-green-900/50 text-green-400', 'pending'=>'bg-yellow-900/50 text-yellow-400', 'refunded'=>'bg-blue-900/50 text-blue-400', default=>'bg-red-900/50 text-red-400' } }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $order->created_at->format('M j, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    No orders yet. <a href="{{ route('marketplace') }}" class="text-brand hover:underline">Browse the marketplace →</a>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
