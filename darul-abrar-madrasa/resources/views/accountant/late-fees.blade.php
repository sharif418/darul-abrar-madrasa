@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Late Fees Management</h1>
        <a href="{{ route('accountant.reports') }}" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">Reports</a>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="border rounded p-4 bg-white">
          <div class="text-lg font-semibold text-gray-800">Overdue Fees</div>
          <div class="text-sm text-gray-600">Fees that passed due date and remain unpaid or partial.</div>
        </div>
        <div class="border rounded p-4 bg-white">
          <div class="text-lg font-semibold text-gray-800">Active Late Fee Policies</div>
          <div class="text-sm text-gray-600">Policies used to calculate late fees by type and grace period.</div>
        </div>
      </div>

      <form method="GET" class="mt-6 grid md:grid-cols-6 gap-3 text-sm">
        <input name="student_id" value="{{ request('student_id') }}" placeholder="Student ID" class="border rounded px-3 py-2" />
        <input name="fee_type" value="{{ request('fee_type') }}" placeholder="Fee Type" class="border rounded px-3 py-2" />
        <select name="policy" class="border rounded px-3 py-2">
          <option value="">Any Policy</option>
          @foreach(($policies ?? []) as $p)
            <option value="{{ $p->id }}" @selected((string)request('policy')===(string)$p->id)>{{ $p->name }}</option>
          @endforeach
        </select>
        <input type="date" name="due_before" value="{{ request('due_before') }}" class="border rounded px-3 py-2" />
        <input type="date" name="due_after" value="{{ request('due_after') }}" class="border rounded px-3 py-2" />
        <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
      </form>

      <form method="POST" action="{{ route('accountant.late-fees.apply') }}" class="mt-4">
        @csrf
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2"><input type="checkbox" onclick="document.querySelectorAll('.fee-check').forEach(cb => cb.checked = this.checked)" /></th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Fee ID</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Type</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Due Date</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Paid</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Days Overdue</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Policy</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Est. Late Fee</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              @php $overdueFees = $overdueFees ?? collect(); @endphp
              @forelse($overdueFees as $row)
                @php
                  $studentName = optional(optional($row->student)->user)->name ?? 'N/A';
                  $net = (float)($row->net_amount ?? $row->amount);
                  $paid = (float)($row->paid_amount ?? 0);
                  $dueDate = optional($row->due_date);
                  $days = $dueDate ? \Carbon\Carbon::parse($dueDate)->diffInDays(now(), false) : null;
                  $policy = $row->policy ?? null;
                  $estLate = $row->estimated_late_fee ?? null;
                @endphp
                <tr>
                  <td class="px-4 py-2">
                    <input class="fee-check" type="checkbox" name="fee_ids[]" value="{{ $row->id }}" />
                  </td>
                  <td class="px-4 py-2">#{{ $row->id }}</td>
                  <td class="px-4 py-2">{{ $studentName }}</td>
                  <td class="px-4 py-2 capitalize">{{ $row->fee_type ?? '-' }}</td>
                  <td class="px-4 py-2">{{ $dueDate? $dueDate->format('d M Y') : '-' }}</td>
                  <td class="px-4 py-2">৳ {{ number_format($net, 2) }}</td>
                  <td class="px-4 py-2">৳ {{ number_format($paid, 2) }}</td>
                  <td class="px-4 py-2">{{ $days !== null ? max(0,$days) : '-' }}</td>
                  <td class="px-4 py-2">{{ $policy->name ?? 'Auto' }}</td>
                  <td class="px-4 py-2">৳ {{ number_format((float)($estLate ?? 0), 2) }}</td>
                </tr>
              @empty
                <tr><td class="px-4 py-4 text-center text-gray-500" colspan="10">No overdue fees found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex items-center gap-3">
          <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Apply Late Fees to Selected</button>
          <a href="{{ route('accountant.late-fees') }}" class="px-4 py-2 bg-gray-100 border rounded hover:bg-gray-200">Refresh</a>
        </div>
      </form>

      <div class="mt-8">
        <div class="text-lg font-semibold text-gray-800 mb-2">Active Policies</div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Name</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Fee Type</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Grace (days)</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Type</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Amount</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Max Cap</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Compound</th>
                <th class="px-4 py-2 text-left text-gray-600 font-medium">Active</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              @forelse(($policies ?? []) as $p)
                <tr>
                  <td class="px-4 py-2">{{ $p->name }}</td>
                  <td class="px-4 py-2">{{ $p->fee_type ?? 'All' }}</td>
                  <td class="px-4 py-2">{{ $p->grace_period_days ?? 0 }}</td>
                  <td class="px-4 py-2 capitalize">{{ $p->calculation_type ?? '-' }}</td>
                  <td class="px-4 py-2">৳ {{ number_format((float)($p->amount ?? 0), 2) }}</td>
                  <td class="px-4 py-2">{{ $p->max_late_fee ? '৳ '.number_format((float)$p->max_late_fee, 2) : '-' }}</td>
                  <td class="px-4 py-2">{{ $p->compound ? 'Yes' : 'No' }}</td>
                  <td class="px-4 py-2">{{ $p->is_active ? 'Yes' : 'No' }}</td>
                </tr>
              @empty
                <tr><td class="px-4 py-4 text-center text-gray-500" colspan="8">No policies configured.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </x-card>
  </div>
</div>
@endsection
