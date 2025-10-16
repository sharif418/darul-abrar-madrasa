@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Guardian Details</h1>
        <div class="flex items-center gap-2">
          <a href="{{ route('guardians.index') }}" class="px-3 py-1.5 border rounded hover:bg-gray-50 text-sm">Back</a>
          <a href="{{ route('guardians.edit', $guardian) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Edit</a>
          <form method="POST" action="{{ route('guardians.destroy', $guardian) }}" onsubmit="return confirm('Delete this guardian? This action cannot be undone.');">
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
        $user = $guardian->user ?? null;
        $name = optional($user)->name ?? 'N/A';
        $email = optional($user)->email ?? ($guardian->email ?? '-');
        $phone = $guardian->phone ?? (optional($user)->phone ?? '-');
      @endphp

      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
          <div class="border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Guardian Info</div>
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
                <dt class="text-gray-500">National ID</dt>
                <dd class="font-semibold text-gray-900">{{ $guardian->national_id ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Occupation</dt>
                <dd class="font-semibold text-gray-900">{{ $guardian->occupation ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Relationship</dt>
                <dd class="font-semibold text-gray-900 capitalize">{{ $guardian->relationship_type ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Primary Contact</dt>
                <dd class="font-semibold text-gray-900">{{ ($guardian->is_primary_contact ?? false) ? 'Yes' : 'No' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Emergency Contact</dt>
                <dd class="font-semibold text-gray-900">{{ ($guardian->emergency_contact ?? false) ? 'Yes' : 'No' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="font-semibold text-gray-900">
                  @if(($guardian->is_active ?? true))
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">Inactive</span>
                  @endif
                </dd>
              </div>
            </dl>
          </div>
          <div class="mt-4 border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800">Contact Address</div>
            <div class="mt-2 text-sm text-gray-800">{{ $guardian->address ?? '-' }}</div>
            <div class="mt-2 text-sm text-gray-600">Alt. Phone: {{ $guardian->alternative_phone ?? '-' }}</div>
          </div>
        </div>

        <div class="md:col-span-2 space-y-6">
          <div class="border rounded p-4 bg-white">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-semibold text-gray-800">Linked Students</div>
                <div class="text-sm text-gray-600">Manage relationships and permissions</div>
              </div>
              <form method="POST" action="{{ route('guardians.link-student', $guardian) }}" class="flex items-center gap-2">
                @csrf
                <input name="student_id" type="number" placeholder="Student ID" class="border rounded px-2 py-1 text-sm" required />
                <select name="relationship" class="border rounded px-2 py-1 text-sm" required>
                  <option value="father">Father</option>
                  <option value="mother">Mother</option>
                  <option value="legal_guardian">Legal Guardian</option>
                  <option value="sibling">Sibling</option>
                  <option value="other">Other</option>
                </select>
                <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                  <input type="checkbox" name="is_primary_guardian" value="1" class="rounded border-gray-300" />
                  Primary
                </label>
                <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                  <input type="checkbox" name="financial_responsibility" value="1" class="rounded border-gray-300" />
                  Financial
                </label>
                <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                  <input type="checkbox" name="receive_notifications" value="1" class="rounded border-gray-300" checked />
                  Notify
                </label>
                <button class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Link</button>
              </form>
            </div>

            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Class</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Relationship</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Primary</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Financial</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Notify</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  @forelse(($guardian->students ?? []) as $s)
                    <tr>
                      <td class="px-4 py-2">{{ optional($s->user)->name ?? 'Student #'.$s->id }}</td>
                      <td class="px-4 py-2">{{ optional($s->class)->name ?? 'N/A' }}</td>
                      <td class="px-4 py-2 capitalize">{{ $s->pivot->relationship ?? '-' }}</td>
                      <td class="px-4 py-2">{{ ($s->pivot->is_primary_guardian ?? false) ? 'Yes' : 'No' }}</td>
                      <td class="px-4 py-2">{{ ($s->pivot->financial_responsibility ?? false) ? 'Yes' : 'No' }}</td>
                      <td class="px-4 py-2">{{ ($s->pivot->receive_notifications ?? false) ? 'Yes' : 'No' }}</td>
                      <td class="px-4 py-2">
                        <form method="POST" action="{{ route('guardians.unlink-student', [$guardian, $s]) }}" onsubmit="return confirm('Unlink this student?');">
                          @csrf
                          @method('DELETE')
                          <button class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">Unlink</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td class="px-4 py-4 text-center text-gray-500" colspan="7">No linked students.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card title="Children" value="{{ method_exists($guardian,'students') ? $guardian->students()->count() : ($guardian->students_count ?? 0) }}" color="indigo" />
            <x-stat-card title="Total Pending Fees" value="৳ {{ number_format((float)($totalPendingFees ?? ($guardian->total_pending_fees ?? 0)), 2) }}" color="yellow" />
            <x-stat-card title="Active" value="{{ ($guardian->is_active ?? true) ? 'Yes' : 'No' }}" color="{{ ($guardian->is_active ?? true) ? 'green' : 'gray' }}" />
          </div>

          <div class="border rounded p-4 bg-white">
            <div class="text-lg font-semibold text-gray-800 mb-2">Recent Fee Payments</div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Date</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Student</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Fee Type</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Amount</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Method</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  @forelse(($recentPayments ?? []) as $p)
                    <tr>
                      <td class="px-4 py-2">{{ optional($p->payment_date)->format('d M Y') ?? optional($p->updated_at)->format('d M Y') ?? '-' }}</td>
                      <td class="px-4 py-2">{{ optional(optional($p->student)->user)->name ?? 'N/A' }}</td>
                      <td class="px-4 py-2 capitalize">{{ $p->fee_type ?? '-' }}</td>
                      <td class="px-4 py-2">৳ {{ number_format((float)($p->amount ?? $p->paid_amount ?? 0), 2) }}</td>
                      <td class="px-4 py-2">{{ $p->payment_method ?? '-' }}</td>
                    </tr>
                  @empty
                    <tr><td class="px-4 py-4 text-center text-gray-500" colspan="5">No recent payments.</td></tr>
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
