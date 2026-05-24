@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
