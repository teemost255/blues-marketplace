@extends('layouts.admin')
@section('title', 'SMS API Log')
@section('page-title', 'HeroSMS API Log')

@section('content')
<style>
    .log-entry { border-radius:.75rem; padding:1rem 1.25rem; margin-bottom:.75rem; border:1px solid; transition:border-color .2s; }
    .log-entry:hover { border-color:#334155 !important; }
    .log-info    { background:#0f172a; border-color:#1e3a5f; }
    .log-warning { background:#1c1400; border-color:#854d0e; }
    .log-error   { background:#1c0a0a; border-color:#7f1d1d; }
    .log-debug   { background:#0f172a; border-color:#1e293b; }
    .level-badge { font-size:.65rem; font-weight:700; padding:.15rem .55rem; border-radius:999px; text-transform:uppercase; letter-spacing:.05em; }
    .badge-info    { background:#1e3a5f; color:#60a5fa; }
    .badge-warning { background:#422006; color:#fbbf24; }
    .badge-error   { background:#450a0a; color:#f87171; }
    .badge-debug   { background:#1e293b; color:#94a3b8; }
    .context-box { font-family:monospace; font-size:.75rem; color:#94a3b8; background:#050d1a; border:1px solid #1e3a5f; border-radius:.5rem; padding:.75rem 1rem; margin-top:.75rem; white-space:pre-wrap; word-break:break-all; max-height:180px; overflow-y:auto; display:none; }
    .context-box.open { display:block; }
    .entry-msg { font-size:.875rem; color:#e2e8f0; font-family:monospace; word-break:break-all; }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; display:inline-block; animation:pulse-dot 1.5s infinite; }
    .live-dot.paused { background:#6b7280; animation:none; }
    @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
    .filter-btn { padding:.4rem 1rem; border-radius:.5rem; font-size:.8rem; font-weight:600; border:1px solid; cursor:pointer; transition:all .15s; }
    .filter-btn.active { background:#1e3a5f; border-color:#3b82f6; color:#60a5fa; }
    .filter-btn:not(.active) { background:#0f172a; border-color:#1e293b; color:#64748b; }
    .filter-btn:hover:not(.active) { border-color:#334155; color:#94a3b8; }
    #log-feed { min-height:200px; }
    .empty-log { text-align:center; color:#475569; padding:3rem 1rem; }
    .highlight { background:#1a2f1a; }
</style>

{{-- Header bar --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <span class="live-dot" id="live-dot"></span>
        <span class="text-sm text-slate-400" id="live-label">Live — refreshing every 4s</span>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button class="filter-btn active" data-level="all"     onclick="setFilter('all',this)">All</button>
        <button class="filter-btn"        data-level="info"    onclick="setFilter('info',this)">Info</button>
        <button class="filter-btn"        data-level="warning" onclick="setFilter('warning',this)">Warning</button>
        <button class="filter-btn"        data-level="error"   onclick="setFilter('error',this)">Error</button>
        <button class="filter-btn"        data-level="debug"   onclick="setFilter('debug',this)">Debug</button>
        <button onclick="togglePause()" id="pause-btn"
                class="filter-btn" style="border-color:#334155;color:#94a3b8;background:#0f172a;">
            ⏸ Pause
        </button>
        <button onclick="clearDisplay()"
                class="filter-btn" style="border-color:#334155;color:#94a3b8;background:#0f172a;">
            Clear
        </button>
    </div>
</div>

{{-- Stats bar --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5" id="stats-bar">
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
        <p class="text-xl font-bold text-white" id="stat-total">{{ count($entries) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total Entries</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
        <p class="text-xl font-bold text-blue-400" id="stat-info">{{ collect($entries)->where('level','info')->count() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Info</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
        <p class="text-xl font-bold text-yellow-400" id="stat-warning">{{ collect($entries)->where('level','warning')->count() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Warnings</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
        <p class="text-xl font-bold text-red-400" id="stat-error">{{ collect($entries)->where('level','error')->count() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Errors</p>
    </div>
</div>

{{-- Search --}}
<div class="mb-4">
    <input type="text" id="search-input" placeholder="Search log messages…"
           oninput="applyFilters()"
           class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500">
</div>

{{-- Log feed --}}
<div id="log-feed">
    @forelse($entries as $entry)
    @php
        $cls   = match($entry['level']) { 'warning'=>'log-warning','error'=>'log-error','debug'=>'log-debug',default=>'log-info' };
        $badge = match($entry['level']) { 'warning'=>'badge-warning','error'=>'badge-error','debug'=>'badge-debug',default=>'badge-info' };
        $hasCtx = !empty(trim($entry['context']));
        $id     = 'entry-'.md5($entry['time'].$entry['message']);
    @endphp
    <div class="log-entry {{ $cls }}" data-level="{{ $entry['level'] }}" data-ts="{{ $entry['ts'] }}">
        <div class="flex items-start gap-3">
            <span class="level-badge {{ $badge }} mt-0.5 flex-shrink-0">{{ $entry['level'] }}</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-xs text-slate-500 font-mono">{{ $entry['time'] }}</span>
                    @if($hasCtx)
                    <button onclick="toggleCtx('{{ $id }}')"
                            class="text-xs text-slate-500 hover:text-slate-300 transition-colors flex-shrink-0">
                        + details
                    </button>
                    @endif
                </div>
                <p class="entry-msg">{{ $entry['message'] }}</p>
                @if($hasCtx)
                <div class="context-box" id="{{ $id }}">{{ trim($entry['context']) }}</div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="empty-log" id="empty-msg">
        <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-slate-500 font-medium">No HeroSMS log entries yet.</p>
        <p class="text-slate-600 text-sm mt-1">API calls will appear here as they happen.</p>
    </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
const STREAM_URL = '{{ route("admin.herosms-log.stream") }}';
let activeFilter = 'all';
let paused       = false;
let lastTs       = {{ count($entries) ? $entries[0]['ts'] : 0 }};
let allEntries   = [];   // JS-side copy for filtering

// Seed from server-rendered entries
document.querySelectorAll('#log-feed .log-entry').forEach(el => {
    allEntries.push({
        ts:      parseInt(el.dataset.ts),
        level:   el.dataset.level,
        html:    el.outerHTML,
    });
});

/* ── Toggle context details ── */
function toggleCtx(id) {
    const box = document.getElementById(id);
    if (!box) return;
    box.classList.toggle('open');
    const btn = box.closest('.log-entry').querySelector('button');
    if (btn) btn.textContent = box.classList.contains('open') ? '− details' : '+ details';
}

/* ── Filter by level ── */
function setFilter(level, btn) {
    activeFilter = level;
    document.querySelectorAll('.filter-btn[data-level]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

/* ── Apply level + search filter to #log-feed ── */
function applyFilters() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const entries = document.querySelectorAll('#log-feed .log-entry');
    let visible = 0;

    entries.forEach(el => {
        const levelOk  = activeFilter === 'all' || el.dataset.level === activeFilter;
        const searchOk = !search || el.textContent.toLowerCase().includes(search);
        el.style.display = (levelOk && searchOk) ? '' : 'none';
        if (levelOk && searchOk) visible++;
    });

    const emptyMsg = document.getElementById('empty-msg');
    if (emptyMsg) emptyMsg.style.display = visible === 0 && entries.length > 0 ? 'block' : 'none';
}

/* ── Pause / Resume ── */
function togglePause() {
    paused = !paused;
    const dot = document.getElementById('live-dot');
    const lbl = document.getElementById('live-label');
    const btn = document.getElementById('pause-btn');
    dot.classList.toggle('paused', paused);
    lbl.textContent = paused ? 'Paused' : 'Live — refreshing every 4s';
    btn.textContent = paused ? '▶ Resume' : '⏸ Pause';
}

/* ── Clear display (client-side only) ── */
function clearDisplay() {
    document.querySelectorAll('#log-feed .log-entry').forEach(el => el.remove());
    allEntries = [];
    updateStats();
}

/* ── Build an entry element from server JSON ── */
function buildEntryEl(e) {
    const levelClass = { warning:'log-warning', error:'log-error', debug:'log-debug' }[e.level] ?? 'log-info';
    const badgeClass = { warning:'badge-warning', error:'badge-error', debug:'badge-debug' }[e.level] ?? 'badge-info';
    const id = 'entry-' + e.ts + '-' + Math.random().toString(36).slice(2,7);
    const hasCtx = e.context && e.context.trim().length > 0;

    const div = document.createElement('div');
    div.className = `log-entry ${levelClass} highlight`;
    div.dataset.level = e.level;
    div.dataset.ts    = e.ts;
    div.innerHTML = `
        <div class="flex items-start gap-3">
            <span class="level-badge ${badgeClass} mt-0.5 flex-shrink-0">${e.level}</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-xs text-slate-500 font-mono">${e.time}</span>
                    ${hasCtx ? `<button onclick="toggleCtx('${id}')" class="text-xs text-slate-500 hover:text-slate-300 transition-colors flex-shrink-0">+ details</button>` : ''}
                </div>
                <p class="entry-msg">${escHtml(e.message)}</p>
                ${hasCtx ? `<div class="context-box" id="${id}">${escHtml(e.context.trim())}</div>` : ''}
            </div>
        </div>`;

    // Remove highlight after animation
    setTimeout(() => div.classList.remove('highlight'), 2000);
    return div;
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

/* ── Update stats counters ── */
function updateStats() {
    const entries = document.querySelectorAll('#log-feed .log-entry');
    let info=0, warn=0, err=0;
    entries.forEach(el => {
        if (el.dataset.level === 'info')    info++;
        if (el.dataset.level === 'warning') warn++;
        if (el.dataset.level === 'error')   err++;
    });
    document.getElementById('stat-total').textContent   = entries.length;
    document.getElementById('stat-info').textContent    = info;
    document.getElementById('stat-warning').textContent = warn;
    document.getElementById('stat-error').textContent   = err;
}

/* ── Live polling ── */
async function poll() {
    if (paused) return;
    try {
        const r    = await fetch(`${STREAM_URL}?since=${lastTs}`);
        const data = await r.json();

        if (data.entries && data.entries.length > 0) {
            const feed = document.getElementById('log-feed');
            const emptyMsg = document.getElementById('empty-msg');
            if (emptyMsg) emptyMsg.remove();

            data.entries.forEach(e => {
                if (e.ts > lastTs) lastTs = e.ts;
                const el = buildEntryEl(e);
                feed.insertBefore(el, feed.firstChild);
            });

            applyFilters();
            updateStats();
        }
    } catch (_) {}
}

setInterval(poll, 4000);
</script>
@endpush
