@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Installment Plans</h1>
        <a href="{{ route('accountant.fees') }}" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">Manage Fees</a>
      </div>

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <input name="student_id" value="{{ request('student_id') }}" placeholder="Student ID" class="border rounded px-3 py-2" />
        <input name="fee_id" value="{{ request('fee_id') }}" placeholder="Fee ID" class="border rounded px-3 py-2" />
        <select name="status" class="border rounded px-3 py-2">
          <option value="">All Status</option>
          <option value="pending" @selected(request('status')==='pending')>Pending</option>
          <option value="paid" @selected(request('status')==='paid')>Paid</option>
          <option value="overdue" @selected(request('status')==='overdue')>Overdue</option>
        </select>
        <input type="date" name="due_from" value="{{ request('due_from') }}" class="border rounded px-3 py-2" />
        <input type="date" name="due_to" value="{{ request('due_to') }}" class="border rounded px-3 py-2" />
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
              <th class="px-4 py-2 text-left font-medium text-gray-600">Fee ID</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Total Amount</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Installments</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Next Due</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @forelse($fees as $fee)
              @php
                $studentName = optional(optional($fee->student)->user)->name ?? 'N/A';
                $next = method_exists($fee, 'getNextInstallmentAttribute') ? $fee->next_installment : optional($fee->installments)->where('status','pending')->sortBy('due_date')->first();
                $count = method_exists($fee, 'installments') ? $fee->installments()->count() : (is_iterable($fee->installments ?? null) ? count($fee->installments) : 0);
              @endphp
              <tr>
                <td class="px-4 py-2">#{{ $fee->id }}</td>
                <td class="px-4 py-2">{{ $studentName }}</td>
                <td class="px-4 py-2">৳ {{ number_format((float)($fee->net_amount ?? $fee->amount), 2) }}</td>
                <td class="px-4 py-2">{{ $count }}</td>
                <td class="px-4 py-2">
                  @if($next)
                    {{ optional($next->due_date)->format('d M Y') ?? '-' }} (৳ {{ number_format((float)($next->amount ?? 0) - (float)($next->paid_amount ?? 0), 2) }})
                  @else
                    -
                  @endif
                </td>
                <td class="px-4 py-2">
                  <div class="flex flex-wrap gap-2">
                    <a href="{{ route('accountant.installments.create', $fee) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create Plan</a>
                    <a href="{{ route('fees.show', $fee) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800">View Fee</a>
                  </div>
                </td>
              </tr>
              @if(method_exists($fee, 'installments'))
                <tr>
                  <td colspan="6" class="px-4 py-2 bg-gray-50">
                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-100">
                          <tr>
                            <th class="px-3 py-1.5 text-left text-gray-600">#</th>
                            <th class="px-3 py-1.5 text-left text-gray-600">Amount</th>
                            <th class="px-3 py-1.5 text-left text-gray-600">Due Date</th>
                            <th class="px-3 py-1.5 text-left text-gray-600">Paid</th>
                            <th class="px-3 py-1.5 text-left text-gray-600">Status</th>
                            <th class="px-3 py-1.5 text-left text-gray-600">Actions</th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                          @foreach($fee->installments()->orderBy('installment_number')->get() as $inst)
                            @php
                              $remaining = max(0, (float)$inst->amount - (float)($inst->paid_amount ?? 0));
                            @endphp
                            <tr>
                              <td class="px-3 py-1.5">{{ $inst->installment_number }}</td>
                              <td class="px-3 py-1.5">৳ {{ number_format((float)$inst->amount, 2) }}</td>
                              <td class="px-3 py-1.5">{{ optional($inst->due_date)->format('d M Y') ?? '-' }}</td>
                              <td class="px-3 py-1.5">৳ {{ number_format((float)($inst->paid_amount ?? 0), 2) }}</td>
                              <td class="px-3 py-1.5 capitalize">
                                {{ $inst->status }}
                                @if(($inst->status ?? '') !== 'paid' && optional($inst->due_date) && \Carbon\Carbon::parse($inst->due_date)->isPast())
                                  <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-700">Overdue</span>
                                @endif
                              </td>
                              <td class="px-3 py-1.5">
                                @if(($inst->status ?? '') !== 'paid')
                                  <form method="POST" action="{{ route('accountant.installments.store', $fee) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="installment_id" value="{{ $inst->id }}">
                                    <input type="hidden" name="action" value="record-payment">
                                    <input type="number" step="0.01" min="0.01" max="{{ $remaining }}" name="amount" value="{{ $remaining }}" class="border rounded px-2 py-1 w-24" />
                                    <button class="ml-2 px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">Pay</button>
                                  </form>
                                @else
                                  <span class="text-gray-500">Paid</span>
                                @endif
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              @endif
            @empty
              <tr>
                <td class="px-4 py-4 text-center text-gray-500" colspan="6">No installment plans found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </x-card>
  </div>
</div>
@endsection
