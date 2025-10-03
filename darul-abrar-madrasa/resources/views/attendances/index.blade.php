@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Attendance Records</h1>
        <div class="flex space-x-2">
            <a href="{{ route('attendances.create.class', ['class_id' => request('class_id', 1)]) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Take Attendance
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('attendances.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full md:w-auto">
                <label for="class_id" class="block text-gray-700 text-sm font-bold mb-2">Class</label>
                <select name="class_id" id="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->department->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                <input type="date" name="date" id="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ request('date') }}">
            </div>
            
            <div class="w-full md:w-auto">
                <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Status</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="leave" {{ request('status') === 'leave' ? 'selected' : '' }}>Leave</option>
                </select>
            </div>
            
            <div class="w-full md:w-auto flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Filter
                </button>
            </div>
            
            <div class="w-full md:w-auto flex items-end">
                <a href="{{ route('attendances.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Summary -->
    @if(request('class_id') && request('date'))
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Attendance Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            </div>
        </div>
    @endif

    <!-- Attendance List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Student</th>
                        <th class="py-3 px-6 text-left">Class</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-left">Remarks</th>
                        <th class="py-3 px-6 text-left">Marked By</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($attendances as $attendance)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $attendance->id }}</td>
                            <td class="py-3 px-6 text-left">
                                <div class="flex items-center">
                                    @if($attendance->student->user->avatar)
                                        <div class="mr-2">
                                            <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $attendance->student->user->avatar) }}" alt="{{ $attendance->student->user->name }}">
                                        </div>
                                    @endif
                                    <span>{{ $attendance->student->user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-6 text-left">{{ $attendance->class->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $attendance->date->format('d M, Y') }}</td>
                            <td class="py-3 px-6 text-center">
                                @if($attendance->status == 'present')
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Present</span>
                                @elseif($attendance->status == 'absent')
                                    <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Absent</span>
                                @elseif($attendance->status == 'late')
                                    <span class="bg-yellow-100 text-yellow-800 py-1 px-3 rounded-full text-xs">Late</span>
                                @elseif($attendance->status == 'leave')
                                    <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs">Leave</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-left">{{ $attendance->remarks ?? '-' }}</td>
                            <td class="py-3 px-6 text-left">{{ $attendance->markedBy->name }}</td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="{{ route('attendances.edit', $attendance->id) }}" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 px-6 text-center text-gray-500">No attendance records found</td>
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