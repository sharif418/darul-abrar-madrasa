@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Payment Reconciliation</h1>
          <p class="text-sm text-gray-600">Match internal payments with bank statements and mark as reconciled.</p>
        </div>
        <a href="{{ route('accountant.reports') }}" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">Reports</a>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2" />
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2" />
        <select name="payment_method" class="border rounded px-3 py-2">
          <option value="">All Methods</option>
          <option value="cash" @selected(request('payment_method')==='cash')>Cash</option>
          <option value="bank" @selected(request('payment_method')==='bank')>Bank Transfer</option>
          <option value="online" @selected(request('payment_method')==='online')>Online</option>
        </select>
        <select name="reconciled" class="border rounded px-3 py-2">
          <option value="">Any Status</option>
          <option value="0" @selected(request('reconciled')==='0')>Unreconciled</option>
          <option value="1" @selected(request('reconciled')==='1')>Reconciled</option>
        </select>
        <input name="q" value="{{ request('q') }}" placeholder="Search student/txn id" class="border rounded px-3 py-2" />
        <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply</button>
      </form>

      <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <form method="POST" action="{{ route('accountant.reconciliation') }}">
            @csrf

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2">
                      <input type="checkbox" onclick="document.querySelectorAll('.payment-check').forEach(cb => cb.checked = this.checked)" />
                    </th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Method</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Txn ID</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  @php $payments = $payments ?? collect(); @endphp
                  @forelse($payments as $p)
                    <tr>
                      <td class="px-4 py-2">
                        @if(!($p->is_reconciled ?? false))
                          <input class="payment-check" type="checkbox" name="payment_ids[]" value="{{ $p->id }}" />
                        @endif
                      </td>
                      <td class="px-4 py-2">{{ optional($p->payment_date ?? $p->created_at)->format('d M Y') }}</td>
                      <td class="px-4 py-2">{{ optional(optional($p->student)->user)->name ?? 'N/A' }}</td>
                      <td class="px-4 py-2 capitalize">{{ $p->payment_method ?? '-' }}</td>
                      <td class="px-4 py-2">{{ $p->transaction_id ?? '-' }}</td>
                      <td class="px-4 py-2">৳ {{ number_format((float)($p->amount ?? $p->paid_amount ?? 0), 2) }}</td>
                      <td class="px-4 py-2">
                        @if($p->is_reconciled ?? false)
                          <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Reconciled</span>
                        @else
                          <span class="inline-flex items-center px-2 py-0.5 rounded bg-yellow-100 text-yellow-700">Unreconciled</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td class="px-4 py-4 text-center text-gray-500" colspan="7">No payments found.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="mt-4 flex items-center gap-3">
              <button name="action" value="mark-reconciled" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Mark Selected as Reconciled</button>
              <button name="action" value="mark-unreconciled" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Mark Selected as Unreconciled</button>
            </div>
          </form>
        </div>

        <div>
          <div class="border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Bank Statement Upload</div>
            <div class="text-sm text-gray-600">Upload a bank statement CSV/XLSX to assist reconciliation.</div>

            <form method="POST" action="{{ route('accountant.reconciliation') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
              @csrf
              <input type="hidden" name="action" value="upload-statement" />
              <input type="file" name="statement_file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="w-full border rounded px-3 py-2" />
              <x-input-error :messages="$errors->first('statement_file')" />
              <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Upload</button>
                <a href="#" class="text-sm text-gray-600 hover:text-gray-800">Download Sample CSV</a>
              </div>
              <p class="text-xs text-gray-500">Expected columns: date, amount, method, transaction_id, reference.</p>
            </form>
          </div>

          <div class="mt-4 border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Summary</div>
            <dl class="mt-2 space-y-1 text-sm">
              <div class="flex justify-between">
                <dt class="text-gray-600">Unreconciled Count</dt>
                <dd class="font-semibold text-gray-800">{{ $summary['unreconciled_count'] ?? 0 }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-600">Unreconciled Amount</dt>
                <dd class="font-semibold text-gray-800">৳ {{ number_format((float)($summary['unreconciled_amount'] ?? 0), 2) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-600">Reconciled This Month</dt>
                <dd class="font-semibold text-gray-800">৳ {{ number_format((float)($summary['reconciled_month'] ?? 0), 2) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </x-card>
  </div>
</div>
@endsection
