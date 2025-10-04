@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('subjects.index') }}" class="hover:text-blue-600">Subjects</a>
            <span>/</span>
            <span class="text-gray-900">Subject Details</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">{{ $subject->name }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('subjects.edit', $subject) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Edit Subject
                </a>
                <a href="{{ route('subjects.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Subject Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-purple-500 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr($subject->code, 0, 3)) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $subject->name }}</h2>
                    <p class="text-gray-600 mt-1">{{ $subject->code }}</p>
                    
                    <div class="mt-3">
                        @if($subject->is_active)
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
                            <label class="text-sm text-gray-600">Class</label>
                            <p class="text-gray-900">{{ $subject->class->name }}</p>
                            <p class="text-sm text-gray-500">{{ $subject->class->department->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Teacher</label>
                            @if($subject->teacher)
                            <p class="text-gray-900">{{ $subject->teacher->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $subject->teacher->designation }}</p>
                            @else
                            <p class="text-gray-500">Not assigned</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Full Marks</label>
                            <p class="text-gray-900 text-2xl font-bold">{{ $subject->full_mark }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Pass Marks</label>
                            <p class="text-gray-900 text-2xl font-bold">{{ $subject->pass_mark }}</p>
                        </div>
                        @if($subject->description)
                        <div>
                            <label class="text-sm text-gray-600">Description</label>
                            <p class="text-gray-900">{{ $subject->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Students</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $students->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Results Recorded</span>
                        <span class="text-2xl font-bold text-green-600">{{ $subject->results->count() }}</span>
                    </div>
                    @if($subject->results->count() > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Average Marks</span>
                        <span class="text-2xl font-bold text-purple-600">{{ number_format($subject->results->avg('marks_obtained'), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pass Rate</span>
                        <span class="text-2xl font-bold text-orange-600">
                            {{ number_format(($subject->results->where('marks_obtained', '>=', $subject->pass_mark)->count() / $subject->results->count()) * 100, 1) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Enrolled Students ({{ $students->count() }})</h3>
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                    <a href="{{ route('results.create.bulk', ['exam_id' => 'latest', 'class_id' => $subject->class_id, 'subject_id' => $subject->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        + Enter Marks
                    </a>
                    @endif
                </div>

                @if($students->count() > 0)
                <div class="space-y-2">
                    @foreach($students as $student)
                    @php
                        $result = $subject->results->where('student_id', $student->id)->first();
                    @endphp
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
                        <div class="text-right">
                            @if($result)
                            <div class="text-lg font-bold {{ $result->marks_obtained >= $subject->pass_mark ? 'text-green-600' : 'text-red-600' }}">
                                {{ $result->marks_obtained }}/{{ $subject->full_mark }}
                            </div>
                            @if($result->grade)
                            <div class="text-sm text-gray-600">Grade: {{ $result->grade }}</div>
                            @endif
                            @else
                            <span class="text-sm text-gray-400">No marks yet</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <p class="mt-2">No students enrolled in this class yet</p>
                </div>
                @endif
            </div>

            <!-- Performance Chart (if results exist) -->
            @if($subject->results->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Overview</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            {{ $subject->results->where('marks_obtained', '>=', $subject->pass_mark)->count() }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">Passed</div>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">
                            {{ $subject->results->where('marks_obtained', '<', $subject->pass_mark)->count() }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">Failed</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ number_format($subject->results->avg('marks_obtained'), 1) }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">Average</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
