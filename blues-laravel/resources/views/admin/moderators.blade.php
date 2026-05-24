@extends('layouts.admin')
@section('title','Moderators')
@section('page-title','Moderators')
@section('content')

@if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-900/40 border border-green-700/50 text-green-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-5 flex items-center gap-3 bg-red-900/40 border border-red-700/50 text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
        {{ session('error') }}
    </div>
@endif

<div class="flex justify-end mb-5">
    <button onclick="openModal('modal-add-moderator')" class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Moderator
    </button>
</div>

{{-- Moderators Table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden mb-8">
    <div class="px-5 py-3 border-b border-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <h3 class="text-white font-semibold text-sm">Moderators <span class="text-slate-400 font-normal">({{ $moderators->count() }})</span></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                <th class="px-5 py-3 text-left">Account</th>
                <th class="px-5 py-3 text-left">Role</th>
                <th class="px-5 py-3 text-left">Last Login</th>
                <th class="px-5 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($moderators as $mod)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-sky-800 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                {{ strtoupper(substr($mod->display_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $mod->display_name }}</p>
                                <p class="text-slate-400 text-xs">{{ $mod->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-900/50 text-sky-300 border border-sky-700/40">Moderator</span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs">
                        {{ $mod->last_login ? \Carbon\Carbon::parse($mod->last_login)->format('d M Y H:i') : 'Never' }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            {{-- Promote to Admin --}}
                            <form method="POST" action="{{ route('admin.moderators.role', $mod) }}" class="inline">
                                @csrf
                                <input type="hidden" name="role" value="admin">
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-medium bg-purple-900/40 text-purple-300 hover:bg-purple-900/70 border border-purple-700/40 transition-colors"
                                    title="Promote to Admin"
                                    onclick="return confirm('Promote {{ addslashes($mod->display_name) }} to Admin?')">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    Promote
                                </button>
                            </form>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.moderators.destroy', $mod) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 rounded text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors"
                                    title="Remove account"
                                    onclick="return confirm('Remove {{ addslashes($mod->display_name) }}? This cannot be undone.')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500 text-sm">No moderators yet. Create one using the button above.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Admins Table (can be demoted) --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <h3 class="text-white font-semibold text-sm">Admin Accounts <span class="text-slate-400 font-normal">({{ $admins->count() }})</span></h3>
        <p class="ml-auto text-xs text-slate-500">Admins can be set as Moderators from here</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                <th class="px-5 py-3 text-left">Account</th>
                <th class="px-5 py-3 text-left">Role</th>
                <th class="px-5 py-3 text-left">Last Login</th>
                <th class="px-5 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($admins as $admin)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-800 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                {{ strtoupper(substr($admin->display_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $admin->display_name }}
                                    @if($admin->id == session('admin_id'))
                                        <span class="ml-1 text-xs text-slate-500">(you)</span>
                                    @endif
                                </p>
                                <p class="text-slate-400 text-xs">{{ $admin->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-900/50 text-purple-300 border border-purple-700/40">Admin</span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs">
                        {{ $admin->last_login ? \Carbon\Carbon::parse($admin->last_login)->format('d M Y H:i') : 'Never' }}
                    </td>
                    <td class="px-5 py-3">
                        @if($admin->id != session('admin_id'))
                            <form method="POST" action="{{ route('admin.moderators.role', $admin) }}" class="inline">
                                @csrf
                                <input type="hidden" name="role" value="moderator">
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-medium bg-slate-700 text-slate-300 hover:bg-slate-600 border border-slate-600 transition-colors"
                                    title="Set as Moderator"
                                    onclick="return confirm('Set {{ addslashes($admin->display_name) }} as Moderator? They will lose admin access.')">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                    Set Moderator
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-slate-600">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500 text-sm">No admin accounts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Moderator Modal --}}
<div id="modal-add-moderator" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-white">New Moderator</h3>
            <button onclick="closeModal('modal-add-moderator')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        @if($errors->any())
            <div class="mb-4 bg-red-900/40 border border-red-700/50 text-red-300 px-4 py-3 rounded-lg text-xs">
                <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.moderators.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Display Name *</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}" required placeholder="e.g. John Doe">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="moderator@example.com">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Password *</label>
                <input type="password" name="password" required placeholder="Minimum 6 characters">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modal-add-moderator')" class="px-4 py-2 rounded-lg text-sm text-slate-400 hover:text-white border border-slate-600 hover:border-slate-500 transition-colors">Cancel</button>
                <button type="submit" class="btn-primary">Create Moderator</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',()=>openModal('modal-add-moderator'));</script>
@endif

@endsection
