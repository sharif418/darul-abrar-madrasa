@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Guardians</h1>
        <a href="{{ route('guardians.create') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
          + Create Guardian
        </a>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Search name/email/phone" class="border rounded px-3 py-2" />
        <select name="status" class="border rounded px-3 py-2">
          <option value="">Any Status</option>
          <option value="active" @selected(request('status')==='active')>Active</option>
          <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
        </select>
        <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
        @if(request()->hasAny(['q','status']))
          <a href="{{ route('guardians.index') }}" class="px-3 py-2 border rounded hover:bg-gray-50">Reset</a>
        @endif
      </form>

      @php $guardians = $guardians ?? collect(); @endphp

      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Name</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Phone</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Email</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Children</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Total Pending Fees</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @forelse($guardians as $g)
              @php
                $user = $g->user ?? null;
                $name = optional($user)->name ?? 'N/A';
                $email = optional($user)->email ?? ($g->email ?? '-');
                $phone = $g->phone ?? (optional($user)->phone ?? '-');
                $childrenCount = method_exists($g, 'students') ? $g->students()->count() : ($g->students_count ?? 0);
                $pending = method_exists($g, 'getTotalPendingFeesAttribute') ? ($g->total_pending_fees ?? 0) : ($g->total_pending_fees ?? 0);
              @endphp
              <tr>
                <td class="px-4 py-2">
                  <a href="{{ route('guardians.show', $g) }}" class="text-indigo-700 hover:text-indigo-900 font-medium">
                    {{ $name }}
                  </a>
                </td>
                <td class="px-4 py-2">{{ $phone }}</td>
                <td class="px-4 py-2">{{ $email }}</td>
                <td class="px-4 py-2">{{ $childrenCount }}</td>
                <td class="px-4 py-2">৳ {{ number_format((float)$pending, 2) }}</td>
                <td class="px-4 py-2">
                  @if(($g->is_active ?? true))
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">Inactive</span>
                  @endif
                </td>
                <td class="px-4 py-2">
                  <div class="flex flex-wrap gap-2">
                    <a href="{{ route('guardians.show', $g) }}" class="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800">View</a>
                    <a href="{{ route('guardians.edit', $g) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700">Edit</a>
                    <form method="POST" action="{{ route('guardians.destroy', $g) }}" onsubmit="return confirm('Delete this guardian? This action cannot be undone.');">
                      @csrf
                      @method('DELETE')
                      <button class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td class="px-4 py-4 text-center text-gray-500" colspan="7">No guardians found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($guardians, 'links'))
        <div class="mt-4">{{ $guardians->appends(request()->query())->links() }}</div>
      @endif
    </x-card>
  </div>
</div>
@endsection
