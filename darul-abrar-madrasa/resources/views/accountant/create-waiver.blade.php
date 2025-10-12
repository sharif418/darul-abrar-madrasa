@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Waiver</h1>

      @if(session('success'))
        <x-alert type="success" :message="session('success')" />
      @endif
      @if(session('error'))
        <x-alert type="error" :message="session('error')" />
      @endif

      <form method="POST" action="{{ route('accountant.waivers.store') }}" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Student</label>
            <input name="student_id" type="number" value="{{ old('student_id') }}" placeholder="Student ID" class="w-full border rounded px-3 py-2" required />
            @error('student_id')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Fee (optional)</label>
            <input name="fee_id" type="number" value="{{ old('fee_id') }}" placeholder="Fee ID" class="w-full border rounded px-3 py-2" />
            @error('fee_id')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Waiver Type</label>
            <select name="waiver_type" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Type</option>
              <option value="scholarship" @selected(old('waiver_type')==='scholarship')>Scholarship</option>
              <option value="financial_aid" @selected(old('waiver_type')==='financial_aid')>Financial Aid</option>
              <option value="merit" @selected(old('waiver_type')==='merit')>Merit</option>
              <option value="sibling_discount" @selected(old('waiver_type')==='sibling_discount')>Sibling Discount</option>
              <option value="staff_child" @selected(old('waiver_type')==='staff_child')>Staff Child</option>
              <option value="other" @selected(old('waiver_type')==='other')>Other</option>
            </select>
            @error('waiver_type')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Amount Type</label>
            <select name="amount_type" class="w-full border rounded px-3 py-2" required x-data x-init="$watch('amount_type', v => {})">
              <option value="percentage" @selected(old('amount_type')==='percentage')>Percentage</option>
              <option value="fixed" @selected(old('amount_type')==='fixed')>Fixed</option>
            </select>
            @error('amount_type')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Amount</label>
            <input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full border rounded px-3 py-2" required />
            @error('amount')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Valid From</label>
            <input name="valid_from" type="date" value="{{ old('valid_from', now()->toDateString()) }}" class="w-full border rounded px-3 py-2" required />
            @error('valid_from')<x-input-error :messages="$message" />@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Valid Until (optional)</label>
            <input name="valid_until" type="date" value="{{ old('valid_until') }}" class="w-full border rounded px-3 py-2" />
            @error('valid_until')<x-input-error :messages="$message" />@enderror
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Reason</label>
          <textarea name="reason" rows="4" class="w-full border rounded px-3 py-2" placeholder="Provide reason for the waiver" required>{{ old('reason') }}</textarea>
          @error('reason')<x-input-error :messages="$message" />@enderror
        </div>

        <div class="flex items-center gap-3">
          <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create Waiver</button>
          <a href="{{ route('accountant.waivers') }}" class="px-4 py-2 bg-gray-100 border rounded hover:bg-gray-200">Back</a>
        </div>
      </form>
    </x-card>
  </div>
</div>
@endsection
