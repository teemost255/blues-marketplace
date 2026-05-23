@extends('layouts.admin')
@section('title','Audit Log')
@section('page-title','Audit Log')
@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Admin</th>
                <th class="px-6 py-3 text-left">Action</th>
                <th class="px-6 py-3 text-left">Target</th>
                <th class="px-6 py-3 text-left">IP</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-6 py-3 text-slate-300">{{ $log->admin_email ?? 'System' }}</td>
                    <td class="px-6 py-3 text-white">{{ $log->action }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ $log->target_type ? $log->target_type.'#'.$log->target_id : '—' }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No audit log entries</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $logs->links() }}</div>
</div>
@endsection
