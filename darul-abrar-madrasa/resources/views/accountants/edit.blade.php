@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Edit Accountant</h1>
        <div class="flex items-center gap-2">
          <a href="{{ route('accountants.show', $accountant) }}" class="px-3 py-1.5 border rounded hover:bg-gray-50 text-sm">Back</a>
        </div>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <form method="POST" action="{{ route('accountants.update', $accountant) }}" class="mt-6 space-y-8">
        @csrf
        @method('PUT')

        @php
          $user = $accountant->user ?? null;
        @endphp

        <div>
          <h2 class="text-lg font-semibold text-gray-800">User Information</h2>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <x-label for="name" value="Name" />
              <x-input id="name" name="name" type="text" value="{{ old('name', optional($user)->name) }}" class="mt-1 block w-full" required />
              @error('name')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="email" value="Email" />
              <x-input id="email" name="email" type="email" value="{{ old('email', optional($user)->email) }}" class="mt-1 block w-full" required />
              @error('email')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="phone" value="Phone" />
              <x-input id="phone" name="phone" type="text" value="{{ old('phone', optional($user)->phone) }}" class="mt-1 block w-full" required />
              @error('phone')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="password" value="Password (leave blank to keep current)" />
              <x-input id="password" name="password" type="password" class="mt-1 block w-full" />
              @error('password')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="password_confirmation" value="Confirm Password" />
              <x-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-lg font-semibold text-gray-800">Accountant Details</h2>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <x-label for="employee_id" value="Employee ID" />
              <x-input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id', $accountant->employee_id) }}" class="mt-1 block w-full" required />
              @error('employee_id')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="designation" value="Designation" />
              <x-input id="designation" name="designation" type="text" value="{{ old('designation', $accountant->designation) }}" class="mt-1 block w-full" required />
              @error('designation')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="qualification" value="Qualification" />
              <x-input id="qualification" name="qualification" type="text" value="{{ old('qualification', $accountant->qualification) }}" class="mt-1 block w-full" />
              @error('qualification')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="joining_date" value="Joining Date" />
              <x-input id="joining_date" name="joining_date" type="date" value="{{ old('joining_date', optional($accountant->joining_date) ? \Carbon\Carbon::parse($accountant->joining_date)->toDateString() : '') }}" class="mt-1 block w-full" required />
              @error('joining_date')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="salary" value="Salary" />
              <x-input id="salary" name="salary" type="number" step="0.01" min="0" value="{{ old('salary', $accountant->salary) }}" class="mt-1 block w-full" required />
              @error('salary')<x-input-error :messages="$message" />@enderror
            </div>
            <div class="sm:col-span-2">
              <x-label for="address" value="Address" />
              <textarea id="address" name="address" rows="3" class="mt-1 block w-full border rounded px-3 py-2">{{ old('address', $accountant->address) }}</textarea>
              @error('address')<x-input-error :messages="$message" />@enderror
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-lg font-semibold text-gray-800">Permissions</h2>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
              <input id="can_approve_waivers" name="can_approve_waivers" type="checkbox" value="1" @checked(old('can_approve_waivers', $accountant->can_approve_waivers)) class="rounded border-gray-300">
              <label for="can_approve_waivers" class="text-sm text-gray-700">Can Approve Waivers</label>
            </div>
            <div class="flex items-center gap-2">
              <input id="can_approve_refunds" name="can_approve_refunds" type="checkbox" value="1" @checked(old('can_approve_refunds', $accountant->can_approve_refunds)) class="rounded border-gray-300">
              <label for="can_approve_refunds" class="text-sm text-gray-700">Can Approve Refunds</label>
            </div>
            <div id="max_waiver_container" class="sm:col-span-2" style="display: none;">
              <x-label for="max_waiver_amount" value="Maximum Waiver Amount (৳)" />
              <x-input id="max_waiver_amount" name="max_waiver_amount" type="number" step="0.01" min="0" value="{{ old('max_waiver_amount', $accountant->max_waiver_amount) }}" class="mt-1 block w-full" />
              @error('max_waiver_amount')<x-input-error :messages="$message" />@enderror
            </div>
            <div class="flex items-center gap-2">
              <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $accountant->is_active)) class="rounded border-gray-300">
              <label for="is_active" class="text-sm text-gray-700">Active</label>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <x-button>Update Accountant</x-button>
          <a href="{{ route('accountants.show', $accountant) }}" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</a>
        </div>
      </form>
    </x-card>
  </div>
</div>

<script>
(function() {
  const checkbox = document.getElementById('can_approve_waivers');
  const container = document.getElementById('max_waiver_container');
  const input = document.getElementById('max_waiver_amount');

  function toggleMaxWaiver() {
    if (checkbox.checked) {
      container.style.display = 'block';
      input.required = true;
    } else {
      container.style.display = 'none';
      input.required = false;
    }
  }

  checkbox.addEventListener('change', toggleMaxWaiver);
  document.addEventListener('DOMContentLoaded', toggleMaxWaiver);
  toggleMaxWaiver();
})();
</script>
@endsection
