@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Attendance</h1>
    </div>

    <!-- Attendance Summary -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Attendance Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-green-100 p-4 rounded-lg">
                <div class="text-green-800 font-semibold">Present</div>
                <div class="text-2xl font-bold">{{ $presentCount }}</div>
            </div>
            <div class="bg-red-100 p-4 rounded-lg">
                <div class="text-red-800 font-semibold">Absent</div>
                <div class="text-2xl font-bold">{{ $absentCount }}</div>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg">
                <div class="text-yellow-800 font-semibold">Late</div>
                <div class="text-2xl font-bold">{{ $lateCount }}</div>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg">
                <div class="text-blue-800 font-semibold">Leave</div>
                <div class="text-2xl font-bold">{{ $leaveCount }}</div>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg">
                <div class="text-purple-800 font-semibold">Attendance Rate</div>
                <div class="text-2xl font-bold">{{ $attendanceRate }}%</div>
            </div>
        </div>
    </div>

    <!-- Monthly Attendance Chart -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Monthly Attendance</h2>
        <div class="h-64 bg-gray-50 p-4 rounded-lg">
            <!-- This is where we would put a chart, but for now we'll use a placeholder -->
            <div class="w-full h-full flex items-center justify-center">
                <div class="text-gray-400">Monthly attendance chart will be displayed here</div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('my.attendance') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full md:w-auto">
                <label for="month" class="block text-gray-700 text-sm font-bold mb-2">Month</label>
                <select name="month" id="month" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" {{ request('month', date('n')) == $month ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <label for="year" class="block text-gray-700 text-sm font-bold mb-2">Year</label>
                <select name="year" id="year" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach(range(date('Y') - 2, date('Y')) as $year)
                        <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-left">Remarks</th>
                        <th class="py-3 px-6 text-left">Marked By</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($attendances as $attendance)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $attendance->date->format('d M, Y (l)') }}</td>
                            <td class="py-3 px-6 text-center">
                                @if($attendance->status == 'present')
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Present</span>
                                @elseif($attendance->status == 'absent')
                                    <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Absent</span>
                                @elseif($attendance->status == 'late')
                                    <span class="bg-yellow-100 text-yellow-800 py-1 px-3 rounded-full text-xs">Late</span>
                                @elseif($attendance->status == 'leave')
                                    <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs">Leave</span>
                                @elseif($attendance->status == 'half_day')
                                    <span class="bg-purple-100 text-purple-800 py-1 px-3 rounded-full text-xs">Half Day</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-left">{{ $attendance->remarks ?? '-' }}</td>
                            <td class="py-3 px-6 text-left">{{ $attendance->markedBy->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 px-6 text-center text-gray-500">No attendance records found for the selected period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection