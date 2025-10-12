@extends('layouts.app')

@section('title', 'Child Fees')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">My Children</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Child Profile</a>
        <span class="mx-2">/</span>
        <span>Fees</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Fees - {{ $student->user->name ?? 'Student' }}</h1>
        <p class="text-gray-600 mt-1">Class: {{ $student->class->name ?? 'N/A' }} · Roll: {{ $student->roll_number ?? 'N/A' }}</p>
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
        <x-stat-card title="Total Pending" value="{{ number_format((float)($totalPending ?? 0), 2) }}" icon="banknotes" color="yellow" />
        @php
            $paidCount = collect($fees ?? [])->where('status', 'paid')->count();
            $partialCount = collect($fees ?? [])->where('status', 'partial')->count();
            $unpaidCount = collect($fees ?? [])->where('status', 'unpaid')->count();
        @endphp
        <x-stat-card title="Paid / Partial / Unpaid" value="{{ $paidCount }} / {{ $partialCount }} / {{ $unpaidCount }}" icon="chart-bar" color="blue" />
        <x-stat-card title="Total Fees" value="{{ number_format(count($fees ?? [])) }}" icon="clipboard-document" color="indigo" />
    </div>

    {{-- Fees table --}}
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Fee Details</h2>
            <div class="flex gap-3">
                <a href="{{ route('guardian.fees') }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View All Children Fees</a>
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
                @forelse(($fees ?? []) as $fee)
                    @php
                        // Prefer net_amount if accessor exists; otherwise fallback to amount - paid
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

                    {{-- Installments (if any) --}}
                    @if(method_exists($fee, 'installments') && $fee->installments && $fee->installments->count() > 0)
                        <tr class="bg-gray-50">
                            <x-table.td colspan="8" class="p-0">
                                <div class="p-3">
                                    <div class="text-sm font-semibold text-gray-700 mb-2">Installments</div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="text-left border-b">
                                                    <th class="px-3 py-2">#</th>
                                                    <th class="px-3 py-2">Due Date</th>
                                                    <th class="px-3 py-2" style="text-align:right;">Amount</th>
                                                    <th class="px-3 py-2" style="text-align:right;">Paid</th>
                                                    <th class="px-3 py-2" style="text-align:right;">Late Fee</th>
                                                    <th class="px-3 py-2">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($fee->installments as $inst)
                                                    <tr class="border-b">
                                                        <td class="px-3 py-2">{{ $inst->installment_number }}</td>
                                                        <td class="px-3 py-2">{{ \Carbon\Carbon::parse($inst->due_date)->format('Y-m-d') }}</td>
                                                        <td class="px-3 py-2" style="text-align:right;">{{ number_format((float)$inst->amount, 2) }}</td>
                                                        <td class="px-3 py-2" style="text-align:right;">{{ number_format((float)$inst->paid_amount, 2) }}</td>
                                                        <td class="px-3 py-2" style="text-align:right;">{{ number_format((float)($inst->late_fee_applied ?? 0), 2) }}</td>
                                                        <td class="px-3 py-2">
                                                            @if($inst->status === 'paid')
                                                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Paid</span>
                                                            @elseif($inst->status === 'overdue')
                                                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Overdue</span>
                                                            @elseif($inst->status === 'waived')
                                                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Waived</span>
                                                            @else
                                                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Pending</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </x-table.td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <x-table.td colspan="8">
                            <div class="text-center text-gray-500 py-8">No fees found for this child.</div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </x-card>

    {{-- Footer links --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Back to Profile</a>
        <span class="mx-2">&middot;</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">Back to Children</a>
    </div>
</div>
@endsection
