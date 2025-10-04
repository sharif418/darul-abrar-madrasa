@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('departments.index') }}" class="hover:text-blue-600">Departments</a>
            <span>/</span>
            <span class="text-gray-900">Department Details</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">{{ $department->name }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('departments.edit', $department) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Edit Department
                </a>
                <a href="{{ route('departments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Department Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr($department->code, 0, 2)) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $department->name }}</h2>
                    <p class="text-gray-600 mt-1">{{ $department->code }}</p>
                    
                    <div class="mt-3">
                        @if($department->is_active)
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
                            <label class="text-sm text-gray-600">Description</label>
                            <p class="text-gray-900">{{ $department->description ?? 'No description provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Created</label>
                            <p class="text-gray-900">{{ $department->created_at->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Last Updated</label>
                            <p class="text-gray-900">{{ $department->updated_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Classes</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $department->classes_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Teachers</span>
                        <span class="text-2xl font-bold text-green-600">{{ $department->teachers_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Students</span>
                        <span class="text-2xl font-bold text-purple-600">{{ $classes->sum('students_count') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes and Teachers -->
        <div class="lg:col-span-2">
            <!-- Classes Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Classes ({{ $classes->count() }})</h3>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('classes.create', ['department_id' => $department->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        + Add Class
                    </a>
                    @endif
                </div>

                @if($classes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($classes as $class)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-900">{{ $class->name }}</h4>
                            @if($class->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span>{{ $class->students_count }} Students</span>
                            </div>
                            @if($class->section)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <span>Section: {{ $class->section }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('classes.show', $class) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                View Details →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <p class="mt-2">No classes in this department yet</p>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('classes.create', ['department_id' => $department->id]) }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                        Create first class
                    </a>
                    @endif
                </div>
                @endif
            </div>

            <!-- Teachers Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Teachers ({{ $teachers->count() }})</h3>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('teachers.create', ['department_id' => $department->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        + Add Teacher
                    </a>
                    @endif
                </div>

                @if($teachers->count() > 0)
                <div class="space-y-3">
                    @foreach($teachers as $teacher)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                @if($teacher->user->avatar)
                                <img class="h-12 w-12 rounded-full" src="{{ asset('storage/' . $teacher->user->avatar) }}" alt="{{ $teacher->user->name }}">
                                @else
                                <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                                </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $teacher->user->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $teacher->designation }}</p>
                                <p class="text-sm text-gray-500">{{ $teacher->user->email }}</p>
                            </div>
                            <div>
                                @if($teacher->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Active</span>
                                @else
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('teachers.show', $teacher) }}" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="mt-2">No teachers in this department yet</p>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('teachers.create', ['department_id' => $department->id]) }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                        Assign first teacher
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
