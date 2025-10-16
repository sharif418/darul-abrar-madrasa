@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Waiver Management</h1>
          <p class="text-sm text-gray-600">Create, review and approve fee waivers.</p>
        </div>
        <a href="{{ route('accountant.waivers.create') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
          + Create Waiver
        </a>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <div class="mt-4 border-b border-gray-200">
        @php $tab = request('status'); @endphp
        <nav class="-mb-px flex flex-wrap gap-4 text-sm" aria-label="Tabs">
          <a href="{{ route('accountant.waivers') }}" class="px-3 py-2 border-b-2 {{ !$tab ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300' }}">All</a>
          <a href="{{ route('accountant.waivers', ['status' => 'pending'] + request()->except('page')) }}" class="px-3 py-2 border-b-2 {{ $tab==='pending' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300' }}">Pending</a>
          <a href="{{ route('accountant.waivers', ['status' => 'approved'] + request()->except('page')) }}" class="px-3 py-2 border-b-2 {{ $tab==='approved' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300' }}">Approved</a>
          <a href="{{ route('accountant.waivers', ['status' => 'rejected'] + request()->except('page')) }}" class="px-3 py-2 border-b-2 {{ $tab==='rejected' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300' }}">Rejected</a>
          <a href="{{ route('accountant.waivers', ['status' => 'expired'] + request()->except('page')) }}" class="px-3 py-2 border-b-2 {{ $tab==='expired' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300' }}">Expired</a>
        </nav>
      </div>

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input name="student_id" value="{{ request('student_id') }}" placeholder="Student ID" class="border rounded px-3 py-2" />
        <select name="waiver_type" class="border rounded px-3 py-2">
          <option value="">All Types</option>
          @foreach(['scholarship','financial_aid','merit','sibling_discount','staff_child','other'] as $type)
            <option value="{{ $type }}" @selected(request('waiver_type')===$type)>{{ str_replace('_',' ',ucfirst($type)) }}</option>
          @endforeach
        </select>
        <select name="amount_type" class="border rounded px-3 py-2">
          <option value="">Amount Type</option>
          <option value="percentage" @selected(request('amount_type')==='percentage')>Percentage</option>
          <option value="fixed" @selected(request('amount_type')==='fixed')>Fixed</option>
        </select>
        <input type="date" name="valid_from" value="{{ request('valid_from') }}" class="border rounded px-3 py-2" />
        <input type="date" name="valid_until" value="{{ request('valid_until') }}" class="border rounded px-3 py-2" />
        <div class="flex gap-2">
          <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
          @if(request()->hasAny(['student_id','waiver_type','amount_type','valid_from','valid_until']))
            <a href="{{ route('accountant.waivers', ['status'=>request('status')]) }}" class="px-3 py-2 border rounded hover:bg-gray-50">Reset</a>
          @endif
        </div>
      </form>

      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Type</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Applies To</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Validity</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Requested By</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @php $waivers = $waivers ?? collect(); @endphp
            @forelse($waivers as $w)
              @php
                $studentName = optional(optional($w->student)->user)->name ?? ('ID #'.$w->student_id);
                $applies = $w->fee_id ? ('Fee #'.$w->fee_id) : 'All Fees';
                $validity = optional($w->valid_from)->format('d M Y').' - '.(optional($w->valid_until)->format('d M Y') ?? '∞');
                $amt = (float)($w->amount ?? 0);
                $amtLabel = $w->amount_type === 'percentage' ? number_format($amt,2).'%' : '৳ '.number_format($amt,2);
              @endphp
              <tr>
                <td class="px-4 py-2">
                  <div class="font-medium text-gray-900">{{ $studentName }}</div>
                  <div class="text-xs text-gray-500">ID: {{ $w->student_id }}</div>
                </td>
                <td class="px-4 py-2 capitalize">{{ str_replace('_',' ',$w->waiver_type ?? '-') }}</td>
                <td class="px-4 py-2">{{ $amtLabel }}</td>
                <td class="px-4 py-2">{{ $applies }}</td>
                <td class="px-4 py-2">{{ $validity }}</td>
                <td class="px-4 py-2">
                  @php $status = $w->status ?? 'pending'; @endphp
                  @if($status==='approved')
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Approved</span>
                  @elseif($status==='rejected')
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-700">Rejected</span>
                  @elseif($status==='expired')
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">Expired</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-yellow-100 text-yellow-700">Pending</span>
                  @endif
                </td>
                <td class="px-4 py-2 text-sm">
                  {{ optional($w->createdBy)->name ?? 'System' }}
                  <div class="text-xs text-gray-500">{{ optional($w->created_at)->format('d M Y') }}</div>
                </td>
                <td class="px-4 py-2">
                  <div class="flex flex-wrap gap-2">
                    @if(($w->status ?? 'pending')==='pending')
                      <form method="POST" action="{{ route('accountant.waivers.approve', $w) }}">
                        @csrf
                        <button class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">Approve</button>
                      </form>
                      <form method="POST" action="{{ route('accountant.waivers.reject', $w) }}" onsubmit="return confirm('Reject this waiver request?');">
                        @csrf
                        <input type="hidden" name="reason" value="Rejected by accountant." />
                        <button class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
                      </form>
                    @else
                      <a href="{{ route('accountant.waivers') }}" class="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800">View</a>
                    @endif
                  </div>
                </td>
              </tr>
              @if(!empty($w->reason))
                <tr class="bg-gray-50">
                  <td class="px-4 py-2 text-xs text-gray-600" colspan="8">
                    <span class="font-semibold text-gray-700">Reason:</span> {{ $w->reason }}
                    @if(!empty($w->rejection_reason) && ($w->status ?? '')==='rejected')
                      <span class="ml-3 font-semibold text-gray-700">Rejection:</span> {{ $w->rejection_reason }}
                    @endif
                  </td>
                </tr>
              @endif
            @empty
              <tr>
                <td class="px-4 py-4 text-center text-gray-500" colspan="8">No waivers found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($waivers, 'links'))
        <div class="mt-4">{{ $waivers->appends(request()->query())->links() }}</div>
      @endif

    </x-card>
  </div>
</div>
@endsection
