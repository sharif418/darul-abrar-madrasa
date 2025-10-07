@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('classes.index') }}" class="hover:text-blue-600">Classes</a>
            <span>/</span>
            <span class="text-gray-900">Class Details</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">{{ $class->name }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('classes.edit', $class) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Edit Class
                </a>
                <a href="{{ route('classes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Class Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3">
                        {{ $class->class_numeric ?? strtoupper(substr($class->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $class->name }}</h2>
                    <p class="text-gray-600 mt-1">{{ $class->department->name }}</p>
                    
                    @if($class->section)
                    <p class="text-sm text-gray-500 mt-1">Section: {{ $class->section }}</p>
                    @endif
                    
                    <div class="mt-3">
                        @if($class->is_active)
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                        @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                        </span>
                        @endif
                    </div>
                </div>

                <div class="border-t pt-4 mt-4">
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Department</label>
                            <p class="text-gray-900">{{ $class->department->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Capacity</label>
                            <p class="text-gray-900">{{ $class->capacity }} Students</p>
                        </div>
                        @if($class->description)
                        <div>
                            <label class="text-sm text-gray-600">Description</label>
                            <p class="text-gray-900">{{ $class->description }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-gray-600">Created</label>
                            <p class="text-gray-900">{{ $class->created_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Enrolled Students</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $class->students_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Available Seats</span>
                        <span class="text-2xl font-bold text-green-600">{{ $class->capacity - $class->students_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Subjects</span>
                        <span class="text-2xl font-bold text-purple-600">{{ $class->subjects_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Exams</span>
                        <span class="text-2xl font-bold text-orange-600">{{ $class->exams_count }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('students.index', ['class_id' => $class->id]) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Bulk Enroll Students
                    </a>
                    <a href="{{ route('attendances.index', ['class_id' => $class->id]) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        View Attendance
                    </a>
                    <a href="{{ route('results.index', ['class_id' => $class->id]) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        View Results
                    </a>
                </div>
            </div>
        </div>

        <!-- Students, Subjects, Exams -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Students Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Students ({{ $class->students->count() }})</h3>
                    @if(auth()->user()->role === 'admin')
                        <x-dropdown-menu align="right" width="sm">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    + Enroll Student
                                    <svg class="ml-2 h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <a href="{{ route('classes.enroll-student.form', $class) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Enroll Existing Student</a>
                            <a href="{{ route('students.create', ['class_id' => $class->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Register New Student</a>
                        </x-dropdown-menu>
                    @endif
                </div>

                @if($class->students->count() > 0)
                <div class="space-y-2">
                    @foreach($class->students->take(10) as $student)
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition">
                        <div class="flex items-center gap-3">
                            @if($student->user->avatar)
                            <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $student->user->avatar) }}" alt="{{ $student->user->name }}">
                            @else
                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($student->user->name, 0, 1)) }}
                            </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $student->user->name }}</p>
                                <p class="text-sm text-gray-600">ID: {{ $student->student_id }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('students.show', $student) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <div>
                                    <button type="button" class="text-red-600 hover:text-red-800" title="Unenroll" @click.prevent="window.dispatchEvent(new CustomEvent('open-unenroll-{{ $student->id }}'))">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                        </svg>
                                    </button>
                                    <x-confirm-delete-modal
                                        openEvent="open-unenroll-{{ $student->id }}"
                                        title="Unenroll Student"
                                        message="Are you sure you want to unenroll this student from the class?"
                                        confirmText="Unenroll"
                                        cancelText="Cancel"
                                        confirmButtonColor="red"
                                        formAction="{{ route('classes.unenroll-student', [$class, $student]) }}"
                                        formMethod="DELETE"
                                    />
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    
                    @if($class->students->count() > 10)
                    <div class="text-center pt-2">
                        <a href="{{ route('students.index', ['class_id' => $class->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            View all {{ $class->students->count() }} students →
                        </a>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <p class="mt-2">No students enrolled yet</p>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('students.create', ['class_id' => $class->id]) }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                        Enroll first student
                    </a>
                    @endif
                </div>
                @endif
            </div>

            <!-- Subjects Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Subjects ({{ $class->subjects->count() }})</h3>
                    @if(auth()->user()->role === 'admin')
                        <x-dropdown-menu align="right" width="sm">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    + Add Subject
                                    <svg class="ml-2 h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <a href="{{ route('classes.assign-subject.form', $class) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Assign Existing Subject</a>
                            <a href="{{ route('subjects.create', ['class_id' => $class->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Create New Subject</a>
                        </x-dropdown-menu>
                    @endif
                </div>

                @if($class->subjects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($class->subjects as $subject)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-900">{{ $subject->name }}</h4>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                    {{ $subject->code }}
                                </span>
                                @if(auth()->user()->role === 'admin')
                                    <div>
                                        <button type="button" class="text-red-600 hover:text-red-800" title="Unassign Subject" @click.prevent="window.dispatchEvent(new CustomEvent('open-unassign-{{ $subject->id }}'))">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                            </svg>
                                        </button>
                                        <x-confirm-delete-modal
                                            openEvent="open-unassign-{{ $subject->id }}"
                                            title="Unassign Subject"
                                            message="Are you sure you want to unassign this subject from the class?"
                                            confirmText="Unassign"
                                            cancelText="Cancel"
                                            confirmButtonColor="red"
                                            formAction="{{ route('classes.unassign-subject', [$class, $subject]) }}"
                                            formMethod="DELETE"
                                        />
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($subject->teacher)
                        <p class="text-sm text-gray-600">Teacher: {{ $subject->teacher->user->name }}</p>
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('subjects.show', $subject) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                View Details →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="mt-2">No subjects assigned yet</p>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('subjects.create', ['class_id' => $class->id]) }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                        Add first subject
                    </a>
                    @endif
                </div>
                @endif
            </div>

            <!-- Recent Exams Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Exams</h3>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('exams.create', ['class_id' => $class->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        + Create Exam
                    </a>
                    @endif
                </div>

                @if($recentExams->count() > 0)
                <div class="space-y-3">
                    @foreach($recentExams as $exam)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $exam->name }}</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $exam->start_date->format('M d') }} - {{ $exam->end_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                @if($exam->is_result_published)
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Published</span>
                                @else
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('exams.show', $exam) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                View Details →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-2">No exams scheduled yet</p>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('exams.create', ['class_id' => $class->id]) }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                        Create first exam
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
