@extends('layouts.app')

@section('header', 'Outstanding Fees Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Outstanding Fees Report</h1>
        <div class="flex space-x-2">
            <x-button href="{{ route('fees.index') }}" color="secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Fees
            </x-button>
            <x-button href="{{ route('fees.reports.collection') }}" color="success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Collection Report
            </x-button>
            <x-button onclick="window.print()" color="warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </x-button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 print:hidden">
        <h2 class="text-lg font-semibold mb-4">Filter Outstanding Fees</h2>
        <form action="{{ route('fees.reports.outstanding') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-label for="fee_type" value="Fee Type" />
                <x-select id="fee_type" name="fee_type" class="block mt-1 w-full">
                    <option value="">All Types</option>
                    @foreach($feeTypes as $type)
                        <option value="{{ $type }}" {{ request('fee_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </x-select>
            </div>
            
            <div class="flex items-center mt-8">
                <input type="checkbox" id="overdue_only" name="overdue_only" value="1" class="form-checkbox h-5 w-5 text-blue-600" {{ request('overdue_only') == '1' ? 'checked' : '' }}>
                <label for="overdue_only" class="ml-2 text-gray-700">Show only overdue fees</label>
            </div>
            
            <div class="flex items-end">
                <x-button type="submit" color="primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Apply Filters
                </x-button>
                <a href="{{ route('fees.reports.outstanding') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Report Header for Print -->
    <div class="hidden print:block mb-8">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Darul Abrar Model Kamil Madrasa</h1>
            <p class="text-lg">Outstanding Fees Report</p>
            <p class="text-sm text-gray-600">
                Generated on: {{ now()->format('F d, Y h:i A') }}
                @if(request('fee_type'))
                    <br>Fee Type: {{ ucfirst(request('fee_type')) }}
                @endif
                @if(request('overdue_only') == '1')
                    <br>Showing only overdue fees
                @endif
            </p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Total Outstanding</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalOutstanding, 2) }}</h3>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Outstanding Records</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $outstandingFees->total() }}</h3>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Unpaid Fees</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $outstandingFees->where('status', 'unpaid')->count() }}</h3>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Partial Payments</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $outstandingFees->where('status', 'partial')->count() }}</h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding by Fee Type -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-800">Outstanding by Fee Type</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($summary as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($item->fee_type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->total_records }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->total_outstanding, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format(($item->total_outstanding / $totalOutstanding) * 100, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $outstandingFees->total() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ number_format($totalOutstanding, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Outstanding Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-800">Outstanding Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($outstandingFees as $fee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $fee->due_date->format('M d, Y') }}
                            @if($fee->isOverdue)
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">OVERDUE</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $fee->student->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $fee->student->student_id }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($fee->fee_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($fee->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            {{ number_format($fee->amount - $fee->paid_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($fee->status == 'partial')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Partial
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Unpaid
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium print:hidden">
                            <a href="{{ route('fees.show', $fee->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('fees.edit', $fee->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('fees.generate-invoice', $fee->id) }}" class="text-green-600 hover:text-green-900" title="Generate Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No outstanding fees found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 print:hidden">
            {{ $outstandingFees->links() }}
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body {
            font-size: 12pt;
        }
        .print\:hidden {
            display: none !important;
        }
        .print\:block {
            display: block !important;
        }
        .shadow-md {
            box-shadow: none !important;
        }
        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }
    }
</style>
@endpush
@endsection