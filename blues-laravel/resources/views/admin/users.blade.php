@extends('layouts.admin')
@section('title','Users')
@section('page-title','Users')
@section('content')

{{-- Toolbar --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-1 max-w-xl">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="flex-1">
        <select name="status" class="w-36">
            <option value="">All statuses</option>
            @foreach(['active','suspended','banned'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="btn-primary">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.users') }}" class="btn-primary" style="background:#475569;">Clear</a>
        @endif
    </form>
    <button onclick="openModal('modal-create-user')" class="btn-primary flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New User
    </button>
</div>

{{-- Table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                <th class="px-5 py-3 text-left">User</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-left">Wallet</th>
                <th class="px-5 py-3 text-left">Joined</th>
                <th class="px-5 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-sky-700 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $user->name }}</p>
                                <p class="text-slate-400 text-xs">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="status-{{ $user->status }}">{{ ucfirst($user->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-white font-medium">₦{{ number_format($user->wallet?->balance ?? 0, 2) }}</td>
                    <td class="px-5 py-3 text-slate-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            {{-- View Dashboard --}}
                            <a href="{{ route('admin.impersonate.dashboard', $user) }}" target="_blank"
                               title="View user dashboard"
                               class="p-1.5 rounded text-slate-400 hover:text-sky-400 hover:bg-slate-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            {{-- Wallet --}}
                            <button onclick="openModal('modal-wallet-{{ $user->id }}')" title="Adjust wallet"
                                class="p-1.5 rounded text-slate-400 hover:text-green-400 hover:bg-slate-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </button>
                            {{-- Status --}}
                            <button onclick="openModal('modal-status-{{ $user->id }}')" title="Change status"
                                class="p-1.5 rounded text-slate-400 hover:text-yellow-400 hover:bg-slate-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                            {{-- Delete --}}
                            <button onclick="openModal('modal-delete-{{ $user->id }}')" title="Delete user"
                                class="p-1.5 rounded text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Wallet Modal --}}
                <div id="modal-wallet-{{ $user->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="font-semibold text-white">Wallet Adjustment — {{ $user->name }}</h3>
                            <button onclick="closeModal('modal-wallet-{{ $user->id }}')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                        </div>
                        <p class="text-sm text-slate-400 mb-4">Current balance: <span class="text-white font-semibold">₦{{ number_format($user->wallet?->balance ?? 0, 2) }}</span></p>
                        <form method="POST" action="{{ route('admin.users.wallet', $user) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Action</label>
                                <select name="type" required>
                                    <option value="fund">Fund (Add money)</option>
                                    <option value="deduct">Deduct (Remove money)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Amount (₦)</label>
                                <input type="number" name="amount" step="0.01" min="0.01" required placeholder="e.g. 5000">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Description (optional)</label>
                                <input type="text" name="description" placeholder="Reason for adjustment">
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="btn-primary">Confirm</button>
                                <button type="button" onclick="closeModal('modal-wallet-{{ $user->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Status Modal --}}
                <div id="modal-status-{{ $user->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="font-semibold text-white">Change Status — {{ $user->name }}</h3>
                            <button onclick="closeModal('modal-status-{{ $user->id }}')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                        </div>
                        <p class="text-sm text-slate-400 mb-4">Current: <span class="status-{{ $user->status }}">{{ ucfirst($user->status) }}</span></p>
                        <div class="grid grid-cols-3 gap-3 mb-5">
                            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium border {{ $user->status === 'active' ? 'bg-green-800 border-green-600 text-green-300' : 'border-slate-600 text-slate-300 hover:border-green-600 hover:text-green-300' }} transition-colors">
                                    ✓ Active
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                @csrf
                                <input type="hidden" name="status" value="suspended">
                                <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium border {{ $user->status === 'suspended' ? 'bg-yellow-800 border-yellow-600 text-yellow-300' : 'border-slate-600 text-slate-300 hover:border-yellow-600 hover:text-yellow-300' }} transition-colors">
                                    ⏸ Suspend
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                @csrf
                                <input type="hidden" name="status" value="banned">
                                <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium border {{ $user->status === 'banned' ? 'bg-red-800 border-red-600 text-red-300' : 'border-slate-600 text-slate-300 hover:border-red-600 hover:text-red-300' }} transition-colors">
                                    ✕ Ban
                                </button>
                            </form>
                        </div>
                        <button onclick="closeModal('modal-status-{{ $user->id }}')" class="btn-primary w-full" style="background:#475569;">Close</button>
                    </div>
                </div>

                {{-- Delete Modal --}}
                <div id="modal-delete-{{ $user->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-white">Delete User</h3>
                            <button onclick="closeModal('modal-delete-{{ $user->id }}')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                        </div>
                        <p class="text-sm text-slate-300 mb-5">Are you sure you want to permanently delete <strong>{{ $user->name }}</strong> ({{ $user->email }})? This cannot be undone.</p>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                            <button onclick="closeModal('modal-delete-{{ $user->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                        </div>
                    </div>
                </div>

            @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-slate-500">No users found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $users->links() }}</div>
</div>

{{-- Create User Modal --}}
<div id="modal-create-user" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-white text-lg">Create New User</h3>
            <button onclick="closeModal('modal-create-user')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Full Name</label>
                <input type="text" name="name" required placeholder="John Doe">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Email Address</label>
                <input type="email" name="email" required placeholder="john@example.com">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Min. 6 characters">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Create User</button>
                <button type="button" onclick="closeModal('modal-create-user')" class="btn-primary" style="background:#475569;">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
