@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Student Dashboard</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">My Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 mb-1">Name: <span class="text-gray-800 font-semibold">{{ Auth::user()->name }}</span></p>
                <p class="text-gray-600 mb-1">Email: <span class="text-gray-800 font-semibold">{{ Auth::user()->email }}</span></p>
                <p class="text-gray-600 mb-1">Class: <span class="text-gray-800 font-semibold">{{ $student->class->name }}</span></p>
                <p class="text-gray-600 mb-1">Roll Number: <span class="text-gray-800 font-semibold">{{ $student->roll_number }}</span></p>
                <p class="text-gray-600 mb-1">Admission Number: <span class="text-gray-800 font-semibold">{{ $student->admission_number }}</span></p>
            </div>
            <div>
                <p class="text-gray-600 mb-1">Father's Name: <span class="text-gray-800 font-semibold">{{ $student->father_name }}</span></p>
                <p class="text-gray-600 mb-1">Mother's Name: <span class="text-gray-800 font-semibold">{{ $student->mother_name }}</span></p>
                <p class="text-gray-600 mb-1">Date of Birth: <span class="text-gray-800 font-semibold">{{ $student->date_of_birth->format('d M, Y') }}</span></p>
                <p class="text-gray-600 mb-1">Gender: <span class="text-gray-800 font-semibold">{{ ucfirst($student->gender) }}</span></p>
                <p class="text-gray-600 mb-1">Admission Date: <span class="text-gray-800 font-semibold">{{ $student->admission_date->format('d M, Y') }}</span></p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('my.results') }}" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span class="text-center font-semibold">My Results</span>
            </a>
            
            <a href="{{ route('my.materials') }}" class="bg-green-500 hover:bg-green-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="text-center font-semibold">Study Materials</span>
            </a>
            
            <a href="{{ route('my.attendance') }}" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="text-center font-semibold">My Attendance</span>
            </a>
            
            <a href="{{ route('my.fees') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg p-4 flex flex-col items-center justify-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-center font-semibold">My Fees</span>
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Attendance Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Attendance Summary</h2>
            <div class="flex items-center justify-center">
                <div class="relative w-32 h-32">
                    <svg class="w-full h-full" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="16" fill="none" stroke="#e6e6e6" stroke-width="2"></circle>
                        <circle cx="18" cy="18" r="16" fill="none" stroke="#4ade80" stroke-width="2" stroke-dasharray="{{ $attendancePercentage * 100 / 100 }} 100" stroke-dashoffset="25" transform="rotate(-90 18 18)"></circle>
                        <text x="18" y="18" text-anchor="middle" dominant-baseline="middle" font-size="10" font-weight="bold" fill="#333">{{ $attendancePercentage }}%</text>
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-center">
                <p class="text-gray-600">Present: <span class="text-gray-800 font-semibold">{{ $presentCount }}</span></p>
                <p class="text-gray-600">Total Classes: <span class="text-gray-800 font-semibold">{{ $attendanceCount }}</span></p>
                <a href="{{ route('my.attendance') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">View Details</a>
            </div>
        </div>
        
        <!-- Upcoming Exams -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Exams</h2>
            @forelse($upcomingExams as $exam)
            <div class="mb-3 pb-3 border-b border-gray-200 last:border-0">
                <h3 class="font-semibold text-gray-800">{{ $exam->name }}</h3>
                <p class="text-sm text-gray-600">{{ $exam->start_date->format('d M, Y') }} to {{ $exam->end_date->format('d M, Y') }}</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No upcoming exams</p>
            @endforelse
        </div>
        
        <!-- Recent Results -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Results</h2>
            @forelse($recentResults as $result)
            <div class="mb-3 pb-3 border-b border-gray-200 last:border-0">
                <h3 class="font-semibold text-gray-800">{{ $result->subject->name }}</h3>
                <p class="text-sm text-gray-600">{{ $result->exam->name }}</p>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-600">Marks: <span class="font-semibold">{{ $result->marks_obtained }}/{{ $result->subject->full_mark }}</span></span>
                    <span class="text-gray-600">Grade: <span class="font-semibold">{{ $result->grade }}</span></span>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No recent results</p>
            @endforelse
            <div class="mt-2 text-center">
                <a href="{{ route('my.results') }}" class="inline-block text-blue-600 hover:text-blue-800">View All Results</a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending Fees -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Pending Fees</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fee Type</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Due Date</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingFees as $fee)
                        <tr>
                            <td class="py-2 px-4 border-b border-gray-200">{{ ucfirst($fee->fee_type) }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <div>{{ number_format($fee->amount, 2) }}</div>
                                @if($fee->status == 'partial')
                                    <div class="text-xs text-gray-500">Paid: {{ number_format($fee->paid_amount, 2) }}</div>
                                    <div class="text-xs text-red-500">Due: {{ number_format($fee->amount - $fee->paid_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                {{ $fee->due_date->format('d M, Y') }}
                                @if($fee->isOverdue)
                                    <span class="text-xs text-red-500 font-medium block">OVERDUE</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                @if($fee->status == 'paid')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                @elseif($fee->status == 'partial')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No pending fees</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4 text-right">
                    <a href="{{ route('my.fees') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        View All Fees <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                </table>
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('my.fees') }}" class="inline-block text-blue-600 hover:text-blue-800">View All Fees</a>
            </div>
        </div>
        
        <!-- Recent Notices -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Notices</h2>
            <div class="space-y-4">
                @forelse($recentNotices as $notice)
                <div class="border-b border-gray-200 pb-4 last:border-0">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $notice->title }}</h3>
                    <p class="text-sm text-gray-600 mb-2">Published on {{ $notice->publish_date->format('d M, Y') }}</p>
                    <p class="text-gray-700">{{ Str::limit($notice->description, 100) }}</p>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No recent notices</p>
                @endforelse
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('notices.public') }}" class="inline-block text-blue-600 hover:text-blue-800">View All Notices</a>
            </div>
        </div>
    </div>
</div>
@endsection