@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Teacher Attendance</h1>
        <a href="{{ route('teacher-attendances.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            Back to List
        </a>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Teacher Info Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Teacher Information</h2>
        <div class="flex items-center">
            <div class="flex-shrink-0 h-16 w-16">
                @if($teacherAttendance->teacher->user->avatar)
                    <img class="h-16 w-16 rounded-full" src="{{ asset('storage/' . $teacherAttendance->teacher->user->avatar) }}" alt="">
                @else
                    <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-semibold">
                        {{ substr($teacherAttendance->teacher->user->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="ml-6">
                <div class="text-xl font-bold text-gray-900">{{ $teacherAttendance->teacher->user->name }}</div>
                <div class="text-sm text-gray-600">Employee ID: {{ $teacherAttendance->teacher->employee_id }}</div>
                <div class="text-sm text-gray-600">Department: {{ $teacherAttendance->teacher->department->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-600">Date: {{ $teacherAttendance->date->format('l, d F Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('teacher-attendances.update', $teacherAttendance) }}" id="editForm">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" id="date" 
                           value="{{ old('date', $teacherAttendance->date->format('Y-m-d')) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                        <option value="present" {{ old('status', $teacherAttendance->status) == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ old('status', $teacherAttendance->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="leave" {{ old('status', $teacherAttendance->status) == 'leave' ? 'selected' : '' }}>Leave</option>
                        <option value="half_day" {{ old('status', $teacherAttendance->status) == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Check In Time -->
                <div>
                    <label for="check_in_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Check In Time
                    </label>
                    <input type="time" name="check_in_time" id="check_in_time" 
                           value="{{ old('check_in_time', $teacherAttendance->check_in_time) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('check_in_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Check Out Time -->
                <div>
                    <label for="check_out_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Check Out Time
                    </label>
                    <input type="time" name="check_out_time" id="check_out_time" 
                           value="{{ old('check_out_time', $teacherAttendance->check_out_time) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('check_out_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remarks -->
                <div class="md:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                        Remarks
                    </label>
                    <textarea name="remarks" id="remarks" rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Optional remarks (e.g., reason for leave)">{{ old('remarks', $teacherAttendance->remarks) }}</textarea>
                    @error('remarks')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Calculated Information Display -->
            @if($teacherAttendance->check_in_time && $teacherAttendance->check_out_time)
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Current Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-600">Working Hours:</span>
                            <span class="ml-2 text-sm font-semibold text-gray-900">
                                {{ number_format($teacherAttendance->getWorkingHours(), 2) }} hours
                            </span>
                        </div>
                        @if($teacherAttendance->isLate())
                            <div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    Arrived Late
                                </span>
                            </div>
                        @endif
                        @if($teacherAttendance->isEarlyLeave())
                            <div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    Left Early
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Real-time Calculation Display -->
            <div id="calculatedInfo" class="mt-6 p-4 bg-blue-50 rounded-lg hidden">
                <h3 class="text-sm font-medium text-blue-700 mb-2">Calculated Working Hours</h3>
                <div class="text-lg font-semibold text-blue-900" id="workingHoursDisplay">-</div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    Update Attendance
                </button>
                <a href="{{ route('teacher-attendances.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript Enhancement -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editForm');
    const statusSelect = document.getElementById('status');
    const checkInInput = document.getElementById('check_in_time');
    const checkOutInput = document.getElementById('check_out_time');
    const calculatedInfo = document.getElementById('calculatedInfo');
    const workingHoursDisplay = document.getElementById('workingHoursDisplay');

    // Handle status change
    statusSelect.addEventListener('change', function() {
        const status = this.value;
        
        if (status === 'absent' || status === 'leave') {
            checkInInput.value = '';
            checkOutInput.value = '';
            checkInInput.disabled = true;
            checkOutInput.disabled = true;
        } else {
            checkInInput.disabled = false;
            checkOutInput.disabled = false;
        }
        
        calculateWorkingHours();
    });

    // Calculate working hours in real-time
    function calculateWorkingHours() {
        if (checkInInput.value && checkOutInput.value) {
            const checkIn = new Date('2000-01-01 ' + checkInInput.value);
            const checkOut = new Date('2000-01-01 ' + checkOutInput.value);
            
            if (checkOut > checkIn) {
                const diff = (checkOut - checkIn) / (1000 * 60 * 60); // Convert to hours
                workingHoursDisplay.textContent = diff.toFixed(2) + ' hours';
                calculatedInfo.classList.remove('hidden');
            } else {
                calculatedInfo.classList.add('hidden');
            }
        } else {
            calculatedInfo.classList.add('hidden');
        }
    }

    checkInInput.addEventListener('change', calculateWorkingHours);
    checkOutInput.addEventListener('change', calculateWorkingHours);

    // Form validation
    form.addEventListener('submit', function(e) {
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;

        if (checkIn && checkOut && checkOut <= checkIn) {
            e.preventDefault();
            alert('Check-out time must be after check-in time');
            return false;
        }
    });

    // Initialize on page load
    if (statusSelect.value === 'absent' || statusSelect.value === 'leave') {
        checkInInput.disabled = true;
        checkOutInput.disabled = true;
    }
    
    calculateWorkingHours();
});
</script>
@endsection
