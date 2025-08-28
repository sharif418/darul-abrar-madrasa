@extends('layouts.app')

@section('header', 'My Fees')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Fees</h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Total Paid</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $fees->where('status', 'paid')->count() }}</h3>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Partially Paid</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $fees->where('status', 'partial')->count() }}</h3>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-circle text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Unpaid</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $fees->where('status', 'unpaid')->count() }}</h3>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $student->name }}</h2>
                    <p class="text-gray-600">{{ $student->student_id }} | {{ $student->class->name }}</p>
                </div>
            </div>
            
            <div class="flex flex-col">
                <div class="flex items-center mb-2">
                    <span class="text-gray-600 mr-2">Roll Number:</span>
                    <span class="font-semibold">{{ $student->roll_number }}</span>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-600 mr-2">Contact:</span>
                    <span class="font-semibold">{{ $student->phone }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fees Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-800">Fee History</h2>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Details</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($fees as $fee)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($fee->fee_type) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ number_format($fee->amount, 2) }}</div>
                        @if($fee->status == 'partial')
                            <div class="text-xs text-gray-500">Paid: {{ number_format($fee->paid_amount, 2) }}</div>
                            <div class="text-xs text-gray-500">Due: {{ number_format($fee->remainingAmount, 2) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $fee->due_date->format('M d, Y') }}
                        @if($fee->isOverdue)
                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">OVERDUE</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($fee->status == 'paid')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Paid
                            </span>
                        @elseif($fee->status == 'partial')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Partial
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Unpaid
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($fee->status != 'unpaid')
                            <div>Payment Date: {{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : 'N/A' }}</div>
                            <div>Method: {{ $fee->payment_method ? ucfirst($fee->payment_method) : 'N/A' }}</div>
                            @if($fee->transaction_id)
                                <div>Transaction ID: {{ $fee->transaction_id }}</div>
                            @endif
                        @else
                            <span class="text-gray-400">No payment recorded</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No fees found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4">
            {{ $fees->links() }}
        </div>
    </div>
</div>
@endsection