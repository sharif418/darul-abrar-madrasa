@extends('layouts.app')

@section('header', 'Fee Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Fee Details</h1>
        <div class="flex space-x-2">
            <x-button href="{{ route('fees.index') }}" color="secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Fees
            </x-button>
            <x-button href="{{ route('fees.edit', $fee->id) }}" color="warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </x-button>
            <x-button href="{{ route('fees.generate-invoice', $fee->id) }}" color="success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Generate Invoice
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Fee Details Card -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Fee Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Fee ID</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->id }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Fee Type</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ ucfirst($fee->fee_type) }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Amount</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($fee->amount, 2) }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Due Date</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $fee->due_date->format('M d, Y') }}
                                @if($fee->isOverdue)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">OVERDUE</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">
                                @if($fee->status == 'paid')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                @elseif($fee->status == 'partial')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Partially Paid
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Unpaid
                                    </span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created At</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        
                        @if($fee->status != 'unpaid')
                            <div>
                                <p class="text-sm font-medium text-gray-500">Paid Amount</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($fee->paid_amount, 2) }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Date</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Method</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->payment_method ? ucfirst($fee->payment_method) : 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">Transaction ID</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->transaction_id ?: 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">Collected By</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->collectedBy ? $fee->collectedBy->name : 'N/A' }}</p>
                            </div>
                        @endif
                        
                        @if($fee->status == 'partial')
                            <div>
                                <p class="text-sm font-medium text-gray-500">Remaining Amount</p>
                                <p class="mt-1 text-lg font-semibold text-red-600">{{ number_format($fee->remainingAmount, 2) }}</p>
                            </div>
                        @endif
                        
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Remarks</p>
                            <p class="mt-1 text-lg text-gray-900">{{ $fee->remarks ?: 'No remarks' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Info and Payment Actions -->
        <div class="space-y-6">
            <!-- Student Information -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Student Information</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 rounded-full p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $fee->student->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $fee->student->student_id }}</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-500">Class</p>
                            <p class="text-gray-900">{{ $fee->student->class->name }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-500">Roll Number</p>
                            <p class="text-gray-900">{{ $fee->student->roll_number }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Contact</p>
                            <p class="text-gray-900">{{ $fee->student->phone }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('students.show', $fee->student->id) }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Student Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Actions -->
            @if($fee->status != 'paid')
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Record Payment</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('fees.record-payment', $fee->id) }}" method="POST">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <x-label for="paid_amount" value="Payment Amount" />
                                <x-input id="paid_amount" type="number" name="paid_amount" value="{{ old('paid_amount', $fee->remainingAmount) }}" step="0.01" min="0.01" max="{{ $fee->remainingAmount }}" class="block mt-1 w-full" required />
                                <x-input-error for="paid_amount" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-label for="payment_method" value="Payment Method" />
                                <x-select id="payment_method" name="payment_method" class="block mt-1 w-full" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                </x-select>
                                <x-input-error for="payment_method" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-label for="transaction_id" value="Transaction ID (Optional)" />
                                <x-input id="transaction_id" type="text" name="transaction_id" value="{{ old('transaction_id') }}" class="block mt-1 w-full" />
                                <x-input-error for="transaction_id" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-label for="remarks" value="Remarks (Optional)" />
                                <x-textarea id="remarks" name="remarks" class="block mt-1 w-full" rows="2">{{ old('remarks') }}</x-textarea>
                                <x-input-error for="remarks" class="mt-2" />
                            </div>
                            
                            <div class="flex justify-end">
                                <x-button type="submit" color="success">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Record Payment
                                </x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection