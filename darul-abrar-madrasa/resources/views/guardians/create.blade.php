@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Create Guardian</h1>
        <a href="{{ route('guardians.index') }}" class="px-3 py-1.5 border rounded hover:bg-gray-50 text-sm">Back</a>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <form method="POST" action="{{ route('guardians.store') }}" class="mt-6 space-y-8">
        @csrf

        <div>
          <h2 class="text-lg font-semibold text-gray-800">User Information</h2>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <x-label for="name" value="Name" />
              <x-input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-1 block w-full" required />
              @error('name')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="email" value="Email" />
              <x-input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full" required />
              @error('email')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="phone" value="Phone" />
              <x-input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-1 block w-full" required />
              @error('phone')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="password" value="Password" />
              <x-input id="password" name="password" type="password" class="mt-1 block w-full" required />
              @error('password')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="password_confirmation" value="Confirm Password" />
              <x-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-lg font-semibold text-gray-800">Guardian Details</h2>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <x-label for="national_id" value="National ID" />
              <x-input id="national_id" name="national_id" type="text" value="{{ old('national_id') }}" class="mt-1 block w-full" />
              @error('national_id')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="occupation" value="Occupation" />
              <x-input id="occupation" name="occupation" type="text" value="{{ old('occupation') }}" class="mt-1 block w-full" />
              @error('occupation')<x-input-error :messages="$message" />@enderror
            </div>
            <div class="sm:col-span-2">
              <x-label for="address" value="Address" />
              <textarea id="address" name="address" rows="3" class="mt-1 block w-full border rounded px-3 py-2">{{ old('address') }}</textarea>
              @error('address')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="alternative_phone" value="Alternative Phone" />
              <x-input id="alternative_phone" name="alternative_phone" type="text" value="{{ old('alternative_phone') }}" class="mt-1 block w-full" />
              @error('alternative_phone')<x-input-error :messages="$message" />@enderror
            </div>
            <div>
              <x-label for="relationship_type" value="Relationship Type" />
              <select id="relationship_type" name="relationship_type" class="mt-1 block w-full border rounded px-3 py-2" required>
                <option value="">Select</option>
                <option value="father" @selected(old('relationship_type')==='father')>Father</option>
                <option value="mother" @selected(old('relationship_type')==='mother')>Mother</option>
                <option value="legal_guardian" @selected(old('relationship_type')==='legal_guardian')>Legal Guardian</option>
                <option value="other" @selected(old('relationship_type')==='other')>Other</option>
              </select>
              @error('relationship_type')<x-input-error :messages="$message" />@enderror
            </div>
            <div class="flex items-center gap-2">
              <input id="is_primary_contact" name="is_primary_contact" type="checkbox" value="1" @checked(old('is_primary_contact', true)) class="rounded border-gray-300">
              <label for="is_primary_contact" class="text-sm text-gray-700">Is Primary Contact</label>
            </div>
            <div class="flex items-center gap-2">
              <input id="emergency_contact" name="emergency_contact" type="checkbox" value="1" @checked(old('emergency_contact', false)) class="rounded border-gray-300">
              <label for="emergency_contact" class="text-sm text-gray-700">Emergency Contact</label>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-lg font-semibold text-gray-800">Link to Students (optional)</h2>
          <p class="text-sm text-gray-600">Link this guardian to existing students. You may select multiple.</p>

          @php $studentsList = $students ?? collect(); @endphp
          @if($studentsList->count() > 0)
            <div class="mt-3">
              <x-label for="student_ids" value="Students" />
              <select id="student_ids" name="student_ids[]" class="mt-1 block w-full border rounded px-3 py-2" multiple>
                @foreach($studentsList as $s)
                  <option value="{{ $s->id }}" @selected(collect(old('student_ids', []))->contains($s->id))>
                    {{ optional($s->user)->name ?? 'Student #'.$s->id }} - {{ optional($s->class)->name ?? 'Class N/A' }}
                  </option>
                @endforeach
              </select>
              @error('student_ids')<x-input-error :messages="$message" />@enderror
              @error('student_ids.*')<x-input-error :messages="$message" />@enderror
            </div>
          @else
            <div class="mt-3">
              <x-label for="student_ids_input" value="Student IDs (comma separated)" />
              <x-input id="student_ids_input" name="student_ids_input" type="text" value="{{ old('student_ids_input') }}" class="mt-1 block w-full" placeholder="e.g., 12, 34, 56" />
              <p class="text-xs text-gray-500 mt-1">Controller may parse this into student_ids[].</p>
            </div>
          @endif
        </div>

        <div class="flex items-center gap-3">
          <x-button>Save Guardian</x-button>
          <a href="{{ route('guardians.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</a>
        </div>
      </form>
    </x-card>
  </div>
</div>
@endsection
