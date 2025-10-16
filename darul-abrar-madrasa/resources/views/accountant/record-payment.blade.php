@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <h1 class="text-2xl font-bold text-gray-800 mb-4">Record Payment</h1>

      @if(session('success'))
        <x-alert type="success" :message="session('success')" />
      @endif
      @if(session('error'))
        <x-alert type="error" :message="session('error')" />
      @endif

      @php
        $fee = $fee ?? null;
        $student = optional($fee)->student;
        $studentName = optional(optional($student)->user)->name ?? 'N/A';
        $net = (float)optional($fee)->net_amount ?? (float)optional($fee)->amount ?? 0;
        $paid = (float)optional($fee)->paid_amount ?? 0;
        $remaining = max(0, $net - $paid);
      @endphp

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
          <div class="text-gray-500">Student</div>
          <div class="font-semibold text-gray-800">{{ $studentName }}</div>
        </div>
        <div>
          <div class="text-gray-500">Fee Type</div>
          <div class="font-semibold text-gray-800 capitalize">{{ $fee->fee_type ?? '-' }}</div>
        </div>
        <div>
          <div class="text-gray-500">Amount</div>
          <div class="font-semibold text-gray-800">৳ {{ number_format($net, 2) }}</div>
        </div>
        <div>
          <div class="text-gray-500">Paid</div>
          <div class="font-semibold text-gray-800">৳ {{ number_format($paid, 2) }}</div>
        </div>
        <div>
          <div class="text-gray-500">Remaining</div>
          <div class="font-semibold text-gray-800">৳ {{ number_format($remaining, 2) }}</div>
        </div>
        <div>
          <div class="text-gray-500">Status</div>
          <div class="font-semibold text-gray-800 capitalize">{{ $fee->status ?? '-' }}</div>
        </div>
      </div>

      @if(($fee->status ?? '') === 'paid')
        <div class="mt-4">
          <x-alert type="info" message="This fee is already fully paid." />
        </div>
      @else
        <form method="POST" action="{{ route('accountant.fees.process-payment', $fee) }}" class="mt-6 space-y-4">
          @csrf
          <div>
            <label class="block text-sm text-gray-600 mb-1">Amount</label>
            <input
              name="paid_amount"
              type="number"
              step="0.01"
              min="0.01"
              max="{{ $remaining }}"
              value="{{ old('paid_amount', $remaining) }}"
              class="w-full border rounded px-3 py-2"
              required
            />
            @error('paid_amount')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Payment Method</label>
            <select name="payment_method" class="w-full border rounded px-3 py-2" required>
              <option value="cash" @selected(old('payment_method')==='cash')>Cash</option>
              <option value="bank" @selected(old('payment_method')==='bank')>Bank Transfer</option>
              <option value="online" @selected(old('payment_method')==='online')>Online</option>
            </select>
            @error('payment_method')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Transaction ID (optional)</label>
            <input name="transaction_id" type="text" value="{{ old('transaction_id') }}" class="w-full border rounded px-3 py-2" />
            @error('transaction_id')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Remarks (optional)</label>
            <textarea name="remarks" class="w-full border rounded px-3 py-2" rows="3">{{ old('remarks') }}</textarea>
            @error('remarks')<x-input-error :messages="$message" />@enderror
          </div>

          <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Record Payment</button>
            <a href="{{ route('accountant.fees') }}" class="px-4 py-2 bg-gray-100 border rounded hover:bg-gray-200">Back</a>
          </div>
        </form>
      @endif
    </x-card>
  </div>
</div>
@endsection
