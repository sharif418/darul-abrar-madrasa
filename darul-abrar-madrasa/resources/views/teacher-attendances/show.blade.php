@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Teacher Attendance Details</h1>
        <div class="flex gap-3">
            @can('update', $teacherAttendance)
                <a href="{{ route('teacher-attendances.edit', $teacherAttendance) }}" 
                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            @endcan
            <a href="{{ route('teacher-attendances.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column - Teacher Information -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Teacher Information</h2>
            
            <div class="flex items-center mb-6">
                <div class="flex-shrink-0 h-20 w-20">
                    @if($teacherAttendance->teacher->user->avatar)
                        <img class="h-20 w-20 rounded-full" src="{{ asset('storage/' . $teacherAttendance->teacher->user->avatar) }}" alt="">
                    @else
                        <div class="h-20 w-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-semibold">
                            {{ substr($teacherAttendance->teacher->user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="ml-6">
                    <div class="text-2xl font-bold text-gray-900">{{ $teacherAttendance->teacher->user->name }}</div>
                    <div class="text-sm text-gray-600">{{ $teacherAttendance->teacher->employee_id }}</div>
                </div>
            </div>

            <dl class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Department</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->teacher->department->name ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Email</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->teacher->user->email }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Phone</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->teacher->phone ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Designation</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->teacher->designation ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-sm font-medium text-gray-600">Status</dt>
                    <dd class="text-sm">
                        @if($teacherAttendance->teacher->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Right Column - Attendance Details -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Attendance Details</h2>
            
            <dl class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Date</dt>
                    <dd class="text-sm text-gray-900 font-semibold">{{ $teacherAttendance->date->format('l, d F Y') }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Status</dt>
                    <dd class="text-sm">
                        @if($teacherAttendance->status === 'present')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Present</span>
                        @elseif($teacherAttendance->status === 'absent')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Absent</span>
                        @elseif($teacherAttendance->status === 'leave')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Leave</span>
                        @elseif($teacherAttendance->status === 'half_day')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Half Day</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Check In Time</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->check_in_time ?? '-' }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Check Out Time</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->check_out_time ?? '-' }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Working Hours</dt>
                    <dd class="text-sm text-gray-900 font-semibold">
                        @if($teacherAttendance->getWorkingHours())
                            {{ number_format($teacherAttendance->getWorkingHours(), 2) }} hours
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Late Status</dt>
                    <dd class="text-sm">
                        @if($teacherAttendance->isLate())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Arrived Late</span>
                        @else
                            <span class="text-gray-500">On Time</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Early Leave Status</dt>
                    <dd class="text-sm">
                        @if($teacherAttendance->isEarlyLeave())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Left Early</span>
                        @else
                            <span class="text-gray-500">Normal</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Remarks</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->remarks ?? 'No remarks' }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Marked By</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->markedBy->name ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Marked At</dt>
                    <dd class="text-sm text-gray-900">{{ $teacherAttendance->created_at->format('d M, Y h:i A') }}</dd>
                </div>
                @if($teacherAttendance->created_at != $teacherAttendance->updated_at)
                    <div class="flex justify-between py-2">
                        <dt class="text-sm font-medium text-gray-600">Last Updated</dt>
                        <dd class="text-sm text-gray-900">{{ $teacherAttendance->updated_at->format('d M, Y h:i A') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Teacher Attendance Summary (This Month) -->
    @if(isset($teacherStats))
        <div class="mt-6 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Teacher Attendance Summary (This Month)</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-600">Total Days Marked</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $teacherStats['totalDays'] }}</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-700">Present Days</div>
                    <div class="text-2xl font-bold text-green-900">{{ $teacherStats['presentDays'] }}</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm text-red-700">Absent Days</div>
                    <div class="text-2xl font-bold text-red-900">{{ $teacherStats['absentDays'] }}</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm text-blue-700">Leave Days</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $teacherStats['leaveDays'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-sm text-yellow-700">Half Days</div>
                    <div class="text-2xl font-bold text-yellow-900">{{ $teacherStats['halfDays'] }}</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <div class="text-sm text-orange-700">Late Days</div>
                    <div class="text-2xl font-bold text-orange-900">{{ $teacherStats['lateDays'] }}</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <div class="text-sm text-orange-700">Early Leave Days</div>
                    <div class="text-2xl font-bold text-orange-900">{{ $teacherStats['earlyLeaveDays'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-700 mb-2">Attendance Rate</div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-green-600 h-4 rounded-full" style="width: {{ $teacherStats['attendanceRate'] }}%"></div>
                    </div>
                    <div class="text-right text-sm text-gray-600 mt-1">{{ $teacherStats['attendanceRate'] }}%</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-700">Average Working Hours</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $teacherStats['averageWorkingHours'] }} hrs</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Danger Zone -->
    @can('delete', $teacherAttendance)
        <div class="mt-6 bg-white shadow-md rounded-lg p-6 border-l-4 border-red-500">
            <h2 class="text-xl font-semibold text-red-800 mb-2">Danger Zone</h2>
            <p class="text-sm text-gray-600 mb-4">Once you delete this attendance record, there is no going back. Please be certain.</p>
            
            <form action="{{ route('teacher-attendances.destroy', $teacherAttendance) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this attendance record? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                    Delete Attendance Record
                </button>
            </form>
        </div>
    @endcan
</div>
@endsection
