@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

{{-- Top stats row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Users</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_users']) }}</p>
        <div class="flex gap-3 mt-2 text-xs flex-wrap">
            <span class="text-green-400">{{ $stats['active_users'] }} active</span>
            <span class="text-yellow-400">{{ $stats['suspended_users'] }} suspended</span>
            <span class="text-red-400">{{ $stats['banned_users'] }} banned</span>
        </div>
        <p class="text-xs text-slate-500 mt-1">+{{ $stats['new_users_today'] }} today · +{{ $stats['new_users_week'] }} this week</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Listings</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_listings']) }}</p>
        <p class="text-xs text-green-400 mt-2">{{ $stats['active_listings'] }} active</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Purchases</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_purchases']) }}</p>
        <p class="text-xs text-slate-500 mt-2">VN orders: {{ number_format($stats['vn_total']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Revenue</p>
        <p class="text-3xl font-bold text-white mt-1">₦{{ number_format($stats['total_revenue'], 2) }}</p>
        <p class="text-xs text-slate-400 mt-1">+₦{{ number_format($stats['revenue_today'], 2) }} today</p>
        <p class="text-xs text-slate-500">Wallet vol: ₦{{ number_format($stats['wallet_volume'], 2) }}</p>
    </div>
</div>

{{-- Secondary stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-orange-500/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
            <p class="text-xs text-slate-400">VN Active</p>
            <p class="text-xl font-bold text-white">{{ $stats['vn_active'] }}</p>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-xs text-slate-400">VN Revenue</p>
            <p class="text-xl font-bold text-white">₦{{ number_format($stats['vn_revenue'], 0) }}</p>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-brand/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <p class="text-xs text-slate-400">Qualified Referrals</p>
            <p class="text-xl font-bold text-white">{{ $stats['qualified_referrals'] }}</p>
            <p class="text-xs text-yellow-400">{{ $stats['pending_referrals'] }} pending</p>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
        </div>
        <div>
            <p class="text-xs text-slate-400">Open Tickets</p>
            <p class="text-xl font-bold text-yellow-400">{{ $stats['open_tickets'] }}</p>
            <a href="{{ route('admin.tickets') }}" class="text-xs text-brand hover:underline">View →</a>
        </div>
    </div>
</div>

{{-- API Balance Widgets --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    {{-- Logsplug Balance --}}
    <div id="logsplug-balance-card" class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-900/40 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Logsplug API Wallet</p>
                <p id="logsplug-balance-value" class="text-2xl font-bold text-white mt-0.5">
                    @if($logsplugBalance !== null)
                        {{ number_format((float)$logsplugBalance, 2) }}
                    @else —
                    @endif
                </p>
                <p id="logsplug-balance-note" class="text-xs mt-0.5 {{ $logsplugBalance !== null ? 'text-slate-500' : 'text-yellow-400' }}">
                    @if($logsplugBalance !== null)
                        Available in API wallet · loaded at {{ now()->format('H:i') }}
                    @else
                        {{ $logsplugError ?? 'Could not load balance.' }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings') }}#virtual-numbers" class="text-xs text-purple-400 hover:underline">Configure →</a>
            <button onclick="refreshLogsplugBalance()" id="logsplug-refresh-btn"
                class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white transition-colors" title="Refresh balance">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    {{-- Hero-SMS Balance --}}
    <div id="herosms-balance-card" class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-900/40 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Hero-SMS API Wallet</p>
                <p id="herosms-balance-value" class="text-2xl font-bold text-white mt-0.5">
                    @if($heroSmsBalance !== null)
                        {{ number_format((float)$heroSmsBalance, 2) }}
                    @else —
                    @endif
                </p>
                <p id="herosms-balance-note" class="text-xs mt-0.5 {{ $heroSmsBalance !== null ? 'text-slate-500' : 'text-yellow-400' }}">
                    @if($heroSmsBalance !== null)
                        Available in API wallet · loaded at {{ now()->format('H:i') }}
                    @else
                        {{ $heroSmsError ?? 'Could not load balance.' }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings') }}#virtual-numbers" class="text-xs text-blue-400 hover:underline">Configure →</a>
            <button onclick="refreshHeroSmsBalance()" id="herosms-refresh-btn"
                class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white transition-colors" title="Refresh balance">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-3">New Users</p>
        <div class="space-y-2">
            @foreach($stats['recent_users'] as $u)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-sky-700 flex items-center justify-center text-xs font-bold text-white">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div>
                        <p class="text-sm text-white font-medium">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($u->last_login_at)
                        <span class="text-xs text-slate-500 hidden sm:block">Last login {{ $u->last_login_at->diffForHumans() }}</span>
                    @endif
                    <span class="status-{{ $u->status }}">{{ ucfirst($u->status) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4">Quick Links</p>
        <div class="space-y-2">
            @foreach([
                ['label' => 'Manage Users',        'route' => 'admin.users',         'color' => 'text-brand'],
                ['label' => 'Virtual Number Orders','route' => 'admin.virtual-numbers','color' => 'text-orange-400'],
                ['label' => 'Transactions',         'route' => 'admin.transactions',  'color' => 'text-green-400'],
                ['label' => 'Support Tickets',      'route' => 'admin.tickets',       'color' => 'text-yellow-400'],
                ['label' => 'Referrals',            'route' => 'admin.referrals',     'color' => 'text-purple-400'],
                ['label' => 'Settings',             'route' => 'admin.settings',      'color' => 'text-slate-300'],
            ] as $link)
            <a href="{{ route($link['route']) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                <span class="text-sm {{ $link['color'] }} font-medium">{{ $link['label'] }}</span>
                <svg class="w-4 h-4 text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Revenue Chart --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-semibold text-white">Revenue — Last 30 Days</h3>
            <p class="text-xs text-slate-400 mt-0.5">₦{{ number_format($stats['revenue_week'], 2) }} this week</p>
        </div>
        <div class="flex items-center gap-4 text-xs text-slate-400">
            <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-brand rounded inline-block"></span>Revenue</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-purple-400 rounded inline-block"></span>Orders</span>
        </div>
    </div>
    <div class="h-48">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

{{-- Recent Purchases --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h3 class="font-semibold text-white">Recent Purchases</h3>
        <a href="{{ route('admin.transactions') }}" class="text-xs text-brand hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">User</th>
                <th class="px-6 py-3 text-left">Listing</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($stats['recent_purchases'] as $p)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-3 text-slate-300">{{ $p->user?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-300 max-w-[180px] truncate">{{ $p->listing?->title ?? '—' }}</td>
                    <td class="px-6 py-3 text-green-400 font-semibold">₦{{ number_format($p->amount, 2) }}</td>
                    <td class="px-6 py-3 text-slate-400 text-xs">{{ $p->created_at->format('M j, H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No purchases yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
async function refreshLogsplugBalance() {
    const valueEl = document.getElementById('logsplug-balance-value');
    const noteEl  = document.getElementById('logsplug-balance-note');
    const btn     = document.getElementById('logsplug-refresh-btn');
    if (!valueEl) return;

    valueEl.innerHTML = '<span class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin align-middle"></span>';
    noteEl.textContent = 'Refreshing…';
    btn.disabled = true;

    try {
        const res  = await fetch('/admin/virtual-numbers/logsplug-balance', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.success && data.balance !== null && data.balance !== undefined) {
            const balance = parseFloat(data.balance);
            valueEl.textContent = isNaN(balance) ? data.balance : balance.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const low = {{ (float) \App\Models\Setting::get('low_balance_threshold', '5') }};
            if (!isNaN(balance) && balance <= low) {
                noteEl.innerHTML = '<span class="text-red-400">⚠ Low balance — top up soon</span>';
                document.getElementById('logsplug-balance-card').classList.add('border-red-700/50');
            } else {
                noteEl.textContent = 'Available in API wallet · updated just now';
                document.getElementById('logsplug-balance-card').classList.remove('border-red-700/50');
            }
        } else {
            valueEl.textContent = '—';
            noteEl.textContent = data.message || 'Could not load balance.';
        }
    } catch (e) {
        valueEl.textContent = '—';
        noteEl.textContent = 'Refresh failed. Check network.';
    } finally {
        btn.disabled = false;
    }
}

async function refreshHeroSmsBalance() {
    const valueEl = document.getElementById('herosms-balance-value');
    const noteEl  = document.getElementById('herosms-balance-note');
    const btn     = document.getElementById('herosms-refresh-btn');
    if (!valueEl) return;

    valueEl.innerHTML = '<span class="inline-block w-5 h-5 border-2 border-blue-400 border-t-transparent rounded-full animate-spin align-middle"></span>';
    noteEl.textContent = 'Refreshing…';
    btn.disabled = true;

    try {
        const res  = await fetch('/admin/virtual-numbers/herosms-balance', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.success && data.balance !== null && data.balance !== undefined) {
            const balance = parseFloat(data.balance);
            valueEl.textContent = isNaN(balance) ? data.balance : balance.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const low = {{ (float) \App\Models\Setting::get('low_balance_threshold', '5') }};
            if (!isNaN(balance) && balance <= low) {
                noteEl.innerHTML = '<span class="text-red-400">⚠ Low balance — top up soon</span>';
                document.getElementById('herosms-balance-card').classList.add('border-red-700/50');
            } else {
                noteEl.textContent = 'Available in API wallet · updated just now';
                document.getElementById('herosms-balance-card').classList.remove('border-red-700/50');
            }
        } else {
            valueEl.textContent = '—';
            noteEl.textContent = data.message || 'Could not load balance.';
        }
    } catch (e) {
        valueEl.textContent = '—';
        noteEl.textContent = 'Refresh failed. Check network.';
    } finally {
        btn.disabled = false;
    }
}
</script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const labels = @json($chartLabels);
const revenue = @json($chartRevenue);
const orders  = @json($chartOrders);
new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Revenue (₦)',
                data: revenue,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,0.08)',
                tension: 0.4,
                fill: true,
                pointRadius: 2,
                yAxisID: 'y',
            },
            {
                label: 'Orders',
                data: orders,
                borderColor: '#a78bfa',
                backgroundColor: 'rgba(167,139,250,0.06)',
                tension: 0.4,
                fill: false,
                pointRadius: 2,
                yAxisID: 'y1',
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: {
            x:  { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
            y:  { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 }, callback: v => '₦' + v.toLocaleString() }, position: 'left' },
            y1: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } }, position: 'right' },
        }
    }
});
</script>
@endpush
