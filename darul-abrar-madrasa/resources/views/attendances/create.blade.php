@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Take Attendance</h1>
        <a href="{{ route('attendances.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Back to List
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Please fix the following errors:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Class: {{ $class->name }}</h2>
            <div class="flex flex-wrap gap-4 mb-4">
                <div>
                    <span class="text-gray-600 font-semibold">Department:</span> 
                    <span>{{ $class->department->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Date:</span> 
                    <span>{{ $date->format('d M, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Total Students:</span> 
                    <span>{{ count($students) }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('attendances.store.bulk') }}">
            @csrf
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Roll No.</th>
                            <th class="py-3 px-6 text-left">Student Name</th>
                            <th class="py-3 px-6 text-center">Attendance Status</th>
                            <th class="py-3 px-6 text-left">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        @forelse($students as $student)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">{{ $student->roll_number ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-left">
                                    <div class="flex items-center">
                                        @if($student->user->avatar)
                                            <div class="mr-2">
                                                <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $student->user->avatar) }}" alt="{{ $student->user->name }}">
                                            </div>
                                        @endif
                                        <span>{{ $student->user->name }}</span>
                                    </div>
                                    <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex justify-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio text-green-600" name="status[{{ $student->id }}]" value="present" checked>
                                            <span class="ml-2">Present</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio text-red-600" name="status[{{ $student->id }}]" value="absent">
                                            <span class="ml-2">Absent</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio text-yellow-600" name="status[{{ $student->id }}]" value="late">
                                            <span class="ml-2">Late</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio text-blue-600" name="status[{{ $student->id }}]" value="leave">
                                            <span class="ml-2">Leave</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <input type="text" name="remarks[{{ $student->id }}]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Optional remarks">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 px-6 text-center text-gray-500">No students found in this class</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(count($students) > 0)
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Attendance
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection