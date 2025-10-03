@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Attendance Record</h1>
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
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Attendance Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="text-gray-600 font-semibold">Student:</span> 
                    <span>{{ $attendance->student->user->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Class:</span> 
                    <span>{{ $attendance->class->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Date:</span> 
                    <span>{{ $attendance->date->format('d M, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Marked By:</span> 
                    <span>{{ $attendance->markedBy->name }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('attendances.update', $attendance->id) }}">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Attendance Status *</label>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-green-600" name="status" value="present" {{ $attendance->status == 'present' ? 'checked' : '' }}>
                        <span class="ml-2">Present</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-red-600" name="status" value="absent" {{ $attendance->status == 'absent' ? 'checked' : '' }}>
                        <span class="ml-2">Absent</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-yellow-600" name="status" value="late" {{ $attendance->status == 'late' ? 'checked' : '' }}>
                        <span class="ml-2">Late</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-blue-600" name="status" value="leave" {{ $attendance->status == 'leave' ? 'checked' : '' }}>
                        <span class="ml-2">Leave</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-purple-600" name="status" value="half_day" {{ $attendance->status == 'half_day' ? 'checked' : '' }}>
                        <span class="ml-2">Half Day</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">Remarks</label>
                <textarea name="remarks" id="remarks" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('remarks', $attendance->remarks) }}</textarea>
            </div>
            
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Attendance
                </button>
            </div>
        </form>
    </div>
</div>
@endsection