@extends('layouts.app')

@section('title', 'Waiver Report')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('accountant.dashboard') }}" class="text-indigo-600 hover:underline">Accountant Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('accountant.reports') }}" class="text-indigo-600 hover:underline">Reports</a>
        <span class="mx-2">/</span>
        <span>Waiver Report</span>
    </div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Waiver Report</h1>
            <p class="text-gray-600">Analyze waivers by type, status, and student.</p>
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
        <form method="GET" action="{{ route('accountant.reports.waivers') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <x-label for="status" value="Status" />
                @php $status = request('status'); @endphp
                <x-select id="status" name="status" class="mt-1 w-full">
                    <option value="" {{ $status === null || $status === '' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                </x-select>
            </div>
            <div>
                <x-label for="student_id" value="Student ID" />
                <x-input id="student_id" name="student_id" type="number" min="1" value="{{ request('student_id') }}" class="mt-1 w-full" />
            </div>
            <div>
                <x-label for="date_from" value="Valid From" />
                <x-input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 w-full" />
            </div>
            <div>
                <x-label for="date_to" value="Valid To" />
                <x-input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 w-full" />
            </div>
            <div class="flex items-end">
                <x-button type="submit" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    {{-- Summary by Type --}}
    <x-card class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Summary by Waiver Type</h2>
        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table.th>Type</x-table.th>
                    <x-table.th class="text-right">Count</x-table.th>
                    <x-table.th class="text-right">Total Amount</x-table.th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse(($summaryByType ?? []) as $row)
                    <tr class="border-b">
                        <x-table.td>{{ ucfirst(str_replace('_',' ', $row->waiver_type ?? 'N/A')) }}</x-table.td>
                        <x-table.td class="text-right">{{ number_format((int)($row->total ?? 0)) }}</x-table.td>
                        <x-table.td class="text-right">{{ number_format((float)($row->total_amount ?? 0), 2) }}</x-table.td>
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

    {{-- Waiver list --}}
    <x-card>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Waivers</h2>
        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table.th>Student</x-table.th>
                    <x-table.th>Fee</x-table.th>
                    <x-table.th>Type</x-table.th>
                    <x-table.th>Amount Type</x-table.th>
                    <x-table.th class="text-right">Amount</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Valid</x-table.th>
                    <x-table.th>Requested By</x-table.th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse(($list ?? []) as $w)
                    <tr class="border-b">
                        <x-table.td>{{ $w->student->user->name ?? 'N/A' }}</x-table.td>
                        <x-table.td>
                            @if($w->fee)
                                #{{ $w->fee->id }} ({{ ucfirst($w->fee->fee_type) }})
                            @else
                                General
                            @endif
                        </x-table.td>
                        <x-table.td>{{ ucfirst(str_replace('_',' ', $w->waiver_type)) }}</x-table.td>
                        <x-table.td>{{ ucfirst($w->amount_type) }}</x-table.td>
                        <x-table.td class="text-right">{{ number_format((float)$w->amount, 2) }}</x-table.td>
                        <x-table.td>
                            @if($w->status === 'approved')
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Approved</span>
                            @elseif($w->status === 'pending')
                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Pending</span>
                            @elseif($w->status === 'rejected')
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">{{ ucfirst($w->status) }}</span>
                            @endif
                        </x-table.td>
                        <x-table.td>
                            {{ \Carbon\Carbon::parse($w->valid_from)->format('Y-m-d') }}
                            —
                            {{ $w->valid_until ? \Carbon\Carbon::parse($w->valid_until)->format('Y-m-d') : 'Open' }}
                        </x-table.td>
                        <x-table.td>{{ optional($w->createdBy)->name ?? 'N/A' }}</x-table.td>
                    </tr>
                @empty
                    <tr>
                        <x-table.td colspan="8">
                            <div class="text-center text-gray-500 py-8">No waivers found for the selected filters.</div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>

        {{-- Pagination --}}
        @if(isset($list) && method_exists($list, 'links'))
            <div class="mt-4">
                {{ $list->withQueryString()->links() }}
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
