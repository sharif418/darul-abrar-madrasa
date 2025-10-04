@extends('layouts.app')

@section('header', 'Record Payment')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Record Payment</h1>
            <a href="{{ route('fees.show', $fee->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Fee Details
            </a>
        </div>

        <!-- Fee Information Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-800">Fee Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Student Name</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $fee->student->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Fee Type</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ ucfirst($fee->fee_type) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($fee->amount, 2) }} BDT</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Amount to Pay</p>
                        <p class="mt-1 text-lg font-semibold text-red-600">{{ number_format($fee->remainingAmount, 2) }} BDT</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-800">Payment Details</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('fees.record-payment', $fee->id) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
                            <input 
                                id="paid_amount" 
                                type="number" 
                                name="paid_amount" 
                                value="{{ old('paid_amount', $fee->remainingAmount) }}" 
                                step="0.01" 
                                min="0.01" 
                                max="{{ $fee->remainingAmount }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('paid_amount') border-red-500 @enderror" 
                                required 
                            />
                            @error('paid_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                            <select 
                                id="payment_method" 
                                name="payment_method" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_method') border-red-500 @enderror" 
                                required
                            >
                                <option value="">Select Payment Method</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                <option value="bkash" {{ old('payment_method') == 'bkash' ? 'selected' : '' }}>bKash</option>
                                <option value="nagad" {{ old('payment_method') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                                <option value="rocket" {{ old('payment_method') == 'rocket' ? 'selected' : '' }}>Rocket</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">Transaction ID (Optional)</label>
                            <input 
                                id="transaction_id" 
                                type="text" 
                                name="transaction_id" 
                                value="{{ old('transaction_id') }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('transaction_id') border-red-500 @enderror" 
                                placeholder="Enter transaction/reference number"
                            />
                            @error('transaction_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks (Optional)</label>
                            <textarea 
                                id="remarks" 
                                name="remarks" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('remarks') border-red-500 @enderror" 
                                rows="3"
                                placeholder="Add any additional notes"
                            >{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="{{ route('fees.show', $fee->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg inline-flex items-center">
                                Cancel
                            </a>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Record Payment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
