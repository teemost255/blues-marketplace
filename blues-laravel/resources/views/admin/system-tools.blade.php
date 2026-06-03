@extends('layouts.admin')
@section('title', 'System Tools')
@section('page-title', 'System Tools')

@section('content')
<div class="space-y-6">

    {{-- Actions Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Run Migrations --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <div class="flex items-start gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4m16 0c0 2.21-3.582 4-8 4"/></svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm">Run Migrations</h3>
                    <p class="text-slate-400 text-xs mt-0.5">Creates any missing database tables. Safe to run multiple times.</p>
                </div>
            </div>
            <button onclick="runAction('migrate')"
                    class="w-full bg-green-600 hover:bg-green-500 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                Run Migrations
            </button>
        </div>

        {{-- Clear Caches --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <div class="flex items-start gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm">Clear All Caches</h3>
                    <p class="text-slate-400 text-xs mt-0.5">Clears config, route, view, and app cache. Use after deploying changes.</p>
                </div>
            </div>
            <button onclick="runAction('clear-caches')"
                    class="w-full bg-yellow-600 hover:bg-yellow-500 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                Clear Caches
            </button>
        </div>
    </div>

    {{-- Output Box --}}
    <div id="output-box" class="hidden bg-slate-900 border border-slate-600 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
            <span id="output-label" class="text-xs text-slate-400 font-medium uppercase tracking-wider">Output</span>
            <button onclick="document.getElementById('output-box').classList.add('hidden')" class="text-slate-500 hover:text-slate-300 text-xs">✕ Dismiss</button>
        </div>
        <pre id="output-text" class="text-sm text-green-300 whitespace-pre-wrap font-mono leading-relaxed"></pre>
    </div>

    {{-- Database Tables --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <h3 class="text-white font-semibold text-sm mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4m16 0c0 2.21-3.582 4-8 4"/></svg>
            Database Tables ({{ count($tables) }} found)
        </h3>
        @if(count($tables))
            @php
                $required = ['users','wallets','wallet_transactions','virtual_number_orders','purchases','listings','notifications','settings','migrations'];
            @endphp
            <div class="flex flex-wrap gap-2">
                @foreach($tables as $table)
                    @php $isRequired = in_array($table, $required); @endphp
                    <span class="px-2 py-1 rounded text-xs font-mono
                        {{ $isRequired ? 'bg-green-900/50 text-green-300 border border-green-700' : 'bg-slate-700 text-slate-300' }}">
                        {{ $table }}
                        @if($isRequired) ✓ @endif
                    </span>
                @endforeach
            </div>
            @php $missing = array_diff($required, $tables); @endphp
            @if(count($missing))
                <div class="mt-3 p-3 bg-red-900/30 border border-red-700 rounded-lg">
                    <p class="text-red-400 text-xs font-semibold">⚠ Missing required tables — run migrations above:</p>
                    <p class="text-red-300 text-xs font-mono mt-1">{{ implode(', ', $missing) }}</p>
                </div>
            @else
                <p class="text-green-400 text-xs mt-3">✓ All required tables are present.</p>
            @endif
        @else
            <p class="text-slate-400 text-sm">Could not read table list.</p>
        @endif
    </div>

    {{-- Laravel Error Log --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Laravel Error Log (last 100 lines)
            </h3>
            <button onclick="refreshLog()" class="text-xs text-brand hover:underline">↻ Refresh</button>
        </div>
        <div class="bg-slate-900 rounded-lg p-3 max-h-96 overflow-y-auto" id="log-container">
            <pre id="log-output" class="text-xs font-mono whitespace-pre-wrap leading-relaxed">@foreach($logLines as $line)
@php
    $cls = 'text-slate-300';
    if (str_contains($line, '.ERROR') || str_contains($line, 'ERROR:')) $cls = 'text-red-400';
    elseif (str_contains($line, '.WARNING') || str_contains($line, 'WARN')) $cls = 'text-yellow-400';
    elseif (str_contains($line, 'Stack trace') || str_contains($line, '#')) $cls = 'text-slate-500';
@endphp
<span class="{{ $cls }}">{{ $line }}</span>
@endforeach</pre>
        </div>
        <p class="text-slate-500 text-xs mt-2">Errors are red · Warnings are yellow · Showing most recent entries</p>
    </div>

</div>

<script>
async function runAction(action) {
    const url    = action === 'migrate' ? '{{ route("admin.system-tools.migrate") }}' : '{{ route("admin.system-tools.clear-caches") }}';
    const box    = document.getElementById('output-box');
    const label  = document.getElementById('output-label');
    const text   = document.getElementById('output-text');

    label.textContent = action === 'migrate' ? 'Migration Output' : 'Cache Clear Output';
    text.textContent  = 'Running…';
    text.className    = 'text-sm text-yellow-300 whitespace-pre-wrap font-mono leading-relaxed';
    box.classList.remove('hidden');

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json();
        text.textContent = data.output || (data.success ? 'Done.' : 'Unknown error.');
        text.className   = `text-sm whitespace-pre-wrap font-mono leading-relaxed ${data.success ? 'text-green-300' : 'text-red-400'}`;

        // If migrations ran, reload the page so the table list refreshes
        if (action === 'migrate' && data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    } catch (e) {
        text.textContent = 'Request failed: ' + e.message;
        text.className   = 'text-sm text-red-400 whitespace-pre-wrap font-mono leading-relaxed';
    }
}

async function refreshLog() {
    try {
        const res  = await fetch('{{ route("admin.system-tools.log") }}?lines=100', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        const pre  = document.getElementById('log-output');
        pre.innerHTML = data.lines.map(line => {
            let cls = 'text-slate-300';
            if (line.includes('.ERROR') || line.includes('ERROR:')) cls = 'text-red-400';
            else if (line.includes('.WARNING') || line.includes('WARN'))  cls = 'text-yellow-400';
            else if (line.includes('Stack trace') || /^ +#\d/.test(line)) cls = 'text-slate-500';
            return `<span class="${cls}">${line.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</span>`;
        }).join('\n');

        // Scroll to bottom
        const container = document.getElementById('log-container');
        container.scrollTop = container.scrollHeight;
    } catch(e) {
        console.error('Log refresh failed', e);
    }
}

// Auto-scroll log to bottom on load
window.addEventListener('DOMContentLoaded', () => {
    const c = document.getElementById('log-container');
    c.scrollTop = c.scrollHeight;
});
</script>
@endsection
