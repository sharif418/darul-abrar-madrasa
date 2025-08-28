@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Teacher Dashboard</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">My Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 mb-1">Name: <span class="text-gray-800 font-semibold">{{ Auth::user()->name }}</span></p>
                <p class="text-gray-600 mb-1">Email: <span class="text-gray-800 font-semibold">{{ Auth::user()->email }}</span></p>
                <p class="text-gray-600 mb-1">Department: <span class="text-gray-800 font-semibold">{{ $teacher->department->name }}</span></p>
                <p class="text-gray-600 mb-1">Designation: <span class="text-gray-800 font-semibold">{{ $teacher->designation }}</span></p>
            </div>
            <div>
                <p class="text-gray-600 mb-1">Phone: <span class="text-gray-800 font-semibold">{{ $teacher->phone }}</span></p>
                <p class="text-gray-600 mb-1">Qualification: <span class="text-gray-800 font-semibold">{{ $teacher->qualification }}</span></p>
                <p class="text-gray-600 mb-1">Joining Date: <span class="text-gray-800 font-semibold">{{ $teacher->joining_date->format('d M, Y') }}</span></p>
                <p class="text-gray-600 mb-1">Status: 
                    @if($teacher->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('marks.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="text-center font-semibold">Enter Marks</span>
            </a>
            
            <a href="{{ route('attendances.index') }}" class="bg-green-500 hover:bg-green-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="text-center font-semibold">Take Attendance</span>
            </a>
            
            <a href="{{ route('results.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span class="text-center font-semibold">View Results</span>
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Assigned Subjects -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">My Assigned Subjects</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subject Name</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Class</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedSubjects as $subject)
                        <tr>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $subject->name }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $subject->class->name }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $subject->code }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <a href="{{ route('attendances.create.class', $subject->class_id) }}" class="text-blue-600 hover:text-blue-800 mr-2">Take Attendance</a>
                                <a href="{{ route('results.create.bulk', ['exam_id' => 0, 'class_id' => $subject->class_id, 'subject_id' => $subject->id]) }}" class="text-green-600 hover:text-green-800">Add Results</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No subjects assigned</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Upcoming Exams -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Exams</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Exam Name</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Class</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Start Date</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingExams as $exam)
                        <tr>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $exam->name }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $exam->class->name }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $exam->start_date->format('d M, Y') }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $exam->end_date->format('d M, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No upcoming exams</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Notices -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Notices</h2>
        <div class="space-y-4">
            @forelse($recentNotices as $notice)
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ $notice->title }}</h3>
                <p class="text-sm text-gray-600 mb-2">Published on {{ $notice->publish_date->format('d M, Y') }} by {{ $notice->publishedBy->name }}</p>
                <p class="text-gray-700">{{ Str::limit($notice->description, 150) }}</p>
                <div class="mt-2">
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($notice->notice_for == 'all') bg-blue-100 text-blue-800
                        @elseif($notice->notice_for == 'students') bg-green-100 text-green-800
                        @elseif($notice->notice_for == 'teachers') bg-purple-100 text-purple-800
                        @else bg-gray-100 text-gray-800 @endif">
                        For: {{ ucfirst($notice->notice_for) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No recent notices</p>
            @endforelse
        </div>
    </div>
</div>
@endsection