@extends('layouts.admin')
@section('title', 'User Dashboard — ' . $user->name)
@section('page-title', 'User Dashboard')
@section('content')

{{-- Banner --}}
<div class="mb-6 p-4 bg-sky-900/30 border border-sky-700 rounded-xl flex items-center justify-between">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        <p class="text-sky-300 text-sm">Viewing dashboard of <strong class="text-white">{{ $user->name }}</strong> ({{ $user->email }})</p>
    </div>
    <a href="{{ route('admin.users') }}" class="text-xs text-sky-400 hover:underline">← Back to Users</a>
</div>

{{-- Profile + Stats --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
    <div class="lg:col-span-1 bg-slate-800 border border-slate-700 rounded-xl p-5 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-sky-700 flex items-center justify-center text-2xl font-bold text-white mb-3">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <p class="text-white font-semibold">{{ $user->name }}</p>
        <p class="text-slate-400 text-xs mt-1">{{ $user->email }}</p>
        <span class="status-{{ $user->status }} mt-2">{{ ucfirst($user->status) }}</span>
        <p class="text-xs text-slate-500 mt-2">Joined {{ $user->created_at->format('d M Y') }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex flex-col justify-between">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Wallet Balance</p>
        <p class="text-2xl font-bold text-white mt-2">₦{{ number_format($wallet?->balance ?? 0, 2) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex flex-col justify-between">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Total Orders</p>
        <p class="text-2xl font-bold text-white mt-2">{{ $orders->count() }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex flex-col justify-between">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Open Tickets</p>
        <p class="text-2xl font-bold text-yellow-400 mt-2">{{ $tickets->where('status','open')->count() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Orders --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl">
        <div class="px-5 py-4 border-b border-slate-700 font-semibold text-white">Recent Orders</div>
        <div class="divide-y divide-slate-700/50">
            @forelse($orders as $o)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm text-white">{{ $o->listing->title ?? 'Deleted listing' }}</p>
                    <p class="text-xs text-slate-400">{{ $o->created_at->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-white font-medium">₦{{ number_format($o->amount, 2) }}</p>
                    <span class="text-xs {{ $o->status === 'completed' ? 'text-green-400' : 'text-yellow-400' }}">{{ ucfirst($o->status) }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-slate-500 text-sm">No orders yet</p>
            @endforelse
        </div>
    </div>

    {{-- Tickets --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl">
        <div class="px-5 py-4 border-b border-slate-700 font-semibold text-white">Support Tickets</div>
        <div class="divide-y divide-slate-700/50">
            @forelse($tickets as $t)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm text-white">{{ $t->subject }}</p>
                    <p class="text-xs text-slate-400">{{ $t->created_at->diffForHumans() }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $t->status === 'open' ? 'bg-yellow-900/50 text-yellow-400' : 'bg-green-900/50 text-green-400' }}">
                    {{ ucfirst(str_replace('_',' ',$t->status)) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-slate-500 text-sm">No tickets</p>
            @endforelse
        </div>
    </div>

    {{-- Wishlist --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl">
        <div class="px-5 py-4 border-b border-slate-700 font-semibold text-white">Wishlist</div>
        <div class="divide-y divide-slate-700/50">
            @forelse($wishlist as $w)
            <div class="px-5 py-3 flex items-center justify-between">
                <p class="text-sm text-white">{{ $w->listing->title ?? 'Deleted listing' }}</p>
                <p class="text-sm text-white font-medium">₦{{ number_format($w->listing->price ?? 0, 2) }}</p>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-slate-500 text-sm">Wishlist empty</p>
            @endforelse
        </div>
    </div>

    {{-- Notifications --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl">
        <div class="px-5 py-4 border-b border-slate-700 font-semibold text-white">Notifications</div>
        <div class="divide-y divide-slate-700/50">
            @forelse($notifs as $n)
            <div class="px-5 py-3 flex items-start justify-between gap-3">
                <p class="text-sm text-slate-300">{{ $n->message ?? $n->data }}</p>
                <p class="text-xs text-slate-500 shrink-0">{{ $n->created_at->diffForHumans() }}</p>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-slate-500 text-sm">No notifications</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Admin actions --}}
<div class="mt-6 bg-slate-800 border border-slate-700 rounded-xl p-5">
    <h3 class="font-semibold text-white mb-4">Quick Admin Actions</h3>
    <div class="flex flex-wrap gap-3">
        <button onclick="openModal('modal-wallet-quick')" class="btn-primary">Adjust Wallet</button>
        <button onclick="openModal('modal-status-quick')" class="btn-primary" style="background:#b45309;">Change Status</button>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Permanently delete this user?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete User</button>
        </form>
    </div>
</div>

{{-- Wallet Quick Modal --}}
<div id="modal-wallet-quick" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-white">Wallet Adjustment</h3>
            <button onclick="closeModal('modal-wallet-quick')" class="text-slate-400 hover:text-white text-xl">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.wallet', $user) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Action</label>
                <select name="type"><option value="fund">Fund</option><option value="deduct">Deduct</option></select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Amount (₦)</label>
                <input type="number" name="amount" min="0.01" step="0.01" required>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Description</label>
                <input type="text" name="description" placeholder="Optional">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn-primary">Confirm</button>
                <button type="button" onclick="closeModal('modal-wallet-quick')" class="btn-primary" style="background:#475569;">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Status Quick Modal --}}
<div id="modal-status-quick" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-white">Change Status</h3>
            <button onclick="closeModal('modal-status-quick')" class="text-slate-400 hover:text-white text-xl">&times;</button>
        </div>
        <div class="grid grid-cols-3 gap-3 mb-4">
            @foreach(['active','suspended','banned'] as $st)
            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $st }}">
                <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium border border-slate-600 text-slate-300 hover:border-sky-500 hover:text-white transition-colors">
                    {{ ucfirst($st) }}
                </button>
            </form>
            @endforeach
        </div>
        <button onclick="closeModal('modal-status-quick')" class="btn-primary w-full" style="background:#475569;">Close</button>
    </div>
</div>

@endsection
