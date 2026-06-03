@extends('layouts.admin')
@section('title', 'Virtual Number Settings')
@section('page-title', 'Virtual Number Settings')

@section('content')

<div class="max-w-3xl space-y-6">

    {{-- Provider Status Card --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base">HeroSMS Provider</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Virtual phone number service for SMS verification</p>
                </div>
            </div>

            @if($balance !== null)
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-xs text-slate-400">Provider Balance</p>
                    <p class="text-lg font-bold text-sky-400">${{ number_format($balance, 4) }}</p>
                </div>
                <a href="https://hero-sms.com" target="_blank"
                   class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg text-white transition-all hover:opacity-90"
                   style="background:#3b82f6;">
                    Top up
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
            @else
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-400 bg-amber-900/30 border border-amber-700/40 px-3 py-1.5 rounded-lg">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    API key not configured
                </span>
            </div>
            @endif
        </div>

        {{-- Live connection test --}}
        <div class="mt-4 pt-4 border-t border-slate-700 flex items-center gap-3">
            <button onclick="testConnection()"
                    id="test-btn"
                    class="flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg text-white transition-all hover:opacity-90"
                    style="background:#1d4ed8;">
                <svg class="w-3.5 h-3.5" id="test-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Test Connection
            </button>
            <div id="test-result" class="text-xs text-slate-400 hidden"></div>
        </div>
    </div>

    {{-- Settings Form --}}
    <form method="POST" action="{{ route('admin.virtual-number-settings.update') }}">
        @csrf

        {{-- Enable / Disable --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white">Enable Virtual Numbers</h3>
                    <p class="text-xs text-slate-400 mt-1">Show the Virtual Numbers section in the user dashboard and allow purchases</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="herosms_enabled" id="vn-toggle" value="1" class="sr-only"
                           {{ $settings['herosms_enabled'] === '1' ? 'checked' : '' }}>
                    <div id="vn-toggle-bg" class="w-12 h-6 rounded-full transition-all duration-200 relative"
                         style="background:{{ $settings['herosms_enabled'] === '1' ? '#3b82f6' : '#475569' }}">
                        <div id="vn-toggle-dot" class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                             style="transform:{{ $settings['herosms_enabled'] === '1' ? 'translateX(1.5rem)' : 'translateX(0)' }}"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- API Key --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">API Credentials</h3>
                    <p class="text-xs text-slate-400">Authentication key for the HeroSMS provider</p>
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">HeroSMS API Key</label>
                <div class="relative">
                    <input type="password" name="herosms_api_key" id="herosms-key-input"
                           value="{{ $settings['herosms_api_key'] }}"
                           placeholder="Enter your HeroSMS API key"
                           class="font-mono text-xs pr-10">
                    <button type="button" onclick="toggleField('herosms-key-input')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">
                    Get your key from
                    <a href="https://hero-sms.com" target="_blank" class="text-sky-400 hover:underline">hero-sms.com</a>
                    → Account → API Access
                </p>
            </div>
        </div>

        {{-- Pricing & Refund --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-green-900/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Pricing & Refunds</h3>
                    <p class="text-xs text-slate-400">Control what users pay and how refunds work</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Price Per Number (₦)</label>
                    <input type="number" name="herosms_number_price"
                           value="{{ $settings['herosms_number_price'] }}"
                           min="0" step="0.01" placeholder="200">
                    <p class="text-xs text-slate-500 mt-1">Amount deducted from user's wallet per number request</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Cancellation Refund (%)</label>
                    <input type="number" name="herosms_cancel_refund_pct"
                           value="{{ $settings['herosms_cancel_refund_pct'] }}"
                           min="0" max="100" placeholder="50">
                    <p class="text-xs text-slate-500 mt-1">% of cost refunded if user cancels before SMS arrives</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Markup on Provider Cost (%)</label>
                    <input type="number" name="herosms_markup_pct"
                           value="{{ $settings['herosms_markup_pct'] ?? '0' }}"
                           min="0" max="500" step="1" placeholder="0">
                    <p class="text-xs text-slate-500 mt-1">Optional extra markup percentage on top of provider cost (informational)</p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg" style="background:#0f172a;border:1px solid #334155;">
                    <svg class="w-4 h-4 text-sky-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-slate-400">
                        Setting a higher price than your provider cost lets you earn a margin on each number rental.
                        Set refund to 100% for full cancellation refunds, or 0% for no refunds.
                    </p>
                </div>
            </div>
        </div>

        {{-- Limits & Behaviour --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-purple-900/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Limits & Behaviour</h3>
                    <p class="text-xs text-slate-400">Control usage limits for each user</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Number Expiry (minutes)</label>
                    <input type="number" name="herosms_expiry_minutes"
                           value="{{ $settings['herosms_expiry_minutes'] ?? '20' }}"
                           min="5" max="60" placeholder="20">
                    <p class="text-xs text-slate-500 mt-1">How long before an unreceived number expires (5–60 min)</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Max Active Rentals Per User</label>
                    <input type="number" name="herosms_max_active"
                           value="{{ $settings['herosms_max_active'] ?? '3' }}"
                           min="1" max="10" placeholder="3">
                    <p class="text-xs text-slate-500 mt-1">Maximum simultaneous active (waiting) rentals per user</p>
                </div>
            </div>
        </div>

        {{-- Save --}}
        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary px-8 py-3 text-base font-semibold">
                Save Settings
            </button>
            <a href="{{ route('admin.virtual-numbers') }}"
               class="text-sm text-slate-400 hover:text-white transition-colors">
                View Orders →
            </a>
        </div>
    </form>

    {{-- Quick Stats --}}
    @php
        $stats = [
            'total'     => \App\Models\VirtualNumberOrder::count(),
            'active'    => \App\Models\VirtualNumberOrder::whereIn('status',['waiting','received'])->count(),
            'completed' => \App\Models\VirtualNumberOrder::where('status','completed')->count(),
            'revenue'   => \App\Models\VirtualNumberOrder::whereIn('status',['waiting','received','completed'])->sum('cost'),
        ];
    @endphp
    <div>
        <h3 class="text-sm font-semibold text-slate-300 mb-3">Quick Stats</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
                <p class="text-xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Total Orders</p>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
                <p class="text-xl font-bold text-blue-400">{{ number_format($stats['active']) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Active Now</p>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
                <p class="text-xl font-bold text-green-400">{{ number_format($stats['completed']) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Completed</p>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
                <p class="text-xl font-bold text-white">₦{{ number_format($stats['revenue'], 0) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Total Revenue</p>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-2">
            <a href="{{ route('admin.virtual-numbers') }}" class="text-sky-400 hover:underline">View all orders →</a>
        </p>
    </div>

</div>

<script>
document.getElementById('vn-toggle').addEventListener('change', function() {
    document.getElementById('vn-toggle-bg').style.background  = this.checked ? '#3b82f6' : '#475569';
    document.getElementById('vn-toggle-dot').style.transform  = this.checked ? 'translateX(1.5rem)' : 'translateX(0)';
});

function toggleField(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

async function testConnection() {
    const btn    = document.getElementById('test-btn');
    const result = document.getElementById('test-result');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:.5rem;height:.5rem;border-radius:50%;background:#60a5fa;animation:pulse 1s infinite"></span> Testing…';
    result.classList.remove('hidden');
    result.textContent = 'Connecting to HeroSMS…';
    result.style.color = '#94a3b8';

    try {
        const r = await fetch('{{ route("admin.virtual-number-settings.test") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const d = await r.json();
        result.textContent = d.message;
        result.style.color = d.success ? '#4ade80' : '#f87171';
    } catch (e) {
        result.textContent = 'Request failed. Check network.';
        result.style.color = '#f87171';
    }

    btn.disabled = false;
    btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Test Connection';
}
</script>
@endsection
