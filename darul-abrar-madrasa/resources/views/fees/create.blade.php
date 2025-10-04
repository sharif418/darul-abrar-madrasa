@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('fees.index') }}" class="hover:text-blue-600">Fees</a>
            <span>/</span>
            <span class="text-gray-900">Create New Fee</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Create New Fee</h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('fees.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Student <span class="text-red-500">*</span>
                    </label>
                    <select id="student_id" name="student_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('student_id') border-red-500 @enderror">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }} ({{ $student->admission_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Fee Type -->
                <div>
                    <label for="fee_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Fee Type <span class="text-red-500">*</span>
                    </label>
                    <select id="fee_type" name="fee_type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('fee_type') border-red-500 @enderror">
                        <option value="">Select Fee Type</option>
                        @foreach($feeTypes as $type)
                            <option value="{{ $type }}" {{ old('fee_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('fee_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <input id="amount" type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror">
                    @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Due Date <span class="text-red-500">*</span>
                    </label>
                    <input id="due_date" type="date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('due_date') border-red-500 @enderror">
                    @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Payment Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror">
                        <option value="unpaid" {{ old('status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Paid Amount -->
                <div id="payment_details" class="hidden">
                    <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Paid Amount
                    </label>
                    <input id="paid_amount" type="number" name="paid_amount" value="{{ old('paid_amount') }}" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Payment Method -->
                <div id="payment_method_div" class="hidden">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Method
                    </label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                    </select>
                </div>
                
                <!-- Transaction ID -->
                <div id="transaction_id_div" class="hidden">
                    <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Transaction ID
                    </label>
                    <input id="transaction_id" type="text" name="transaction_id" value="{{ old('transaction_id') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Remarks -->
                <div class="md:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                        Remarks
                    </label>
                    <textarea id="remarks" name="remarks" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('remarks') }}</textarea>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Create Fee
                </button>
                <a href="{{ route('fees.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg inline-block text-center">
                    Cancel
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
        
        togglePaymentFields();
        statusSelect.addEventListener('change', togglePaymentFields);
    });
</script>
@endpush
@endsection
