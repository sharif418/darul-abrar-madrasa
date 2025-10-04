@extends('layouts.app')

@section('header', 'Edit Fee')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Fee</h1>
        <div class="flex space-x-2">
            <a href="{{ route('fees.show', $fee->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Details
            </a>
            <a href="{{ route('fees.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Fees
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('fees.update', $fee->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Student</label>
                    <select id="student_id" name="student_id" class="block mt-1 w-full" required>
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id', $fee->student_id) == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->student_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="fee_type" class="block text-sm font-medium text-gray-700 mb-1">Fee Type</label>
                    <select id="fee_type" name="fee_type" class="block mt-1 w-full" required>
                        <option value="">Select Fee Type</option>
                        @foreach($feeTypes as $type)
                            <option value="{{ $type }}" {{ old('fee_type', $fee->fee_type) == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('fee_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input id="amount" type="number" name="amount" value="{{ old('amount', $fee->amount) }}" step="0.01" min="0" class="block mt-1 w-full" required />
                    @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input id="due_date" type="date" name="due_date" value="{{ old('due_date', $fee->due_date->format('Y-m-d')) }}" class="block mt-1 w-full" required />
                    @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select id="status" name="status" class="block mt-1 w-full" required>
                        <option value="unpaid" {{ old('status', $fee->status) == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ old('status', $fee->status) == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ old('status', $fee->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div id="payment_details" class="{{ in_array(old('status', $fee->status), ['paid', 'partial']) ? '' : 'hidden' }}">
                    <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                    <input id="paid_amount" type="number" name="paid_amount" value="{{ old('paid_amount', $fee->paid_amount) }}" step="0.01" min="0" class="block mt-1 w-full" />
                    @error('paid_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div id="payment_method_div" class="{{ in_array(old('status', $fee->status), ['paid', 'partial']) ? '' : 'hidden' }}">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="block mt-1 w-full">
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method', $fee->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $fee->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="check" {{ old('payment_method', $fee->payment_method) == 'check' ? 'selected' : '' }}>Check</option>
                        <option value="online" {{ old('payment_method', $fee->payment_method) == 'online' ? 'selected' : '' }}>Online Payment</option>
                    </select>
                    @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div id="transaction_id_div" class="{{ in_array(old('status', $fee->status), ['paid', 'partial']) ? '' : 'hidden' }}">
                    <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                    <input id="transaction_id" type="text" name="transaction_id" value="{{ old('transaction_id', $fee->transaction_id) }}" class="block mt-1 w-full" />
                    @error('transaction_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea id="remarks" name="remarks" class="block mt-1 w-full" rows="3">{{ old('remarks', $fee->remarks) }}</textarea>
                    @error('remarks')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    Update Fee
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const paymentDetails = document.getElementById('payment_details');
        const paymentMethodDiv = document.getElementById('payment_method_div');
        const transactionIdDiv = document.getElementById('transaction_id_div');
        
        function togglePaymentFields() {
            if (statusSelect.value === 'unpaid') {
                paymentDetails.classList.add('hidden');
                paymentMethodDiv.classList.add('hidden');
                transactionIdDiv.classList.add('hidden');
            } else {
                paymentDetails.classList.remove('hidden');
                paymentMethodDiv.classList.remove('hidden');
                transactionIdDiv.classList.remove('hidden');
            }
        }
        
        // Listen for changes
        statusSelect.addEventListener('change', togglePaymentFields);
    });
</script>
@endpush
@endsection