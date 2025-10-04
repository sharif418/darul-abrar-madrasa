@extends('layouts.app')

@section('header', 'Fee Collection Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Fee Collection Report</h1>
        <div class="flex space-x-2">
            <a href="{{ route('fees.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Fees
            </a>
            <a href="{{ route('fees.reports.outstanding') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Outstanding Report
            </a>
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 print:hidden">
        <h2 class="text-lg font-semibold mb-4">Filter Collections</h2>
        <form action="{{ route('fees.reports.collection') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="fee_type" class="block text-sm font-medium text-gray-700 mb-1">Fee Type</label>
                <select id="fee_type" name="fee_type" class="block mt-1 w-full">
                    <option value="">All Types</option>
                    @foreach($feeTypes as $type)
                        <option value="{{ $type }}" {{ request('fee_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Payment Date From</label>
                <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="block mt-1 w-full" />
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Payment Date To</label>
                <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="block mt-1 w-full" />
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Apply Filters
                </button>
                <a href="{{ route('fees.reports.collection') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
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
            <p class="text-lg">Fee Collection Report</p>
            <p class="text-sm text-gray-600">
                Generated on: {{ now()->format('F d, Y h:i A') }}
                @if(request('date_from') || request('date_to'))
                    <br>
                    Period: 
                    {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All time' }}
                    to
                    {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}
                @endif
                @if(request('fee_type'))
                    <br>Fee Type: {{ ucfirst(request('fee_type')) }}
                @endif
            </p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Total Collection</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalCollected, 2) }}</h3>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Transactions</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $collections->total() }}</h3>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Full Payments</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $collections->where('status', 'paid')->count() }}</h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Partial Payments</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $collections->where('status', 'partial')->count() }}</h3>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection by Fee Type -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-800">Collection by Fee Type</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Collected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($summary as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($item->fee_type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->total_transactions }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->total_collected, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format(($item->total_collected / $totalCollected) * 100, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $collections->total() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ number_format($totalCollected, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Collection Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-800">Collection Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($collections as $fee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : 'N/A' }}
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($fee->paid_amount, 2) }}</div>
                            @if($fee->status == 'partial')
                                <div class="text-xs text-gray-500">of {{ number_format($fee->amount, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($fee->status == 'paid')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Paid
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Partial
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $fee->payment_method ? ucfirst($fee->payment_method) : 'N/A' }}
                            @if($fee->transaction_id)
                                <div class="text-xs text-gray-500">{{ $fee->transaction_id }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium print:hidden">
                            <a href="{{ route('fees.show', $fee->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('fees.invoice', $fee->id) }}" class="text-green-600 hover:text-green-900" title="Generate Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No collections found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 print:hidden">
            {{ $collections->links() }}
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