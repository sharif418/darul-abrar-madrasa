@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Accountant Details</h1>
        <div class="flex items-center gap-2">
          <a href="{{ route('accountants.index') }}" class="px-3 py-1.5 border rounded hover:bg-gray-50 text-sm">Back</a>
          <a href="{{ route('accountants.edit', $accountant) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Edit</a>
          <form method="POST" action="{{ route('accountants.destroy', $accountant) }}" onsubmit="return confirm('Delete this accountant? If they have financial records, consider deactivating instead.');">
            @csrf
            @method('DELETE')
            <button class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm">Delete</button>
          </form>
        </div>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      @php
        $user = $accountant->user ?? null;
        $name = optional($user)->name ?? 'N/A';
        $email = optional($user)->email ?? '-';
        $phone = optional($user)->phone ?? '-';
        $joining = optional($accountant->joining_date) ? \Carbon\Carbon::parse($accountant->joining_date)->format('d M Y') : '-';
        $stats = $stats ?? [
          'this_month_collection' => 0,
          'today_collection' => 0,
          'waivers_approved' => 0,
          'years_experience' => 0,
        ];
      @endphp

      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
          <div class="border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Personal Info</div>
            <dl class="mt-3 space-y-2 text-sm">
              <div>
                <dt class="text-gray-500">Name</dt>
                <dd class="font-semibold text-gray-900">{{ $name }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Email</dt>
                <dd class="font-semibold text-gray-900">{{ $email }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Phone</dt>
                <dd class="font-semibold text-gray-900">{{ $phone }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Address</dt>
                <dd class="font-semibold text-gray-900">{{ $accountant->address ?? '-' }}</dd>
              </div>
            </dl>
          </div>

          <div class="mt-4 border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Employment</div>
            <dl class="mt-3 space-y-2 text-sm">
              <div>
                <dt class="text-gray-500">Employee ID</dt>
                <dd class="font-semibold text-gray-900">{{ $accountant->employee_id ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Designation</dt>
                <dd class="font-semibold text-gray-900">{{ $accountant->designation ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Qualification</dt>
                <dd class="font-semibold text-gray-900">{{ $accountant->qualification ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Joining Date</dt>
                <dd class="font-semibold text-gray-900">{{ $joining }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Salary</dt>
                <dd class="font-semibold text-gray-900">৳ {{ number_format((float)($accountant->salary ?? 0), 2) }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="font-semibold text-gray-900">
                  @if(($accountant->is_active ?? true))
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">Inactive</span>
                  @endif
                </dd>
              </div>
            </dl>
          </div>

          <div class="mt-4 border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Permissions</div>
            <dl class="mt-3 space-y-2 text-sm">
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Approve Waivers</dt>
                <dd class="font-semibold text-gray-900">
                  @if($accountant->can_approve_waivers ?? false)
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Yes</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">No</span>
                  @endif
                </dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Approve Refunds</dt>
                <dd class="font-semibold text-gray-900">
                  @if($accountant->can_approve_refunds ?? false)
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Yes</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">No</span>
                  @endif
                </dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Max Waiver Amount</dt>
                <dd class="font-semibold text-gray-900">
                  {{ ($accountant->max_waiver_amount !== null) ? '৳ '.number_format((float)$accountant->max_waiver_amount, 2) : '-' }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <div class="md:col-span-2 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Today's Collection" value="৳ {{ number_format((float)($stats['today_collection'] ?? 0), 2) }}" color="green" />
            <x-stat-card title="This Month" value="৳ {{ number_format((float)($stats['this_month_collection'] ?? 0), 2) }}" color="blue" />
            <x-stat-card title="Waivers Approved" value="{{ (int)($stats['waivers_approved'] ?? 0) }}" color="indigo" />
            <x-stat-card title="Experience (yrs)" value="{{ (int)($stats['years_experience'] ?? 0) }}" color="gray" />
          </div>

          <div class="border rounded p-4 bg-white">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-semibold text-gray-800">Recent Collections</div>
                <div class="text-sm text-gray-600">Last 10 recorded payments</div>
              </div>
              <a href="{{ route('accountant.fees') }}" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">Manage Fees</a>
            </div>
            <div class="mt-3 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Fee Type</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Method</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  @php $recentCollections = $recentCollections ?? collect(); @endphp
                  @forelse($recentCollections as $p)
                    <tr>
                      <td class="px-4 py-2">{{ optional($p->payment_date ?? $p->created_at)->format('d M Y') }}</td>
                      <td class="px-4 py-2">{{ optional(optional($p->student)->user)->name ?? 'N/A' }}</td>
                      <td class="px-4 py-2 capitalize">{{ $p->fee_type ?? '-' }}</td>
                      <td class="px-4 py-2">৳ {{ number_format((float)($p->amount ?? $p->paid_amount ?? 0), 2) }}</td>
                      <td class="px-4 py-2 capitalize">{{ $p->payment_method ?? '-' }}</td>
                      <td class="px-4 py-2">
                        @if(isset($p->fee_id))
                          <a href="{{ route('fees.show', $p->fee_id) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800">View Receipt</a>
                        @else
                          <span class="text-gray-500">N/A</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td class="px-4 py-4 text-center text-gray-500" colspan="6">No recent collections.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div class="border rounded p-4 bg-white">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-semibold text-gray-800">Approved Waivers</div>
                <div class="text-sm text-gray-600">Recently approved waivers</div>
              </div>
              <a href="{{ route('accountant.waivers') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Manage Waivers</a>
            </div>
            <div class="mt-3 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Type</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Amount</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Approved By</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  @php $approvedWaivers = $approvedWaivers ?? collect(); @endphp
                  @forelse($approvedWaivers as $w)
                    @php
                      $amount = (float)($w->amount ?? 0);
                      $amtLabel = $w->amount_type === 'percentage' ? number_format($amount,2).'%' : '৳ '.number_format($amount,2);
                    @endphp
                    <tr>
                      <td class="px-4 py-2">{{ optional($w->approved_at ?? $w->updated_at)->format('d M Y') }}</td>
                      <td class="px-4 py-2">{{ optional(optional($w->student)->user)->name ?? 'ID #'.$w->student_id }}</td>
                      <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $w->waiver_type ?? '-') }}</td>
                      <td class="px-4 py-2">{{ $amtLabel }}</td>
                      <td class="px-4 py-2">{{ optional($w->approvedBy ?? $user)->name ?? 'N/A' }}</td>
                    </tr>
                  @empty
                    <tr><td class="px-4 py-4 text-center text-gray-500" colspan="5">No approved waivers.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </x-card>

  </div>
</div>
@endsection
