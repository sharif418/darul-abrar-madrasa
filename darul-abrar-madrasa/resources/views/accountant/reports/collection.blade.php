@extends('layouts.app')

@section('title', 'Collection Report')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('accountant.dashboard') }}" class="text-indigo-600 hover:underline">Accountant Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('accountant.reports') }}" class="text-indigo-600 hover:underline">Reports</a>
        <span class="mx-2">/</span>
        <span>Collection Report</span>
    </div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Collection Report</h1>
            <p class="text-gray-600">View collected payments with filters and summary.</p>
        </div>
        <div class="flex gap-3">
            <a href="#" class="inline-flex items-center px-4 py-2 bg-white border rounded-md hover:bg-gray-50 text-gray-700">
                Export PDF
            </a>
            <a href="#" class="inline-flex items-center px-4 py-2 bg-white border rounded-md hover:bg-gray-50 text-gray-700">
                Export Excel
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('accountant.reports.collection') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <x-label for="date_from" value="Date From" />
                <x-input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 w-full" />
            </div>
            <div>
                <x-label for="date_to" value="Date To" />
                <x-input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 w-full" />
            </div>
            <div>
                <x-label for="class_id" value="Class" />
                <x-input id="class_id" name="class_id" type="number" min="1" value="{{ request('class_id') }}" class="mt-1 w-full" placeholder="Class ID" />
            </div>
            <div>
                <x-label for="fee_type" value="Fee Type" />
                <x-input id="fee_type" name="fee_type" type="text" value="{{ request('fee_type') }}" class="mt-1 w-full" placeholder="e.g., monthly" />
            </div>
            <div class="flex items-end">
                <x-button type="submit" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card title="Total Collected" value="{{ isset($totalCollected) ? number_format((float)$totalCollected, 2) : '0.00' }}" icon="currency-dollar" color="green" />
        <x-stat-card title="Transactions" value="{{ isset($fees) ? number_format($fees->total()) : '0' }}" icon="receipt-percent" color="blue" />
        <x-stat-card title="Avg per Transaction" value="{{ (isset($totalCollected) && isset($fees) && $fees->total() > 0) ? number_format($totalCollected / $fees->total(), 2) : '0.00' }}" icon="chart-bar" color="indigo" />
    </div>

    {{-- Summary by Fee Type --}}
    <x-card class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Summary by Fee Type</h2>
        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table.th>Fee Type</x-table.th>
                    <x-table.th class="text-right">Transactions</x-table.th>
                    <x-table.th class="text-right">Total Collected</x-table.th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse(($summary ?? []) as $row)
                    <tr class="border-b">
                        <x-table.td>{{ $row->fee_type ?? 'N/A' }}</x-table.td>
                        <x-table.td class="text-right">{{ number_format((int)($row->total_transactions ?? 0)) }}</x-table.td>
                        <x-table.td class="text-right">{{ number_format((float)($row->total_collected ?? 0), 2) }}</x-table.td>
                    </tr>
                @empty
                    <tr>
                        <x-table.td colspan="3">
                            <div class="text-center text-gray-500 py-6">No summary data available for selected filters.</div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </x-card>

    {{-- Collections list --}}
    <x-card>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Collections</h2>
        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table.th>Date</x-table.th>
                    <x-table.th>Student</x-table.th>
                    <x-table.th>Class</x-table.th>
                    <x-table.th>Fee Type</x-table.th>
                    <x-table.th>Method</x-table.th>
                    <x-table.th>Txn ID</x-table.th>
                    <x-table.th class="text-right">Amount</x-table.th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse(($fees ?? []) as $fee)
                    <tr class="border-b">
                        <x-table.td>{{ \Carbon\Carbon::parse($fee->payment_date)->format('Y-m-d') }}</x-table.td>
                        <x-table.td>{{ $fee->student->user->name ?? 'N/A' }}</x-table.td>
                        <x-table.td>{{ $fee->student->class->name ?? 'N/A' }}</x-table.td>
                        <x-table.td>{{ ucfirst($fee->fee_type) }}</x-table.td>
                        <x-table.td>{{ $fee->payment_method ?? '-' }}</x-table.td>
                        <x-table.td>{{ $fee->transaction_id ?? '-' }}</x-table.td>
                        <x-table.td class="text-right">{{ number_format((float)$fee->paid_amount, 2) }}</x-table.td>
                    </tr>
                @empty
                    <tr>
                        <x-table.td colspan="7">
                            <div class="text-center text-gray-500 py-8">No collection records found for the selected filters.</div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>

        {{-- Pagination --}}
        @if(isset($fees) && method_exists($fees, 'links'))
            <div class="mt-4">
                {{ $fees->withQueryString()->links() }}
            </div>
        @endif
    </x-card>

    {{-- Footer links --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('accountant.reports') }}" class="text-indigo-600 hover:underline">Back to Reports</a>
        <span class="mx-2">&middot;</span>
        <a href="{{ route('accountant.dashboard') }}" class="text-indigo-600 hover:underline">Back to Dashboard</a>
    </div>
</div>
@endsection
