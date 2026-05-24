@extends('layouts.admin')
@section('title', 'Virtual Number Orders')
@section('page-title', 'Virtual Number Orders')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach([
        ['label' => 'Total Orders', 'value' => $stats['total'],     'color' => 'text-white',        'bg' => 'bg-slate-700/50'],
        ['label' => 'Active',       'value' => $stats['active'],    'color' => 'text-blue-400',     'bg' => 'bg-blue-900/20'],
        ['label' => 'Completed',    'value' => $stats['completed'], 'color' => 'text-green-400',    'bg' => 'bg-green-900/20'],
        ['label' => 'Cancelled',    'value' => $stats['cancelled'], 'color' => 'text-slate-400',    'bg' => 'bg-slate-700/30'],
        ['label' => 'Revenue',      'value' => '₦'.number_format($stats['revenue'],2), 'color' => 'text-brand', 'bg' => 'bg-sky-900/20'],
    ] as $s)
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-xs text-slate-400 mb-1">{{ $s['label'] }}</p>
        <p class="text-xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user, number, service…" class="flex-1 min-w-48">
    <select name="status" class="w-36">
        <option value="">All statuses</option>
        @foreach(['pending','active','completed','cancelled','failed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <input type="text" name="service" value="{{ request('service') }}" placeholder="Service (whatsapp…)" class="w-40">
    <button class="btn-primary">Filter</button>
    @if(request()->hasAny(['search','status','service']))
        <a href="{{ route('admin.virtual-numbers') }}" class="btn-primary" style="background:#475569;">Clear</a>
    @endif
</form>

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Service</th>
                    <th class="px-5 py-3 text-left">Phone Number</th>
                    <th class="px-5 py-3 text-left">SMS Code</th>
                    <th class="px-5 py-3 text-left">Cost</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20">
                    <td class="px-5 py-3 text-slate-400 font-mono text-xs">#{{ $order->id }}</td>
                    <td class="px-5 py-3">
                        <p class="text-white font-medium">{{ $order->user?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $order->user?->email ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-medium text-white capitalize">{{ $order->service }}</span>
                        <span class="text-xs text-slate-400 ml-1 uppercase">({{ $order->country }})</span>
                    </td>
                    <td class="px-5 py-3 font-mono text-slate-300 text-xs">{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono font-bold text-green-400 text-xs">{{ $order->sms_code ?? '—' }}</td>
                    <td class="px-5 py-3 text-white">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($order->status) {
                                'active'    => 'bg-blue-900/50 text-blue-300 border-blue-700/50',
                                'completed' => 'bg-green-900/50 text-green-300 border-green-700/50',
                                'cancelled' => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
                                'failed'    => 'bg-red-900/50 text-red-300 border-red-700/50',
                                default     => 'bg-yellow-900/50 text-yellow-300 border-yellow-700/50',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs whitespace-nowrap">{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            <button onclick="openModal('modal-status-{{ $order->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-sky-400 hover:bg-slate-700 transition-colors" title="Change status">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="openModal('modal-delete-vn-{{ $order->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Change Status Modal --}}
                <div id="modal-status-{{ $order->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <h3 class="font-semibold text-white mb-4">Update Order Status</h3>
                        <p class="text-sm text-slate-400 mb-1">Order #{{ $order->id }} — {{ $order->service }} ({{ $order->user?->name }})</p>
                        <p class="text-xs text-slate-500 mb-4">Number: {{ $order->phone_number ?? 'N/A' }}</p>
                        <form method="POST" action="{{ route('admin.virtual-numbers.status', $order) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">New Status</label>
                                <select name="status">
                                    @foreach(['pending','active','completed','cancelled','failed'] as $s)
                                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" class="btn-primary">Update</button>
                                <button type="button" onclick="closeModal('modal-status-{{ $order->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Delete Modal --}}
                <div id="modal-delete-vn-{{ $order->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <h3 class="font-semibold text-white mb-3">Delete Order</h3>
                        <p class="text-sm text-slate-300 mb-5">Delete order #{{ $order->id }} for <strong>{{ $order->user?->name }}</strong>? This cannot be undone.</p>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.virtual-numbers.destroy', $order) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                            <button onclick="closeModal('modal-delete-vn-{{ $order->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                        </div>
                    </div>
                </div>

            @empty
                <tr><td colspan="9" class="px-6 py-12 text-center text-slate-500">No virtual number orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
