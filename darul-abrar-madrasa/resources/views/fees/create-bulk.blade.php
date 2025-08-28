@extends('layouts.app')

@section('header', 'Bulk Create Fees')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Bulk Create Fees</h1>
        <x-button href="{{ route('fees.index') }}" color="secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Fees
        </x-button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('fees.store-bulk') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <x-label for="fee_type" value="Fee Type" />
                    <x-select id="fee_type" name="fee_type" class="block mt-1 w-full" required>
                        <option value="">Select Fee Type</option>
                        @foreach($feeTypes as $type)
                            <option value="{{ $type }}" {{ old('fee_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="fee_type" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="amount" value="Amount" />
                    <x-input id="amount" type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" class="block mt-1 w-full" required />
                    <x-input-error for="amount" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="due_date" value="Due Date" />
                    <x-input id="due_date" type="date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" class="block mt-1 w-full" required />
                    <x-input-error for="due_date" class="mt-2" />
                </div>
                
                <div class="md:col-span-2">
                    <x-label for="remarks" value="Remarks (Optional)" />
                    <x-textarea id="remarks" name="remarks" class="block mt-1 w-full" rows="2">{{ old('remarks') }}</x-textarea>
                    <x-input-error for="remarks" class="mt-2" />
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Students</h2>
                
                <div class="mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="select_all" class="form-checkbox h-5 w-5 text-blue-600">
                        <label for="select_all" class="ml-2 text-gray-700 font-medium">Select/Deselect All</label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($students->groupBy('class.name') as $className => $classStudents)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-700 mb-3">{{ $className }}</h3>
                            
                            @foreach($classStudents as $student)
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="student_{{ $student->id }}" name="student_ids[]" value="{{ $student->id }}" class="form-checkbox h-5 w-5 text-blue-600 student-checkbox">
                                    <label for="student_{{ $student->id }}" class="ml-2 text-gray-700">
                                        {{ $student->name }} ({{ $student->student_id }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                
                @error('student_ids')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex justify-end mt-6">
                <x-button type="submit" color="primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Fees
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select_all');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        
        // Select/deselect all
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            studentCheckboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
        });
        
        // Update select all checkbox when individual checkboxes change
        studentCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(studentCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(studentCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    });
</script>
@endpush
@endsection