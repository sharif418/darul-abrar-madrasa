@extends('layouts.app')

@section('title', 'All Children Fees')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <span>All Children Fees</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">All Children Fees</h1>
        <p class="text-gray-600 mt-1">Hello, {{ $guardian->user->name ?? 'Guardian' }}. Review pending fees across all your children.</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card title="Total Children" value="{{ number_format(count($students ?? [])) }}" icon="users" color="indigo" />
        <x-stat-card title="Total Pending" value="{{ number_format((float)($totalPending ?? 0), 2) }}" icon="banknotes" color="yellow" />
        @php
            $allFees = collect($feesByStudent ?? [])->flatten(1);
            $paid = $allFees->where('status','paid')->count();
            $partial = $allFees->where('status','partial')->count();
            $unpaid = $allFees->where('status','unpaid')->count();
        @endphp
        <x-stat-card title="Paid/Partial/Unpaid" value="{{ $paid }} / {{ $partial }} / {{ $unpaid }}" icon="chart-bar" color="blue" />
    </div>

    {{-- Fees grouped by student --}}
    @forelse(($students ?? []) as $child)
        @php
            $childFees = collect($feesByStudent[$child->id] ?? []);
            $childTotalPending = $childFees->reduce(function($carry, $fee){
                $netAmount = method_exists($fee, 'getNetAmountAttribute') ? (float)$fee->net_amount : (float)$fee->amount;
                return $carry + max(0, $netAmount - (float)($fee->paid_amount ?? 0));
            }, 0.0);
        @endphp

        <x-card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $child->user->name ?? 'Student' }}</h2>
                    <p class="text-gray-600 text-sm">Class: {{ $child->class->name ?? 'N/A' }} · Roll: {{ $child->roll_number ?? 'N/A' }}</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">Pending</div>
                    <div class="text-xl font-semibold text-gray-800">{{ number_format($childTotalPending, 2) }}</div>
                </div>
            </div>

            <x-table>
                <x-slot name="head">
                    <tr>
                        <x-table.th>Invoice</x-table.th>
                        <x-table.th>Type</x-table.th>
                        <x-table.th>Due Date</x-table.th>
                        <x-table.th>Status</x-table.th>
                        <x-table.th class="text-right">Amount</x-table.th>
                        <x-table.th class="text-right">Paid</x-table.th>
                        <x-table.th class="text-right">Remaining</x-table.th>
                        <x-table.th>Actions</x-table.th>
                    </tr>
                </x-slot>
                <x-slot name="body">
                    @forelse($childFees as $fee)
                        @php
                            $netAmount = method_exists($fee, 'getNetAmountAttribute') ? (float)$fee->net_amount : (float)$fee->amount;
                            $remaining = max(0, $netAmount - (float)($fee->paid_amount ?? 0));
                        @endphp
                        <tr class="border-b">
                            <x-table.td>#{{ $fee->invoice_number ?? $fee->id }}</x-table.td>
                            <x-table.td>{{ ucfirst($fee->fee_type) }}</x-table.td>
                            <x-table.td>{{ \Carbon\Carbon::parse($fee->due_date)->format('Y-m-d') }}</x-table.td>
                            <x-table.td>
                                @if($fee->status === 'paid')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Paid</span>
                                @elseif($fee->status === 'partial')
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Partial</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Unpaid</span>
                                @endif
                            </x-table.td>
                            <x-table.td class="text-right">{{ number_format($netAmount, 2) }}</x-table.td>
                            <x-table.td class="text-right">{{ number_format((float)($fee->paid_amount ?? 0), 2) }}</x-table.td>
                            <x-table.td class="text-right">{{ number_format($remaining, 2) }}</x-table.td>
                            <x-table.td>
                                <div class="flex flex-wrap gap-2">
                                    @if($remaining > 0)
                                        <a href="{{ route('guardian.fees.pay', $fee) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Pay Now</a>
                                    @else
                                        <span class="text-sm text-gray-500">No action</span>
                                    @endif
                                </div>
                            </x-table.td>
                        </tr>
                    @empty
                        <tr>
                            <x-table.td colspan="8">
                                <div class="text-center text-gray-500 py-6">No fees found for this child.</div>
                            </x-table.td>
                        </tr>
                    @endforelse
                </x-slot>
            </x-table>
        </x-card>
    @empty
        <x-card>
            <div class="text-center text-gray-500 py-8">No children are linked to your guardian account.</div>
        </x-card>
    @endforelse

    {{-- Footer --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Back to Dashboard</a>
    </div>
</div>
@endsection
