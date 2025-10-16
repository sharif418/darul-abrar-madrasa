@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Timetable</h1>
        <a href="{{ route('timetables.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">Validation Errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->any() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Entry Count Warning -->
    @if($timetable->entries()->count() > 0)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">Notice:</strong>
            <p class="mt-1">This timetable has {{ $timetable->entries()->count() }} entries. Changing effective dates may affect existing schedules.</p>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('timetables.update', $timetable) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Timetable Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $timetable->name) }}" required maxlength="255" placeholder="e.g., Spring 2025 Timetable" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Brief description of this timetable" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $timetable->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Effective From -->
                    <div>
                        <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-2">Effective From <span class="text-red-500">*</span></label>
                        <input type="date" name="effective_from" id="effective_from" value="{{ old('effective_from', $timetable->effective_from->format('Y-m-d')) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('effective_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Effective To -->
                    <div>
                        <label for="effective_to" class="block text-sm font-medium text-gray-700 mb-2">Effective To</label>
                        <input type="date" name="effective_to" id="effective_to" value="{{ old('effective_to', $timetable->effective_to?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Leave empty for ongoing timetables</p>
                        @error('effective_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $timetable->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-save mr-2"></i>Update Timetable
                </button>
                <a href="{{ route('timetables.show', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">
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
            <li>• Changing effective dates may affect when this timetable is considered current</li>
            <li>• Deactivating a timetable will hide it from active use</li>
            <li>• Existing entries will not be affected by name or description changes</li>
        </ul>
    </div>
</div>
@endsection
