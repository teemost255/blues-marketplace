@extends('layouts.admin')
@section('title', 'Virtual Number Settings')
@section('page-title', 'Virtual Number Settings')

@section('content')

<style>
/* ── Toggle switch ── */
.vn-toggle-wrap { position:relative; display:inline-flex; align-items:center; cursor:pointer; }
.vn-toggle-wrap input[type=checkbox] { position:absolute; opacity:0; width:0; height:0; }
.vn-track {
    width:3rem; height:1.625rem; border-radius:9999px;
    background:#3f3f46;
    transition:background .2s;
    position:relative; flex-shrink:0;
    border: 1px solid rgba(255,255,255,.08);
}
.vn-track::after {
    content:'';
    position:absolute; top:2px; left:2px;
    width:1.125rem; height:1.125rem;
    border-radius:50%; background:#fff;
    box-shadow:0 1px 3px rgba(0,0,0,.4);
    transition:transform .2s;
}
.vn-toggle-wrap input:checked ~ .vn-track { background:#f97316; }
.vn-toggle-wrap input:checked ~ .vn-track::after { transform:translateX(1.375rem); }

/* ── Cards ── */
.vn-card {
    background:#1e293b;
    border:1px solid rgba(255,255,255,.07);
    border-radius:.875rem;
    overflow:hidden;
}
.vn-card-header {
    display:flex; align-items:center; gap:.875rem;
    padding:1.25rem 1.5rem 1rem;
}
.vn-icon-badge {
    width:2.75rem; height:2.75rem; border-radius:.625rem;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.vn-card-body { padding:0 1.5rem 1.5rem; }
.vn-divider { height:1px; background:rgba(255,255,255,.06); margin:0 1.5rem; }
.vn-row { padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; }
.vn-row + .vn-row { border-top:1px solid rgba(255,255,255,.06); }
.vn-row-label { flex:1; min-width:0; }
.vn-row-label p { font-size:.875rem; font-weight:500; color:#f1f5f9; }
.vn-row-label span { font-size:.75rem; color:#64748b; display:block; margin-top:.2rem; line-height:1.4; }

/* ── Section label ── */
.section-label {
    font-size:.7rem; font-weight:700; letter-spacing:.08em;
    color:#64748b; text-transform:uppercase;
    padding: .875rem 1.5rem .5rem;
}

/* ── Field ── */
.vn-field label { display:block; font-size:.75rem; color:#94a3b8; margin-bottom:.375rem; }
.vn-field input,
.vn-field select {
    width:100%;
    background:#0f172a;
    border:1px solid rgba(255,255,255,.1);
    border-radius:.5rem;
    padding:.625rem .875rem;
    color:#f1f5f9;
    font-size:.875rem;
    outline:none;
    transition:border-color .15s;
    appearance:none;
}
.vn-field input:focus,
.vn-field select:focus { border-color:#f97316; }
.vn-field .field-hint { font-size:.72rem; color:#475569; margin-top:.375rem; }
.vn-field .field-link { color:#38bdf8; }
.vn-field .field-link:hover { text-decoration:underline; }

/* ── Password reveal ── */
.pw-wrap { position:relative; }
.pw-wrap input { padding-right:2.75rem; }
.pw-reveal {
    position:absolute; right:.75rem; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer;
    color:#475569; transition:color .15s; padding:.25rem;
    display:flex; align-items:center; justify-content:center;
}
.pw-reveal:hover { color:#94a3b8; }

/* ── Select arrow ── */
.sel-wrap { position:relative; }
.sel-wrap::after {
    content:''; position:absolute; right:.75rem; top:50%; transform:translateY(-50%);
    border:5px solid transparent; border-top-color:#64748b; margin-top:3px;
    pointer-events:none;
}

/* ── Test connection button ── */
#test-btn {
    display:inline-flex; align-items:center; gap:.5rem;
    font-size:.8rem; font-weight:600;
    padding:.5rem 1rem; border-radius:.5rem;
    background:rgba(249,115,22,.15); color:#fb923c;
    border:1px solid rgba(249,115,22,.3);
    cursor:pointer; transition:all .15s;
}
#test-btn:hover { background:rgba(249,115,22,.25); }
#test-btn:disabled { opacity:.5; cursor:not-allowed; }

/* ── Save button ── */
.save-btn {
    display:inline-flex; align-items:center; gap:.5rem;
    font-size:.875rem; font-weight:600;
    padding:.75rem 2rem; border-radius:.625rem;
    background:#f97316; color:#fff;
    border:none; cursor:pointer; transition:background .15s;
}
.save-btn:hover { background:#ea6c0a; }

/* ── Stats bar ── */
.stat-chip {
    background:#0f172a; border:1px solid rgba(255,255,255,.07);
    border-radius:.625rem; padding:.875rem 1.25rem; text-align:center;
}
</style>

<div class="max-w-2xl space-y-4">

    <form method="POST" action="{{ route('admin.virtual-number-settings.update') }}" id="vn-settings-form">
    @csrf

    {{-- ════════════════════════════════════════════
         CARD 1 — Server / API Credentials
    ════════════════════════════════════════════ --}}
    <div class="vn-card">
        <div class="vn-card-header">
            <div class="vn-icon-badge" style="background:linear-gradient(135deg,#c2410c,#f97316)">
                {{-- Phone icon --}}
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Virtual Numbers — HeroSMS</p>
                <p class="text-xs text-slate-400 mt-0.5">API credentials for the virtual number provider. Prices in USD, auto-converted to NGN.</p>
            </div>
        </div>

        <div class="vn-divider"></div>

        <div class="vn-card-body pt-4 space-y-4">
            {{-- API Key --}}
            <div class="vn-field">
                <label>API Key</label>
                <div class="pw-wrap">
                    <input type="password" name="herosms_api_key" id="herosms-key-input"
                           value="{{ $settings['herosms_api_key'] }}"
                           placeholder="••••••••••••••••••••••••••••••">
                    <button type="button" class="pw-reveal" onclick="togglePw('herosms-key-input', this)" title="Show / hide">
                        <svg id="eye-herosms-key-input" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="field-hint">Obtain from your <a href="https://hero-sms.com" target="_blank" class="field-link">HeroSMS</a> provider account settings.</p>
            </div>

            {{-- Exchange Rate --}}
            <div class="vn-field">
                <label>USD → NGN Exchange Rate</label>
                <input type="number" name="herosms_exchange_rate"
                       value="{{ $settings['herosms_exchange_rate'] }}"
                       min="1" step="1" placeholder="1600">
                <p class="field-hint">How many Naira per 1 USD of API cost. E.g. <strong style="color:#f1f5f9;">1600</strong> means $0.25 API cost = ₦400 base price. Update this whenever the exchange rate changes.</p>
            </div>

            {{-- Test connection --}}
            <div class="flex items-center gap-3">
                <button type="button" id="test-btn" onclick="testConnection()">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Test Connection
                </button>
                <span id="test-result" class="text-xs hidden"></span>
            </div>
        </div>

        <div class="vn-divider"></div>

        {{-- Enable toggle row --}}
        <div class="vn-row">
            <div class="vn-row-label">
                <p>Enable HeroSMS</p>
                <span>Turn the provider on or off independently without removing the API key</span>
            </div>
            <label class="vn-toggle-wrap" for="herosms-enabled-toggle">
                <input type="checkbox" name="herosms_enabled" id="herosms-enabled-toggle"
                       value="1" {{ $settings['herosms_enabled'] === '1' ? 'checked' : '' }}>
                <div class="vn-track"></div>
            </label>
        </div>
    </div>


    {{-- ════════════════════════════════════════════
         CARD 2 — Commission & Controls
    ════════════════════════════════════════════ --}}
    <div class="vn-card">
        <div class="vn-card-header">
            <div class="vn-icon-badge" style="background:linear-gradient(135deg,#92400e,#f59e0b)">
                {{-- Dollar / coin icon --}}
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Virtual Numbers — Commission &amp; Controls</p>
                <p class="text-xs text-slate-400 mt-0.5">Applies to all virtual number providers</p>
            </div>
        </div>

        <div class="vn-divider"></div>

        {{-- Platform commission section --}}
        <p class="section-label">Platform Commission</p>

        <div class="vn-card-body space-y-4">
            <p class="text-xs text-slate-400 -mt-1">
                Added on top of the API price and deducted from the user's wallet.
                The API is always charged its original rate from the API wallet.
            </p>

            {{-- Commission type + amount --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="vn-field">
                    <label>Commission Type</label>
                    <div class="sel-wrap">
                        <select name="herosms_commission_type" id="commission-type" onchange="updateCommissionLabel()">
                            <option value="flat"       {{ $settings['herosms_commission_type'] === 'flat'       ? 'selected' : '' }}>Flat amount (₦)</option>
                            <option value="percentage" {{ $settings['herosms_commission_type'] === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        </select>
                    </div>
                </div>
                <div class="vn-field">
                    <label id="commission-amount-label">Commission (<span id="commission-unit">{{ $settings['herosms_commission_type'] === 'percentage' ? '%' : '₦' }}</span>)</label>
                    <input type="number" name="herosms_number_price"
                           id="commission-amount"
                           value="{{ $settings['herosms_number_price'] }}"
                           min="0" step="0.01" placeholder="200">
                    <p class="field-hint">Added on top of the USD→NGN converted API cost. Set 0 for no extra charge.</p>
                </div>
            </div>

            {{-- Live price preview --}}
            <div class="rounded-lg px-3 py-2.5" style="background:#0f172a;border:1px solid rgba(255,255,255,.08);">
                <p class="text-xs text-slate-400 mb-1">Example price preview</p>
                <p class="text-xs text-slate-500">If API cost is <strong style="color:#f1f5f9;" id="preview-usd">$0.25</strong> → Base: <strong style="color:#f1f5f9;" id="preview-base">₦400</strong> + Commission: <strong style="color:#f1f5f9;" id="preview-commission">₦200</strong> = <strong style="color:#fb923c;" id="preview-total">₦600</strong></p>
            </div>

            {{-- Cancellation refund --}}
            <div class="vn-field">
                <label>Cancellation Refund (%)</label>
                <input type="number" name="herosms_cancel_refund_pct"
                       value="{{ $settings['herosms_cancel_refund_pct'] }}"
                       min="0" max="100" placeholder="50">
                <p class="field-hint">% of cost refunded when user cancels before SMS arrives. Set to 0 for no refund, 100 for full refund.</p>
            </div>
        </div>

        <div class="vn-divider"></div>

        {{-- Expiry & limits section --}}
        <p class="section-label">Limits</p>

        <div class="vn-card-body space-y-4 pt-0">
            <div class="grid grid-cols-2 gap-3">
                <div class="vn-field">
                    <label>Number Expiry (minutes)</label>
                    <input type="number" name="herosms_expiry_minutes"
                           value="{{ $settings['herosms_expiry_minutes'] ?? '20' }}"
                           min="5" max="60" placeholder="20">
                    <p class="field-hint">Minutes before an unreceived number expires (5–60).</p>
                </div>
                <div class="vn-field">
                    <label>Max Active Rentals Per User</label>
                    <input type="number" name="herosms_max_active"
                           value="{{ $settings['herosms_max_active'] ?? '3' }}"
                           min="1" max="10" placeholder="3">
                    <p class="field-hint">Max simultaneous active rentals allowed per user.</p>
                </div>
            </div>
        </div>

        <div class="vn-divider"></div>

        {{-- Enable virtual numbers toggle --}}
        <div class="vn-row">
            <div class="vn-row-label">
                <p>Enable Virtual Numbers</p>
                <span>Toggle the Virtual Numbers section in the user dashboard</span>
            </div>
            <label class="vn-toggle-wrap" for="vn-enabled-toggle">
                <input type="checkbox" name="herosms_enabled_vn" id="vn-enabled-toggle"
                       value="1" {{ $settings['herosms_enabled'] === '1' ? 'checked' : '' }}
                       onchange="syncEnableToggle(this)">
                <div class="vn-track"></div>
            </label>
        </div>
    </div>


    {{-- ════════════════════════════════════════════
         CARD 3 — Balance info (if configured)
    ════════════════════════════════════════════ --}}
    @if($balance !== null)
    <div class="vn-card">
        <div class="vn-card-header">
            <div class="vn-icon-badge" style="background:linear-gradient(135deg,#065f46,#10b981)">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white">Provider Wallet</p>
                <p class="text-xs text-slate-400 mt-0.5">Your live HeroSMS provider balance</p>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold text-emerald-400">${{ number_format($balance, 4) }}</p>
                <a href="https://hero-sms.com" target="_blank"
                   class="text-xs text-sky-400 hover:underline">Top up →</a>
            </div>
        </div>
    </div>
    @endif


    {{-- ════════════════════════════════════════════
         CARD — SMS Push Webhook
    ════════════════════════════════════════════ --}}
    <div class="vn-card">
        <div class="vn-card-header">
            <div class="vn-icon-badge" style="background:linear-gradient(135deg,#1e3a5f,#3b82f6)">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">SMS Push Webhook</p>
                <p class="text-xs text-slate-400 mt-0.5">HeroSMS calls this URL instantly when a code arrives — no polling needed.</p>
            </div>
        </div>

        <div class="vn-divider"></div>
        <div class="vn-card-body pt-4 space-y-4">

            {{-- Webhook URL display --}}
            <div class="vn-field">
                <label>Webhook URL <span class="text-slate-600">(copy and paste into HeroSMS settings)</span></label>
                @php
                    $webhookUrl = url('/herosms/webhook') . '?token=' . $settings['herosms_webhook_secret'];
                @endphp
                <div class="flex gap-2">
                    <input type="text" id="webhook-url-input"
                           value="{{ $webhookUrl }}"
                           readonly
                           style="font-size:.72rem; font-family:monospace; color:#94a3b8; cursor:default;">
                    <button type="button" onclick="copyWebhookUrl()"
                            id="copy-webhook-btn"
                            style="flex-shrink:0; padding:.625rem .875rem; background:#1e3a5f; border:1px solid rgba(59,130,246,.35); border-radius:.5rem; color:#60a5fa; font-size:.75rem; font-weight:600; cursor:pointer; white-space:nowrap; transition:background .15s;"
                            onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1e3a5f'">
                        Copy
                    </button>
                </div>
                <p class="field-hint">Paste this into your HeroSMS account under <strong style="color:#f1f5f9;">Account → Webhooks / Callbacks</strong>. When HeroSMS receives a code it will POST to this URL and the code will appear in the user's order instantly.</p>
            </div>

            {{-- Webhook secret --}}
            <div class="vn-field">
                <label>Webhook Secret Token</label>
                <div class="pw-wrap">
                    <input type="text" name="herosms_webhook_secret" id="webhook-secret-input"
                           value="{{ $settings['herosms_webhook_secret'] }}"
                           placeholder="auto-generated"
                           style="font-family:monospace; font-size:.78rem;">
                </div>
                <p class="field-hint">Embedded in the webhook URL as <code style="color:#94a3b8;">?token=</code>. Change it to regenerate a new URL (and update it in HeroSMS). Leave as-is to keep the current URL.</p>
            </div>

        </div>
    </div>


    {{-- ════════════════════════════════════════════
         Save button
    ════════════════════════════════════════════ --}}
    <div class="flex items-center gap-4 pt-1">
        <button type="submit" class="save-btn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Save Settings
        </button>
        <a href="{{ route('admin.virtual-numbers') }}"
           class="text-sm text-slate-400 hover:text-white transition-colors">
            View Orders →
        </a>
    </div>

    </form>


    {{-- ════════════════════════════════════════════
         Quick Stats
    ════════════════════════════════════════════ --}}
    @php
        $stats = [
            'total'     => \App\Models\VirtualNumberOrder::count(),
            'active'    => \App\Models\VirtualNumberOrder::whereIn('status',['waiting','received'])->count(),
            'completed' => \App\Models\VirtualNumberOrder::where('status','completed')->count(),
            'revenue'   => \App\Models\VirtualNumberOrder::whereIn('status',['waiting','received','completed'])->sum('cost'),
        ];
    @endphp

    <div class="vn-card">
        <p class="section-label">Quick Stats</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-px p-0" style="background:rgba(255,255,255,.06);">
            <div class="stat-chip">
                <p class="text-xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Total Orders</p>
            </div>
            <div class="stat-chip">
                <p class="text-xl font-bold text-orange-400">{{ number_format($stats['active']) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Active Now</p>
            </div>
            <div class="stat-chip">
                <p class="text-xl font-bold text-emerald-400">{{ number_format($stats['completed']) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Completed</p>
            </div>
            <div class="stat-chip">
                <p class="text-xl font-bold text-white">₦{{ number_format($stats['revenue'], 0) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Revenue</p>
            </div>
        </div>
        <div class="px-5 py-3 text-right">
            <a href="{{ route('admin.virtual-numbers') }}" class="text-xs text-sky-400 hover:underline">View all orders →</a>
        </div>
    </div>

</div>

<script>
/* ── Show / hide password ── */
function togglePw(inputId, btn) {
    const el = document.getElementById(inputId);
    const open = el.type === 'password';
    el.type = open ? 'text' : 'password';
    const icon = document.getElementById('eye-' + inputId);
    if (icon) {
        icon.innerHTML = open
            ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
            : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    }
}

/* ── Keep both enable toggles in sync ── */
function syncEnableToggle(changed) {
    const other = changed.id === 'herosms-enabled-toggle'
        ? document.getElementById('vn-enabled-toggle')
        : document.getElementById('herosms-enabled-toggle');
    if (other) other.checked = changed.checked;
    // Only the first checkbox (herosms_enabled) is submitted — the second is cosmetic
    document.getElementById('herosms-enabled-toggle').checked = changed.checked;
}
document.getElementById('vn-enabled-toggle').addEventListener('change', function() {
    syncEnableToggle(this);
});
document.getElementById('herosms-enabled-toggle').addEventListener('change', function() {
    syncEnableToggle(this);
});

/* ── Commission label + live preview ── */
function updateCommissionLabel() {
    const type = document.getElementById('commission-type').value;
    const unit = document.getElementById('commission-unit');
    const inp  = document.getElementById('commission-amount');
    if (type === 'percentage') {
        unit.textContent = '%';
        inp.setAttribute('max', '500');
        inp.placeholder = '10';
    } else {
        unit.textContent = '₦';
        inp.removeAttribute('max');
        inp.placeholder = '200';
    }
    updatePricePreview();
}

function updatePricePreview() {
    const exRate     = parseFloat(document.querySelector('[name=herosms_exchange_rate]')?.value) || 1600;
    const commType   = document.getElementById('commission-type')?.value || 'flat';
    const commAmount = parseFloat(document.getElementById('commission-amount')?.value) || 0;
    const exampleUsd = 0.25;
    const baseNgn    = exampleUsd * exRate;
    let total, commDisplay;
    if (commType === 'percentage') {
        total = Math.ceil(baseNgn * (1 + commAmount / 100));
        commDisplay = commAmount + '%';
    } else {
        total = Math.ceil(baseNgn + commAmount);
        commDisplay = '₦' + commAmount.toLocaleString();
    }
    const el = (id) => document.getElementById(id);
    if (el('preview-base'))  el('preview-base').textContent  = '₦' + Math.ceil(baseNgn).toLocaleString();
    if (el('preview-commission')) el('preview-commission').textContent = commDisplay;
    if (el('preview-total')) el('preview-total').textContent = '₦' + total.toLocaleString();
}

document.querySelector('[name=herosms_exchange_rate]')?.addEventListener('input', updatePricePreview);
document.getElementById('commission-amount')?.addEventListener('input', updatePricePreview);

/* ── Test connection ── */
async function testConnection() {
    const btn    = document.getElementById('test-btn');
    const result = document.getElementById('test-result');
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Testing…`;
    result.classList.remove('hidden');
    result.textContent = 'Connecting…';
    result.style.color = '#94a3b8';

    try {
        const r = await fetch('{{ route("admin.virtual-number-settings.test") }}', {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const d = await r.json();
        result.textContent = d.message;
        result.style.color = d.success ? '#4ade80' : '#f87171';
    } catch (e) {
        result.textContent = 'Request failed. Check network or API key.';
        result.style.color = '#f87171';
    }

    btn.disabled = false;
    btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Test Connection`;
}

/* Init commission label */
updateCommissionLabel();

/* ── Copy webhook URL ── */
function copyWebhookUrl() {
    const inp = document.getElementById('webhook-url-input');
    const btn = document.getElementById('copy-webhook-btn');
    inp.select();
    inp.setSelectionRange(0, 99999);
    try {
        navigator.clipboard.writeText(inp.value).catch(() => document.execCommand('copy'));
    } catch(e) {
        document.execCommand('copy');
    }
    btn.textContent = 'Copied!';
    btn.style.color = '#4ade80';
    btn.style.borderColor = 'rgba(74,222,128,.4)';
    setTimeout(() => {
        btn.textContent = 'Copy';
        btn.style.color = '#60a5fa';
        btn.style.borderColor = 'rgba(59,130,246,.35)';
    }, 2000);
}
</script>

@endsection
