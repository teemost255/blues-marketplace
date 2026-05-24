@extends('layouts.dashboard')
@section('title', 'Refer a Friend')
@section('page-title', 'Refer a Friend')
@section('content')

{{-- Hero stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-brand/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-white">{{ $referralCount }}</p>
            <p class="text-sm text-slate-400">Friends Referred</p>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-green-400">₦{{ number_format($totalEarned, 2) }}</p>
            <p class="text-sm text-slate-400">Total Earned</p>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-yellow-400">
                {{ $bonusRate > 0 ? '₦' . number_format($bonusRate, 2) : 'Active' }}
            </p>
            <p class="text-sm text-slate-400">Bonus Per Referral</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Share card --}}
    <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl p-6">
        <h2 class="font-bold text-white text-base mb-1">Your Referral Link</h2>
        <p class="text-slate-400 text-sm mb-6">Share this link — when a friend registers using it, you both benefit.</p>

        {{-- Link --}}
        <div class="mb-5">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Referral Link</label>
            <div class="flex items-center gap-2">
                <input id="referral-link" type="text" readonly
                    value="{{ url('/r/' . $profile->referral_code) }}"
                    class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-slate-200 text-sm font-mono select-all focus:outline-none focus:border-brand">
                <button onclick="copyText('referral-link', 'link-btn', 'link-ok')"
                    id="link-btn"
                    class="flex-shrink-0 flex items-center gap-1.5 bg-brand hover:bg-brand-dark text-white px-4 py-3 rounded-lg text-sm font-semibold transition-colors">
                    <svg id="link-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Copy
                </button>
            </div>
            <p id="link-ok" class="text-green-400 text-xs mt-1.5 hidden">✓ Link copied!</p>
        </div>

        {{-- Code --}}
        <div class="mb-6">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Referral Code</label>
            <div class="flex items-center gap-2">
                <code id="referral-code-display"
                    class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-brand font-mono font-bold text-lg tracking-[0.3em] select-all">{{ $profile->referral_code }}</code>
                <button onclick="copyText('referral-code-display', 'code-btn', 'code-ok')"
                    id="code-btn"
                    class="flex-shrink-0 flex items-center gap-1.5 bg-slate-700 hover:bg-slate-600 text-white px-4 py-3 rounded-lg text-sm font-semibold transition-colors">
                    <svg id="code-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Copy
                </button>
            </div>
            <p id="code-ok" class="text-green-400 text-xs mt-1.5 hidden">✓ Code copied!</p>
        </div>

        {{-- Share buttons --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Share via</label>
            <div class="flex flex-wrap gap-2">
                @php
                    $link    = urlencode(url('/r/' . $profile->referral_code));
                    $message = urlencode('Join Blues Marketplace using my referral link and start buying verified digital accounts!');
                @endphp
                <a href="https://wa.me/?text={{ $message }}%20{{ $link }}" target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-green-700/20 hover:bg-green-700/40 border border-green-700/40 text-green-400 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
                <a href="https://t.me/share/url?url={{ $link }}&text={{ $message }}" target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-sky-700/20 hover:bg-sky-700/40 border border-sky-700/40 text-sky-400 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    Telegram
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ $message }}&url={{ $link }}" target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-slate-700/40 hover:bg-slate-700/70 border border-slate-600 text-slate-300 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.631 5.905-5.631zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X / Twitter
                </a>
            </div>
        </div>
    </div>

    {{-- How it works --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <h3 class="font-bold text-white text-base mb-5">How it works</h3>
        <ol class="space-y-5">
            @foreach([
                ['step' => '1', 'color' => 'bg-brand', 'title' => 'Copy your link', 'desc' => 'Share your unique referral link or code with friends.'],
                ['step' => '2', 'color' => 'bg-purple-500', 'title' => 'Friend registers', 'desc' => 'They sign up using your link — it tracks automatically.'],
                ['step' => '3', 'color' => 'bg-green-500', 'title' => 'You earn', 'desc' => $bonusRate > 0
                    ? '₦' . number_format($bonusRate, 2) . ' is instantly added to your wallet.'
                    : 'A bonus is credited to your wallet instantly.'],
                ['step' => '4', 'color' => 'bg-yellow-500', 'title' => 'No limit', 'desc' => 'Refer as many people as you like — earnings stack up.'],
            ] as $s)
            <li class="flex gap-3">
                <span class="flex-shrink-0 w-7 h-7 rounded-full {{ $s['color'] }} text-white text-xs font-bold flex items-center justify-center">{{ $s['step'] }}</span>
                <div>
                    <p class="text-sm font-semibold text-white">{{ $s['title'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $s['desc'] }}</p>
                </div>
            </li>
            @endforeach
        </ol>
    </div>
</div>

{{-- Referred friends --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold text-white">Friends You Referred</h3>
            <span class="text-xs text-slate-400">{{ $referralCount }} total</span>
        </div>
        @if($referrals->isEmpty())
        <div class="text-center py-12">
            <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-slate-400 text-sm">No referrals yet. Share your link to get started!</p>
        </div>
        @else
        <ul class="divide-y divide-slate-700/50">
            @foreach($referrals as $ref)
            <li class="flex items-center gap-3 px-6 py-3">
                <div class="w-8 h-8 rounded-full bg-brand/20 flex items-center justify-center text-brand font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($ref->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate">{{ $ref->name }}</p>
                    <p class="text-xs text-slate-500">Joined {{ $ref->created_at->diffForHumans() }}</p>
                </div>
                @if($bonusRate > 0)
                <span class="text-xs text-green-400 font-semibold flex-shrink-0">+₦{{ number_format($bonusRate, 2) }}</span>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- Earnings history --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold text-white">Earnings History</h3>
            <span class="text-xs text-green-400 font-semibold">₦{{ number_format($totalEarned, 2) }} total</span>
        </div>
        @if($earnings->isEmpty())
        <div class="text-center py-12">
            <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <p class="text-slate-400 text-sm">No earnings yet.</p>
        </div>
        @else
        <ul class="divide-y divide-slate-700/50">
            @foreach($earnings as $tx)
            <li class="flex items-center gap-3 px-6 py-3">
                <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-slate-300 truncate">{{ $tx->description }}</p>
                    <p class="text-xs text-slate-500">{{ $tx->created_at->format('M d, Y · H:i') }}</p>
                </div>
                <span class="text-sm font-bold text-green-400 flex-shrink-0">+₦{{ number_format($tx->amount, 2) }}</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>

<script>
function copyText(inputId, btnId, okId) {
    const el = document.getElementById(inputId);
    const text = el.tagName === 'INPUT' ? el.value : el.textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const ok = document.getElementById(okId);
        ok.classList.remove('hidden');
        const btn = document.getElementById(btnId);
        btn.classList.add('opacity-75');
        setTimeout(() => {
            ok.classList.add('hidden');
            btn.classList.remove('opacity-75');
        }, 2500);
    });
}
</script>
@endsection
