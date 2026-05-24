@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Users</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_users']) }}</p>
        <div class="flex gap-3 mt-2 text-xs">
            <span class="text-green-400">{{ $stats['active_users'] }} active</span>
            <span class="text-yellow-400">{{ $stats['suspended_users'] }} suspended</span>
            <span class="text-red-400">{{ $stats['banned_users'] }} banned</span>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Listings</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_listings']) }}</p>
        <p class="text-xs text-green-400 mt-2">{{ $stats['active_listings'] }} active</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Purchases</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_purchases']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Revenue</p>
        <p class="text-3xl font-bold text-white mt-1">₦{{ number_format($stats['total_revenue'], 2) }}</p>
        <p class="text-xs text-slate-400 mt-2">Wallet vol: ₦{{ number_format($stats['wallet_volume'], 2) }}</p>
    </div>
</div>

{{-- Logsplug API Balance Widget --}}
<div class="mb-6">
    <div id="logsplug-balance-card" class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-900/40 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Logsplug API Wallet</p>
                <p id="logsplug-balance-value" class="text-2xl font-bold text-white mt-0.5">
                    <span class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin align-middle"></span>
                </p>
                <p id="logsplug-balance-note" class="text-xs text-slate-500 mt-0.5">Loading balance…</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings') }}#virtual-numbers" class="text-xs text-purple-400 hover:underline">Configure →</a>
            <button onclick="refreshLogsplugBalance()" id="logsplug-refresh-btn"
                class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white transition-colors" title="Refresh">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex items-center justify-between">
        <div>
            <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Open Tickets</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $stats['open_tickets'] }}</p>
        </div>
        <a href="{{ route('admin.tickets') }}" class="text-xs text-sky-400 hover:underline">View →</a>
    </div>
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
                <span class="status-{{ $u->status }}">{{ ucfirst($u->status) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Revenue Chart --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl mb-6">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-white">Revenue — Last 30 Days</h2>
            <p class="text-xs text-slate-400 mt-0.5">Daily completed purchase revenue</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-500">30-day total</p>
            <p class="text-lg font-bold text-white">₦{{ number_format(array_sum($chartRevenue), 2) }}</p>
        </div>
    </div>
    <div class="p-5">
        <canvas id="revenueChart" style="max-height:220px"></canvas>
    </div>
</div>

<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h2 class="font-semibold text-white">Recent Purchases</h2>
        <a href="{{ route('admin.transactions') }}" class="text-xs text-sky-400 hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">User</th>
                <th class="px-6 py-3 text-left">Listing</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($stats['recent_purchases'] as $p)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-6 py-3 text-slate-300">{{ $p->user->email ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-300">{{ $p->listing->title ?? '—' }}</td>
                    <td class="px-6 py-3 text-white font-medium">₦{{ number_format($p->amount, 2) }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $p->status === 'completed' ? 'bg-green-900/50 text-green-400' : ($p->status === 'pending' ? 'bg-yellow-900/50 text-yellow-400' : 'bg-red-900/50 text-red-400') }}">
                        {{ ucfirst($p->status) }}</span></td>
                    <td class="px-6 py-3 text-slate-400">{{ $p->created_at->diffForHumans() }}</td>
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

    valueEl.innerHTML = '<span class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin align-middle"></span>';
    noteEl.textContent = 'Loading balance…';
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
        noteEl.textContent = 'Network error. Try again.';
    } finally {
        btn.disabled = false;
    }
}
refreshLogsplugBalance();
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
        datasets: [{
            label: 'Revenue (₦)',
            data: revenue,
            borderColor: '#0ea5e9',
            backgroundColor: 'rgba(14,165,233,0.08)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#0ea5e9',
        },{
            label: 'Orders',
            data: orders,
            borderColor: '#a78bfa',
            backgroundColor: 'rgba(167,139,250,0.05)',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            pointRadius: 2,
            pointBackgroundColor: '#a78bfa',
            yAxisID: 'y2',
        }],
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#94a3b8', font: { size: 11 } } },
            tooltip: {
                backgroundColor: '#1e293b',
                borderColor: '#334155',
                borderWidth: 1,
                titleColor: '#f1f5f9',
                bodyColor: '#94a3b8',
            },
        },
        scales: {
            x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', font: { size: 10 }, maxTicksLimit: 10 } },
            y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', font: { size: 10 }, callback: v => '₦'+v.toLocaleString() }, beginAtZero: true },
            y2: { position: 'right', grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } }, beginAtZero: true },
        },
    },
});
</script>
@endpush
