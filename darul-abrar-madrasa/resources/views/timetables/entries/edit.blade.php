@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm mb-4">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('timetables.index') }}" class="text-blue-600 hover:text-blue-800">Timetables</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
            </li>
            <li class="flex items-center">
                <a href="{{ route('timetables.show', $timetable) }}" class="text-blue-600 hover:text-blue-800">{{ $timetable->name }}</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
            </li>
            <li class="flex items-center">
                <a href="{{ route('timetables.entries', $timetable) }}" class="text-blue-600 hover:text-blue-800">Entries</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
            </li>
            <li class="text-gray-500">Edit Entry</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Timetable Entry</h1>
        <a href="{{ route('timetables.entries', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">Validation Errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('timetables.entries.update', [$timetable, $entry]) }}" method="POST" id="entryForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="timetable_id" value="{{ $timetable->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Day of Week -->
                <div>
                    <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-2">Day of Week <span class="text-red-500">*</span></label>
                    <select name="day_of_week" id="day_of_week" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Day</option>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <option value="{{ $day }}" {{ old('day_of_week', $entry->day_of_week) == $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                    @error('day_of_week')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Period -->
                <div>
                    <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Period <span class="text-red-500">*</span></label>
                    <select name="period_id" id="period_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Period</option>
                    </select>
                    @error('period_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Class -->
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class <span class="text-red-500">*</span></label>
                    <select name="class_id" id="class_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $entry->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                    <select name="subject_id" id="subject_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Subject</option>
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teacher -->
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-2">Teacher</label>
                    <select name="teacher_id" id="teacher_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Teacher (Optional)</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $entry->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Room Number -->
                <div>
                    <label for="room_number" class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                    <input type="text" name="room_number" id="room_number" value="{{ old('room_number', $entry->room_number) }}" placeholder="e.g., Room 101" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('room_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Any special instructions or notes" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $entry->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $entry->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-save mr-2"></i>Update Entry
                </button>
                <a href="{{ route('timetables.entries', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const daySelect = document.getElementById('day_of_week');
    const periodSelect = document.getElementById('period_id');
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const teacherSelect = document.getElementById('teacher_id');

    const periodsByDay = @json($periods);
    const allSubjects = @json($subjects);
    const currentPeriodId = {{ old('period_id', $entry->period_id) }};
    const currentSubjectId = {{ old('subject_id', $entry->subject_id) }};

    // Filter periods when day changes
    daySelect.addEventListener('change', function() {
        const selectedDay = this.value;
        periodSelect.innerHTML = '<option value="">Select Period</option>';
        
        if (selectedDay && periodsByDay[selectedDay]) {
            periodsByDay[selectedDay].forEach(period => {
                const option = document.createElement('option');
                option.value = period.id;
                option.textContent = `${period.name} (${period.start_time} - ${period.end_time})`;
                if (period.id == currentPeriodId) option.selected = true;
                periodSelect.appendChild(option);
            });
        }
    });

    // Filter subjects when class changes
    classSelect.addEventListener('change', function() {
        const selectedClass = this.value;
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        
        if (selectedClass) {
            const classSubjects = allSubjects.filter(s => s.class_id == selectedClass);
            classSubjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                option.dataset.teacherId = subject.teacher_id;
                if (subject.id == currentSubjectId) option.selected = true;
                subjectSelect.appendChild(option);
            });
        }
    });

    // Auto-select teacher when subject changes
    subjectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.teacherId) {
            teacherSelect.value = selectedOption.dataset.teacherId;
        }
    });

    // Initialize on load
    if (daySelect.value) {
        daySelect.dispatchEvent(new Event('change'));
    }
    if (classSelect.value) {
        classSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
