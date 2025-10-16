@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Period</h1>
        <a href="{{ route('periods.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">Validation Errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Usage Warning -->
    @if($period->timetableEntries()->count() > 0)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">Warning:</strong>
            <p class="mt-1">This period is used in {{ $period->timetableEntries()->count() }} timetable entries. Changing the time may affect existing schedules.</p>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('periods.update', $period) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Period Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $period->name) }}" required placeholder="e.g., Period 1, Morning Assembly" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Day of Week -->
                <div>
                    <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-2">Day of Week <span class="text-red-500">*</span></label>
                    <select name="day_of_week" id="day_of_week" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Day</option>
                        <option value="monday" {{ old('day_of_week', $period->day_of_week) == 'monday' ? 'selected' : '' }}>Monday</option>
                        <option value="tuesday" {{ old('day_of_week', $period->day_of_week) == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                        <option value="wednesday" {{ old('day_of_week', $period->day_of_week) == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                        <option value="thursday" {{ old('day_of_week', $period->day_of_week) == 'thursday' ? 'selected' : '' }}>Thursday</option>
                        <option value="friday" {{ old('day_of_week', $period->day_of_week) == 'friday' ? 'selected' : '' }}>Friday</option>
                        <option value="saturday" {{ old('day_of_week', $period->day_of_week) == 'saturday' ? 'selected' : '' }}>Saturday</option>
                        <option value="sunday" {{ old('day_of_week', $period->day_of_week) == 'sunday' ? 'selected' : '' }}>Sunday</option>
                    </select>
                    @error('day_of_week')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Time -->
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Time <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $period->start_time) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Time -->
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Time <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $period->end_time) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order -->
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Order <span class="text-red-500">*</span></label>
                    <input type="number" name="order" id="order" value="{{ old('order', $period->order) }}" min="0" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in the schedule</p>
                    @error('order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $period->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-save mr-2"></i>Update Period
                </button>
                <a href="{{ route('periods.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Help Text -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">
            <i class="fas fa-info-circle mr-2"></i>Important Notes
        </h3>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• Changing time slots may affect existing timetable entries</li>
            <li>• Ensure the new time doesn't conflict with other periods on the same day</li>
            <li>• Deactivating a period will hide it from new timetable entries</li>
        </ul>
    </div>
</div>
@endsection
