@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Manage Fees</h1>
        <a href="{{ route('accountant.reports') }}" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">Reports</a>
      </div>

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <select name="status" class="border rounded px-3 py-2">
          <option value="">All Status</option>
          <option value="unpaid" @selected(request('status')==='unpaid')>Unpaid</option>
          <option value="partial" @selected(request('status')==='partial')>Partial</option>
          <option value="paid" @selected(request('status')==='paid')>Paid</option>
        </select>
        <input name="fee_type" value="{{ request('fee_type') }}" placeholder="Fee Type" class="border rounded px-3 py-2" />
        <input name="class_id" value="{{ request('class_id') }}" placeholder="Class ID" class="border rounded px-3 py-2" />
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2" />
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2" />
        <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
      </form>

      @if(session('success'))
        <x-alert type="success" :message="session('success')" />
      @endif
      @if(session('error'))
        <x-alert type="error" :message="session('error')" />
      @endif

      @php $fees = $fees ?? collect(); @endphp
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Type</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Paid</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Due Date</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @forelse($fees as $fee)
              @php
                $studentName = optional(optional($fee->student)->user)->name ?? 'N/A';
                $net = (float)($fee->net_amount ?? $fee->amount);
                $paid = (float)($fee->paid_amount ?? 0);
              @endphp
              <tr>
                <td class="px-4 py-2">{{ $studentName }}</td>
                <td class="px-4 py-2 capitalize">{{ $fee->fee_type ?? '-' }}</td>
                <td class="px-4 py-2">৳ {{ number_format($net, 2) }}</td>
                <td class="px-4 py-2">৳ {{ number_format($paid, 2) }}</td>
                <td class="px-4 py-2">{{ optional($fee->due_date)->format('d M Y') ?? '-' }}</td>
                <td class="px-4 py-2 capitalize">{{ $fee->status ?? '-' }}</td>
                <td class="px-4 py-2">
                  <div class="flex flex-wrap gap-2">
                    @if(($fee->status ?? '') !== 'paid')
                      <a href="{{ route('accountant.fees.record-payment', $fee) }}" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">Record Payment</a>
                    @endif
                    <a href="{{ route('fees.show', $fee) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800">View</a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td class="px-4 py-4 text-center text-gray-500" colspan="7">No fees found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($fees, 'links'))
        <div class="mt-4">{{ $fees->links() }}</div>
      @endif
    </x-card>
  </div>
</div>
@endsection
