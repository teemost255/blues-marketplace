@extends('layouts.admin')
@section('title','Audit Log')
@section('page-title','Audit Log')
@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search action…" class="flex-1 min-w-40">
    <select name="target_type">
        <option value="">All types</option>
        @foreach(['user','moderator','admin_user','listing','category','setting'] as $t)
            <option value="{{ $t }}" {{ request('target_type') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
        @endforeach
    </select>
    <select name="admin_id">
        <option value="">All admins</option>
        @foreach($adminUsers as $a)
            <option value="{{ $a->id }}" {{ request('admin_id') == $a->id ? 'selected' : '' }}>
                {{ $a->display_name }} ({{ ucfirst($a->role ?? 'admin') }})
            </option>
        @endforeach
    </select>
    <button class="btn-primary">Filter</button>
    @if(request()->hasAny(['search','target_type','admin_id']))
        <a href="{{ route('admin.audit') }}" class="btn-primary" style="background:#475569;">Clear</a>
    @endif
</form>

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                <th class="px-5 py-3 text-left">Admin</th>
                <th class="px-5 py-3 text-left">Action</th>
                <th class="px-5 py-3 text-left">Target</th>
                <th class="px-5 py-3 text-left">IP</th>
                <th class="px-5 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($logs as $log)
                @php
                    $action = $log->action ?? '';
                    $al = strtolower($action);
                    $color = 'text-slate-300';
                    if (str_contains($al, 'fund') || str_contains($al, 'credit') || str_contains($al, 'creat') || str_contains($al, 'promot'))
                        $color = 'text-green-400';
                    elseif (str_contains($al, 'deduct') || str_contains($al, 'debit') || str_contains($al, 'ban') || str_contains($al, 'delet') || str_contains($al, 'remov'))
                        $color = 'text-red-400';
                    elseif (str_contains($al, 'role') || str_contains($al, 'status') || str_contains($al, 'suspend'))
                        $color = 'text-yellow-400';
                @endphp
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                {{ ($log->admin_role ?? '') === 'moderator' ? 'bg-sky-800 text-sky-200' : 'bg-purple-800 text-purple-200' }}">
                                {{ strtoupper(substr($log->admin_display ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white text-xs font-medium leading-none">{{ $log->admin_display ?? 'System' }}</p>
                                @if(($log->admin_role ?? '') === 'moderator')
                                    <span class="text-sky-400 text-xs">Moderator</span>
                                @elseif($log->admin_display)
                                    <span class="text-purple-400 text-xs">Admin</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 max-w-xs">
                        <span class="{{ $color }}">{{ $action }}</span>
                        @if($log->details)
                            <p class="text-slate-500 text-xs mt-0.5 truncate">{{ $log->details }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($log->target_type)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-300">
                                {{ ucfirst(str_replace('_',' ',$log->target_type)) }}
                                @if($log->target_id)<span class="text-slate-500">#{{ $log->target_id }}</span>@endif
                            </span>
                        @else
                            <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs font-mono">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-400 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('M j, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">
                    <p class="text-lg mb-1">No activity yet</p>
                    <p class="text-xs">Admin and moderator actions will be recorded here automatically.</p>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-700">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
