@extends('layouts.app')

@section('header', 'Create New Fee')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Create New Fee</h1>
        <x-button href="{{ route('fees.index') }}" color="secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Fees
        </x-button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('fees.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label for="student_id" value="Student" />
                    <x-select id="student_id" name="student_id" class="block mt-1 w-full" required>
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->student_id }})
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="student_id" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="fee_type" value="Fee Type" />
                    <x-select id="fee_type" name="fee_type" class="block mt-1 w-full" required>
                        <option value="">Select Fee Type</option>
                        @foreach($feeTypes as $type)
                            <option value="{{ $type }}" {{ old('fee_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="fee_type" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="amount" value="Amount" />
                    <x-input id="amount" type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" class="block mt-1 w-full" required />
                    <x-input-error for="amount" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="due_date" value="Due Date" />
                    <x-input id="due_date" type="date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" class="block mt-1 w-full" required />
                    <x-input-error for="due_date" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="status" value="Payment Status" />
                    <x-select id="status" name="status" class="block mt-1 w-full" required>
                        <option value="unpaid" {{ old('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </x-select>
                    <x-input-error for="status" class="mt-2" />
                </div>
                
                <div id="payment_details" class="hidden">
                    <x-label for="paid_amount" value="Paid Amount" />
                    <x-input id="paid_amount" type="number" name="paid_amount" value="{{ old('paid_amount') }}" step="0.01" min="0" class="block mt-1 w-full" />
                    <x-input-error for="paid_amount" class="mt-2" />
                </div>
                
                <div id="payment_method_div" class="hidden">
                    <x-label for="payment_method" value="Payment Method" />
                    <x-select id="payment_method" name="payment_method" class="block mt-1 w-full">
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                    </x-select>
                    <x-input-error for="payment_method" class="mt-2" />
                </div>
                
                <div id="transaction_id_div" class="hidden">
                    <x-label for="transaction_id" value="Transaction ID" />
                    <x-input id="transaction_id" type="text" name="transaction_id" value="{{ old('transaction_id') }}" class="block mt-1 w-full" />
                    <x-input-error for="transaction_id" class="mt-2" />
                </div>
                
                <div class="md:col-span-2">
                    <x-label for="remarks" value="Remarks" />
                    <x-textarea id="remarks" name="remarks" class="block mt-1 w-full" rows="3">{{ old('remarks') }}</x-textarea>
                    <x-input-error for="remarks" class="mt-2" />
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <x-button type="submit" color="primary">
                    Create Fee
                </x-button>
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
        
        // Initial toggle
        togglePaymentFields();
        
        // Listen for changes
        statusSelect.addEventListener('change', togglePaymentFields);
    });
</script>
@endpush
@endsection