@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Accountant Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ \Carbon\Carbon::now()->format('l, d M Y H:i') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('accountant.fees') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Record Payment</a>
                    <a href="{{ route('accountant.waivers') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Manage Waivers</a>
                    <a href="{{ route('accountant.installments') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Installments</a>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Today's Collection</div>
                <div class="mt-2 text-3xl font-bold text-green-600">৳ {{ number_format((float)($todayCollection ?? 0), 2) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">This Month's Collection</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">৳ {{ number_format((float)($monthCollection ?? 0), 2) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Total Pending Fees</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600">৳ {{ number_format((float)($totalPending ?? 0), 2) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Total Overdue Fees</div>
                <div class="mt-2 text-3xl font-bold text-red-600">৳ {{ number_format((float)($totalOverdue ?? 0), 2) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Pending Waivers</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">{{ (int)($pendingWaivers ?? 0) }}</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Collection Trend (Last 6 months)</h2>
                </div>
                <canvas id="collectionTrendChart" class="mt-4"></canvas>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Fee Type Breakdown</h2>
                </div>
                <canvas id="feeTypeChart" class="mt-4"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Payment Method Distribution</h2>
                </div>
                <canvas id="paymentMethodChart" class="mt-4"></canvas>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Waiver Statistics</h2>
                </div>
                <canvas id="waiverStatsChart" class="mt-4"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Recent Transactions</h2>
                <a href="{{ route('accountant.fees') }}" class="text-sm text-green-700 hover:text-green-800">View all</a>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Fee Type</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Method</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Collected By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse(($recentTransactions ?? []) as $tx)
                            <tr>
                                <td class="px-4 py-2">{{ optional($tx->payment_date)->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-2">{{ optional(optional($tx->student)->user)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $tx->fee_type ?? '-' }}</td>
                                <td class="px-4 py-2">৳ {{ number_format((float)($tx->paid_amount ?? 0), 2) }}</td>
                                <td class="px-4 py-2">{{ $tx->payment_method ?? '-' }}</td>
                                <td class="px-4 py-2">{{ optional($tx->collectedBy)->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-4 text-center text-gray-500" colspan="6">No recent transactions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <a href="{{ route('accountant.fees') }}" class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4 hover:shadow">
                <div class="text-sm text-gray-600">Record Payment</div>
                <div class="mt-2 text-lg font-semibold text-green-800">Go</div>
            </a>
            <a href="{{ route('accountant.waivers.create') }}" class="bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 rounded-lg p-4 hover:shadow">
                <div class="text-sm text-gray-600">Create Waiver</div>
                <div class="mt-2 text-lg font-semibold text-indigo-800">Go</div>
            </a>
            <a href="{{ route('accountant.installments') }}" class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 hover:shadow">
                <div class="text-sm text-gray-600">Installment Plans</div>
                <div class="mt-2 text-lg font-semibold text-blue-800">Go</div>
            </a>
            <a href="{{ route('accountant.late-fees') }}" class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-4 hover:shadow">
                <div class="text-sm text-gray-600">Late Fees</div>
                <div class="mt-2 text-lg font-semibold text-red-800">Go</div>
            </a>
            <a href="{{ route('accountant.reports') }}" class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4 hover:shadow">
                <div class="text-sm text-gray-600">Reports</div>
                <div class="mt-2 text-lg font-semibold text-purple-800">Go</div>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const collectionTrend = @json($collectionTrend ?? ['labels' => [], 'data' => []]);
    const feeTypeBreakdown = @json($feeTypeBreakdown ?? ['labels' => [], 'data' => []]);
    const paymentMethodDistribution = @json($paymentMethodDistribution ?? ['labels' => [], 'data' => []]);
    const waiverStatistics = @json($waiverStatistics ?? ['labels' => [], 'data' => []]);

    new Chart(document.getElementById('collectionTrendChart'), {
        type: 'line',
        data: {
            labels: collectionTrend.labels,
            datasets: [{
                label: 'Collections',
                data: collectionTrend.data,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('feeTypeChart'), {
        type: 'doughnut',
        data: {
            labels: feeTypeBreakdown.labels,
            datasets: [{
                label: 'Amount',
                data: feeTypeBreakdown.data,
                backgroundColor: ['#1d4ed8','#16a34a','#f59e0b','#ef4444','#8b5cf6','#0ea5e9','#14b8a6']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'bar',
        data: {
            labels: paymentMethodDistribution.labels,
            datasets: [{
                label: 'Amount',
                data: paymentMethodDistribution.data,
                backgroundColor: '#0ea5e9'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('waiverStatsChart'), {
        type: 'bar',
        data: {
            labels: waiverStatistics.labels,
            datasets: [{
                label: 'Count',
                data: waiverStatistics.data,
                backgroundColor: '#8b5cf6'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endpush
@endsection
