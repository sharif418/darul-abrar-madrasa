@extends('layouts.app')

@section('header', 'Create Lesson Plan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Create Lesson Plan</h1>
        <x-button href="{{ route('lesson-plans.index') }}" color="secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Lesson Plans
        </x-button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Lesson Plan Information</h2>
            <p class="text-gray-600 mt-1">Create a new lesson plan for your class.</p>
        </div>

        <form action="{{ route('lesson-plans.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(Auth::user()->isAdmin())
                    <div>
                        <x-label for="teacher_id" value="Teacher" />
                        <x-select id="teacher_id" name="teacher_id" class="block mt-1 w-full" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error for="teacher_id" class="mt-2" />
                    </div>
                @else
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                    <div>
                        <x-label value="Teacher" />
                        <div class="mt-1 p-2 bg-gray-100 rounded-md">
                            {{ $teacher->user->name }}
                        </div>
                    </div>
                @endif

                <div>
                    <x-label for="class_id" value="Class" />
                    <x-select id="class_id" name="class_id" class="block mt-1 w-full" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="class_id" class="mt-2" />
                </div>

                <div>
                    <x-label for="subject_id" value="Subject" />
                    <x-select id="subject_id" name="subject_id" class="block mt-1 w-full" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}
                                data-class="{{ $subject->class_id }}" data-teacher="{{ $subject->teacher_id }}">
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="subject_id" class="mt-2" />
                </div>

                <div>
                    <x-label for="plan_date" value="Lesson Date" />
                    <x-input id="plan_date" type="date" name="plan_date" value="{{ old('plan_date', date('Y-m-d')) }}" class="block mt-1 w-full" required />
                    <x-input-error for="plan_date" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="title" value="Lesson Title" />
                    <x-input id="title" type="text" name="title" value="{{ old('title') }}" class="block mt-1 w-full" required />
                    <x-input-error for="title" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="description" value="Lesson Description" />
                    <x-textarea id="description" name="description" class="block mt-1 w-full" rows="6" required>{{ old('description') }}</x-textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>

                <div>
                    <x-label for="status" value="Status" />
                    <x-select id="status" name="status" class="block mt-1 w-full" required>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </x-select>
                    <x-input-error for="status" class="mt-2" />
                </div>

                <div id="completion_notes_container" class="{{ old('status') == 'completed' ? '' : 'hidden' }}">
                    <x-label for="completion_notes" value="Completion Notes" />
                    <x-textarea id="completion_notes" name="completion_notes" class="block mt-1 w-full" rows="3">{{ old('completion_notes') }}</x-textarea>
                    <x-input-error for="completion_notes" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <x-button type="submit" color="primary">
                    Create Lesson Plan
                </x-button>
            </div>
        </form>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Lesson Plan Guidelines</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Creating Effective Lesson Plans</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Define clear learning objectives</li>
                        <li>Include teaching methods and activities</li>
                        <li>Specify required materials and resources</li>
                        <li>Plan for assessment and evaluation</li>
                        <li>Include time allocations for different activities</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Best Practices</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Create lesson plans at least one week in advance</li>
                        <li>Align lessons with curriculum objectives</li>
                        <li>Consider different learning styles</li>
                        <li>Include differentiation strategies for diverse learners</li>
                        <li>Update completion notes after teaching the lesson</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        const subjectSelect = document.getElementById('subject_id');
        const teacherSelect = document.getElementById('teacher_id');
        const statusSelect = document.getElementById('status');
        const completionNotesContainer = document.getElementById('completion_notes_container');
        
        // Filter subjects based on selected class and teacher
        function filterSubjects() {
            const selectedClassId = classSelect.value;
            const selectedTeacherId = teacherSelect ? teacherSelect.value : '{{ Auth::user()->isTeacher() ? Auth::user()->teacher->id : "" }}';
            
            // Hide all options first
            Array.from(subjectSelect.options).forEach(option => {
                if (option.value === '') return; // Skip the placeholder option
                
                const subjectClassId = option.getAttribute('data-class');
                const subjectTeacherId = option.getAttribute('data-teacher');
                
                // Show option only if it matches both class and teacher (if selected)
                const matchesClass = !selectedClassId || subjectClassId === selectedClassId;
                const matchesTeacher = !selectedTeacherId || subjectTeacherId === selectedTeacherId;
                
                option.hidden = !(matchesClass && matchesTeacher);
            });
            
            // If the currently selected option is now hidden, reset the selection
            if (subjectSelect.selectedOptions.length > 0 && subjectSelect.selectedOptions[0].hidden) {
                subjectSelect.value = '';
            }
        }
        
        // Show/hide completion notes based on status
        function toggleCompletionNotes() {
            if (statusSelect.value === 'completed') {
                completionNotesContainer.classList.remove('hidden');
            } else {
                completionNotesContainer.classList.add('hidden');
            }
        }
        
        // Initial filtering
        if (classSelect && subjectSelect) {
            filterSubjects();
            
            // Add event listeners
            classSelect.addEventListener('change', filterSubjects);
            if (teacherSelect) {
                teacherSelect.addEventListener('change', filterSubjects);
            }
        }
        
        // Status change event
        if (statusSelect && completionNotesContainer) {
            statusSelect.addEventListener('change', toggleCompletionNotes);
        }
    });
</script>
@endpush
@endsection