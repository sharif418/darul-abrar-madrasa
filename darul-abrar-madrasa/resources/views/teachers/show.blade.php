@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Teacher Profile</h1>
        <div class="flex space-x-2">
            <a href="{{ route('teachers.edit', $teacher->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Edit
            </a>
            <a href="{{ route('teachers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Teacher Profile Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white p-4 flex items-center justify-center flex-col">
                @if($teacher->user->avatar)
                    <img class="w-24 h-24 rounded-full object-cover border-4 border-white" src="{{ asset('storage/' . $teacher->user->avatar) }}" alt="{{ $teacher->user->name }}">
                @else
                    <div class="w-24 h-24 rounded-full bg-blue-700 flex items-center justify-center text-white text-3xl font-bold border-4 border-white">
                        {{ substr($teacher->user->name, 0, 1) }}
                    </div>
                @endif
                <h2 class="text-xl font-bold mt-2">{{ $teacher->user->name }}</h2>
                <p class="text-sm">{{ $teacher->designation }}</p>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Department</h3>
                    <p>{{ $teacher->department->name }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Qualification</h3>
                    <p>{{ $teacher->qualification }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Status</h3>
                    <p>
                        @if($teacher->is_active)
                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                        @else
                            <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Inactive</span>
                        @endif
                    </p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Joining Date</h3>
                    <p>{{ $teacher->joining_date->format('d M, Y') }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Email</h3>
                    <p>{{ $teacher->user->email }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Phone</h3>
                    <p>{{ $teacher->phone }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Address</h3>
                    <p>{{ $teacher->address }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Salary</h3>
                    <p>{{ number_format($teacher->salary, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Assigned Subjects -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden md:col-span-2">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Assigned Subjects</h2>
                
                @if(count($assignedSubjects) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700">
                                    <th class="py-2 px-4 border">Subject Name</th>
                                    <th class="py-2 px-4 border">Code</th>
                                    <th class="py-2 px-4 border">Class</th>
                                    <th class="py-2 px-4 border">Full Mark</th>
                                    <th class="py-2 px-4 border">Pass Mark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedSubjects as $subject)
                                    <tr>
                                        <td class="py-2 px-4 border">{{ $subject->name }}</td>
                                        <td class="py-2 px-4 border">{{ $subject->code }}</td>
                                        <td class="py-2 px-4 border">{{ $subject->class->name }}</td>
                                        <td class="py-2 px-4 border">{{ $subject->full_mark }}</td>
                                        <td class="py-2 px-4 border">{{ $subject->pass_mark }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No subjects assigned to this teacher.</p>
                @endif
            </div>
        </div>
        
        <!-- Classes and Exams -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden md:col-span-3">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Classes -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Assigned Classes</h2>
                        
                        @if(count($assignedClasses) > 0)
                            <div class="bg-gray-100 rounded-lg p-4">
                                <ul class="space-y-2">
                                    @foreach($assignedClasses as $class)
                                        <li class="bg-white p-3 rounded-lg shadow">
                                            <div class="font-semibold">{{ $class->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $class->department->name }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-gray-500">No classes assigned to this teacher.</p>
                        @endif
                    </div>
                    
                    <!-- Upcoming Exams -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Exams</h2>
                        
                        @if(count($upcomingExams) > 0)
                            <div class="bg-gray-100 rounded-lg p-4">
                                <ul class="space-y-2">
                                    @foreach($upcomingExams as $exam)
                                        <li class="bg-white p-3 rounded-lg shadow">
                                            <div class="font-semibold">{{ $exam->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $exam->class->name }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ $exam->start_date->format('d M, Y') }} - {{ $exam->end_date->format('d M, Y') }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-gray-500">No upcoming exams for this teacher's classes.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection