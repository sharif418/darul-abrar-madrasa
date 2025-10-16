@extends('layouts.app')

@section('title', 'Pay Fee')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">My Children</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.child.fees', $student) }}" class="text-indigo-600 hover:underline">Child Fees</a>
        <span class="mx-2">/</span>
        <span>Pay Fee</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Pay Fee</h1>
        <p class="text-gray-600 mt-1">
            Student: <span class="font-medium">{{ $student->user->name ?? 'Student' }}</span>
            · Invoice: <span class="font-medium">#{{ $fee->invoice_number ?? $fee->id }}</span>
        </p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Fee summary --}}
    @php
        $netAmount = method_exists($fee, 'getNetAmountAttribute') ? (float)$fee->net_amount : (float)$fee->amount;
        $remaining = max(0, $netAmount - (float)($fee->paid_amount ?? 0));
    @endphp
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-600">Fee Type</div>
                <div class="font-medium text-gray-900">{{ ucfirst($fee->fee_type) }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Due Date</div>
                <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($fee->due_date)->format('Y-m-d') }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Amount</div>
                <div class="font-medium text-gray-900">{{ number_format($netAmount, 2) }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Remaining</div>
                <div class="font-medium text-gray-900">{{ number_format($remaining, 2) }}</div>
            </div>
        </div>
    </x-card>

    {{-- Payment form --}}
    <x-card>
        <form method="POST" action="{{ route('guardian.fees.process-payment', $fee) }}" class="space-y-6">
            @csrf

            <div>
                <x-label for="amount" value="Amount to Pay" />
                <x-input id="amount" name="amount" type="number" min="0.01" step="0.01"
                         value="{{ old('amount', $remaining) }}" class="mt-1 w-full" required />
                <x-input-error for="amount" class="mt-1" />
            </div>

            <div>
                <x-label for="payment_method" value="Payment Method" />
                <x-select id="payment_method" name="payment_method" class="mt-1 w-full">
                    @php $method = old('payment_method', 'online'); @endphp
                    <option value="online" {{ $method === 'online' ? 'selected' : '' }}>Online Gateway</option>
                    <option value="bank" {{ $method === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="mobile" {{ $method === 'mobile' ? 'selected' : '' }}>Mobile Wallet</option>
                </x-select>
                <x-input-error for="payment_method" class="mt-1" />
            </div>

            <div>
                <x-label for="transaction_id" value="Transaction ID (if applicable)" />
                <x-input id="transaction_id" name="transaction_id" type="text" value="{{ old('transaction_id') }}" class="mt-1 w-full" />
                <x-input-error for="transaction_id" class="mt-1" />
            </div>

            <div>
                <x-label for="remarks" value="Remarks" />
                <textarea id="remarks" name="remarks" rows="3" class="mt-1 w-full border-gray-300 rounded-md">{{ old('remarks') }}</textarea>
                <x-input-error for="remarks" class="mt-1" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('guardian.child.fees', $student) }}" class="inline-flex items-center px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
                <x-button type="submit">Confirm Payment</x-button>
            </div>
        </form>
    </x-card>

    {{-- Footer --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('guardian.child.fees', $student) }}" class="text-indigo-600 hover:underline">Back to Fees</a>
        <span class="mx-2">&middot;</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">Back to Children</a>
    </div>
</div>
@endsection
