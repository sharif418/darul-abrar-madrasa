@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Accountants</h1>
        <a href="{{ route('accountants.create') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
          + Create Accountant
        </a>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Search name/email/employee ID" class="border rounded px-3 py-2" />
        <select name="active" class="border rounded px-3 py-2">
          <option value="">Any Status</option>
          <option value="1" @selected(request('active')==='1')>Active</option>
          <option value="0" @selected(request('active')==='0')>Inactive</option>
        </select>
        <select name="can_approve_waivers" class="border rounded px-3 py-2">
          <option value="">Approval Permission</option>
          <option value="1" @selected(request('can_approve_waivers')==='1')>Can Approve</option>
          <option value="0" @selected(request('can_approve_waivers')==='0')>Cannot Approve</option>
        </select>
        <input name="joining_from" type="date" value="{{ request('joining_from') }}" class="border rounded px-3 py-2" />
        <input name="joining_to" type="date" value="{{ request('joining_to') }}" class="border rounded px-3 py-2" />
        <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
        @if(request()->hasAny(['q','active','can_approve_waivers','joining_from','joining_to']))
          <a href="{{ route('accountants.index') }}" class="px-3 py-2 border rounded hover:bg-gray-50">Reset</a>
        @endif
      </form>

      @php $accountants = $accountants ?? collect(); @endphp

      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Name</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Employee ID</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Designation</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Phone</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Joining Date</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Approve Waivers</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Max Waiver</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @forelse($accountants as $a)
              @php
                $user = $a->user ?? null;
                $name = optional($user)->name ?? 'N/A';
                $phone = optional($user)->phone ?? '-';
                $joining = optional($a->joining_date) ? \Carbon\Carbon::parse($a->joining_date)->format('d M Y') : '-';
              @endphp
              <tr>
                <td class="px-4 py-2">
                  <a href="{{ route('accountants.show', $a) }}" class="text-indigo-700 hover:text-indigo-900 font-medium">
                    {{ $name }}
                  </a>
                </td>
                <td class="px-4 py-2">{{ $a->employee_id ?? '-' }}</td>
                <td class="px-4 py-2">{{ $a->designation ?? '-' }}</td>
                <td class="px-4 py-2">{{ $phone }}</td>
                <td class="px-4 py-2">{{ $joining }}</td>
                <td class="px-4 py-2">
                  @if($a->can_approve_waivers ?? false)
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Yes</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">No</span>
                  @endif
                </td>
                <td class="px-4 py-2">
                  {{ ($a->max_waiver_amount !== null) ? '৳ '.number_format((float)$a->max_waiver_amount, 2) : '-' }}
                </td>
                <td class="px-4 py-2">
                  @if(($a->is_active ?? true))
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">Inactive</span>
                  @endif
                </td>
                <td class="px-4 py-2">
                  <div class="flex flex-wrap gap-2">
                    <a href="{{ route('accountants.show', $a) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800">View</a>
                    <a href="{{ route('accountants.edit', $a) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700">Edit</a>
                    <form method="POST" action="{{ route('accountants.destroy', $a) }}" onsubmit="return confirm('Delete this accountant? If they have financial records, consider deactivating instead.');">
                      @csrf
                      @method('DELETE')
                      <button class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td class="px-4 py-4 text-center text-gray-500" colspan="9">No accountants found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($accountants, 'links'))
        <div class="mt-4">{{ $accountants->appends(request()->query())->links() }}</div>
      @endif
    </x-card>

  </div>
</div>
@endsection
