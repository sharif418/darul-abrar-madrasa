@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Take Teacher Attendance</h1>
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

    <!-- Info Section -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Date:</strong> {{ $date->format('l, d F Y') }} | 
                    <strong>Total Teachers:</strong> {{ count($teachers) }}
                    @if(count($existingTeacherIds ?? []) > 0)
                        | <strong>Already Marked:</strong> {{ count($existingTeacherIds) }}
                    @endif
                </p>
                <p class="text-sm text-blue-700 mt-1">
                    Mark attendance for all active teachers. Leave check-in/check-out times empty for absent or leave status.
                    @if(count($existingTeacherIds ?? []) > 0)
                        <br><strong>Note:</strong> Teachers with existing attendance will be updated if you submit this form.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Bulk Entry Form -->
    <form method="POST" action="{{ route('teacher-attendances.store-bulk') }}" id="attendanceForm">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Teacher Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Check In Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Check Out Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Remarks
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($teachers as $teacher)
                            <tr class="hover:bg-gray-50 {{ in_array($teacher->id, $existingTeacherIds ?? []) ? 'bg-yellow-50' : '' }}" data-teacher-id="{{ $teacher->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $teacher->employee_id }}
                                    @if(in_array($teacher->id, $existingTeacherIds ?? []))
                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Already Marked
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($teacher->user->avatar)
                                                <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $teacher->user->avatar) }}" alt="">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                    {{ substr($teacher->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $teacher->user->name }}</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="teacher_ids[]" value="{{ $teacher->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $teacher->department->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status[{{ $teacher->id }}]" value="present" 
                                                   class="form-radio text-green-600 status-radio" checked>
                                            <span class="ml-2 text-sm text-gray-700">Present</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status[{{ $teacher->id }}]" value="absent" 
                                                   class="form-radio text-red-600 status-radio">
                                            <span class="ml-2 text-sm text-gray-700">Absent</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status[{{ $teacher->id }}]" value="leave" 
                                                   class="form-radio text-blue-600 status-radio">
                                            <span class="ml-2 text-sm text-gray-700">Leave</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status[{{ $teacher->id }}]" value="half_day" 
                                                   class="form-radio text-yellow-600 status-radio">
                                            <span class="ml-2 text-sm text-gray-700">Half Day</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="time" name="check_in_time[{{ $teacher->id }}]" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 check-in-time"
                                           placeholder="HH:MM">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="time" name="check_out_time[{{ $teacher->id }}]" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 check-out-time"
                                           placeholder="HH:MM">
                                </td>
                                <td class="px-6 py-4">
                                    <textarea name="remarks[{{ $teacher->id }}]" rows="2"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Optional remarks"></textarea>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No active teachers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Submit Section -->
            @if(count($teachers) > 0)
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <a href="{{ route('teacher-attendances.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                        Save Attendance
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>

<!-- JavaScript Enhancement -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('attendanceForm');
    const rows = document.querySelectorAll('tr[data-teacher-id]');

    rows.forEach(row => {
        const statusRadios = row.querySelectorAll('.status-radio');
        const checkInInput = row.querySelector('.check-in-time');
        const checkOutInput = row.querySelector('.check-out-time');

        // Handle status change
        statusRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const status = this.value;
                
                if (status === 'present') {
                    // Auto-fill check-in time with current time if empty
                    if (!checkInInput.value) {
                        const now = new Date();
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        checkInInput.value = `${hours}:${minutes}`;
                    }
                    checkInInput.disabled = false;
                    checkOutInput.disabled = false;
                } else if (status === 'absent' || status === 'leave') {
                    // Clear and disable time inputs for absent/leave
                    checkInInput.value = '';
                    checkOutInput.value = '';
                    checkInInput.disabled = true;
                    checkOutInput.disabled = true;
                } else if (status === 'half_day') {
                    // Enable time inputs for half day
                    checkInInput.disabled = false;
                    checkOutInput.disabled = false;
                }
            });
        });
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        let hasError = false;
        const errors = [];

        rows.forEach(row => {
            const teacherId = row.dataset.teacherId;
            const status = row.querySelector('input[name="status[' + teacherId + ']"]:checked').value;
            const checkIn = row.querySelector('.check-in-time').value;
            const checkOut = row.querySelector('.check-out-time').value;

            // Validate check-out time is after check-in time
            if (checkIn && checkOut) {
                if (checkOut <= checkIn) {
                    hasError = true;
                    errors.push(`Teacher ID ${teacherId}: Check-out time must be after check-in time`);
                }
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Validation Errors:\n' + errors.join('\n'));
        }
    });

    // Bulk actions
    const bulkPresentBtn = document.createElement('button');
    bulkPresentBtn.type = 'button';
    bulkPresentBtn.className = 'bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm mr-2';
    bulkPresentBtn.textContent = 'Mark All Present';
    bulkPresentBtn.addEventListener('click', function() {
        rows.forEach(row => {
            row.querySelector('input[value="present"]').checked = true;
            row.querySelector('input[value="present"]').dispatchEvent(new Event('change'));
        });
    });

    const submitSection = document.querySelector('.bg-gray-50.flex');
    if (submitSection) {
        submitSection.insertBefore(bulkPresentBtn, submitSection.firstChild);
    }
});
</script>
@endsection
