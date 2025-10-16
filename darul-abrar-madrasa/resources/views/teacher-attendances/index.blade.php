@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Teacher Attendance Records</h1>
        <div class="flex gap-3">
            @can('create', App\Models\TeacherAttendance::class)
                <a href="{{ route('teacher-attendances.create') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Take Attendance
                </a>
            @endcan
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('teacher-attendances.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Department Filter -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" id="department_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" 
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Teacher Filter -->
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1">Teacher</label>
                    <select name="teacher_id" id="teacher_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" 
                                    {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }} ({{ $teacher->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    </select>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    Filter
                </button>
                <a href="{{ route('teacher-attendances.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Dashboard -->
    @if($summary)
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Attendance Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <!-- Present -->
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="text-sm text-green-700 font-medium">Present</div>
                    <div class="text-2xl font-bold text-green-900">{{ $summary['presentCount'] }}</div>
                    <div class="text-xs text-green-600">
                        {{ $summary['totalCount'] > 0 ? round($summary['presentCount'] / $summary['totalCount'] * 100, 1) : 0 }}%
                    </div>
                </div>

                <!-- Absent -->
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="text-sm text-red-700 font-medium">Absent</div>
                    <div class="text-2xl font-bold text-red-900">{{ $summary['absentCount'] }}</div>
                    <div class="text-xs text-red-600">
                        {{ $summary['totalCount'] > 0 ? round($summary['absentCount'] / $summary['totalCount'] * 100, 1) : 0 }}%
                    </div>
                </div>

                <!-- Leave -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="text-sm text-blue-700 font-medium">Leave</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $summary['leaveCount'] }}</div>
                    <div class="text-xs text-blue-600">
                        {{ $summary['totalCount'] > 0 ? round($summary['leaveCount'] / $summary['totalCount'] * 100, 1) : 0 }}%
                    </div>
                </div>

                <!-- Half Day -->
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <div class="text-sm text-yellow-700 font-medium">Half Day</div>
                    <div class="text-2xl font-bold text-yellow-900">{{ $summary['halfDayCount'] }}</div>
                    <div class="text-xs text-yellow-600">
                        {{ $summary['totalCount'] > 0 ? round($summary['halfDayCount'] / $summary['totalCount'] * 100, 1) : 0 }}%
                    </div>
                </div>

                <!-- Late Arrivals -->
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                    <div class="text-sm text-orange-700 font-medium">Late Arrivals</div>
                    <div class="text-2xl font-bold text-orange-900">{{ $summary['lateCount'] }}</div>
                    <div class="text-xs text-orange-600">
                        {{ $summary['totalCount'] > 0 ? round($summary['lateCount'] / $summary['totalCount'] * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>

            <!-- Additional Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-gray-50 p-4 rounded">
                    <div class="text-sm text-gray-700 font-medium mb-2">Attendance Rate</div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-green-600 h-4 rounded-full" style="width: {{ $summary['attendanceRate'] }}%"></div>
                    </div>
                    <div class="text-right text-sm text-gray-600 mt-1">{{ $summary['attendanceRate'] }}%</div>
                </div>

                <div class="bg-gray-50 p-4 rounded">
                    <div class="text-sm text-gray-700 font-medium">Average Working Hours</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $summary['averageWorkingHours'] }} hrs</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Attendance Trends Chart -->
    @if(!empty($trends) && !empty($trends['labels']))
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Attendance Trends</h2>
            <canvas id="attendanceTrendsChart" height="80"></canvas>
        </div>
    @endif

    <!-- Attendance List Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Working Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marked By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($attendance->teacher->user->avatar)
                                            <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $attendance->teacher->user->avatar) }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                {{ substr($attendance->teacher->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $attendance->teacher->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $attendance->teacher->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->teacher->department->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->date->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->status === 'present')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Present
                                    </span>
                                @elseif($attendance->status === 'absent')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Absent
                                    </span>
                                @elseif($attendance->status === 'leave')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Leave
                                    </span>
                                @elseif($attendance->status === 'half_day')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Half Day
                                    </span>
                                @endif
                                @if($attendance->isLate())
                                    <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                        Late
                                    </span>
                                @endif
                                @if($attendance->isEarlyLeave())
                                    <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                        Early
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->check_in_time ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->check_out_time ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->getWorkingHours() ? number_format($attendance->getWorkingHours(), 2) . ' hrs' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @php
                                    use Illuminate\Support\Str;
                                @endphp
                                {{ $attendance->remarks ? Str::limit($attendance->remarks, 50) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->markedBy->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <a href="{{ route('teacher-attendances.show', $attendance) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @can('update', $attendance)
                                        <a href="{{ route('teacher-attendances.edit', $attendance) }}" 
                                           class="text-yellow-600 hover:text-yellow-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endcan
                                    @can('delete', $attendance)
                                        <form action="{{ route('teacher-attendances.destroy', $attendance) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-4 text-center text-gray-500">
                                No teacher attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
            <div class="px-6 py-4 bg-gray-50">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>

@if(!empty($trends) && !empty($trends['labels']))
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const trends = @json($trends);
        const ctx = document.getElementById('attendanceTrendsChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trends.labels,
                datasets: trends.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Teacher Attendance Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
    </script>
    @endpush
@endif
@endsection
