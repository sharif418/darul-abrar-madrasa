@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('classes.index') }}" class="hover:text-blue-600">Classes</a>
            <span>/</span>
            <span class="text-gray-900">Edit Class</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Edit Class: {{ $class->name }}</h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form action="{{ route('classes.update', $class) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Class Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Class Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                    placeholder="e.g., Class 1, Hifz Level 1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department -->
            <div class="mb-4">
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Department <span class="text-red-500">*</span>
                </label>
                <select name="department_id" id="department_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id', $class->department_id) == $department->id ? 'selected' : '' }}>
                        {{ $department->name }} ({{ $department->code }})
                    </option>
                    @endforeach
                </select>
                @error('department_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Class Numeric -->
                <div>
                    <label for="class_numeric" class="block text-sm font-medium text-gray-700 mb-1">
                        Class Level/Number
                    </label>
                    <input type="text" name="class_numeric" id="class_numeric" value="{{ old('class_numeric', $class->class_numeric) }}"
                        placeholder="e.g., 1, 2, 3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('class_numeric') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Optional: For sorting/grouping</p>
                    @error('class_numeric')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Section -->
                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-1">
                        Section
                    </label>
                    <input type="text" name="section" id="section" value="{{ old('section', $class->section) }}"
                        placeholder="e.g., A, B, Morning"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('section') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Optional: Section identifier</p>
                    @error('section')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Capacity -->
            <div class="mb-4">
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">
                    Student Capacity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $class->capacity) }}" required min="1"
                    placeholder="30"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Maximum number of students allowed in this class</p>
                @error('capacity')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea name="description" id="description" rows="4"
                    placeholder="Brief description of the class..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $class->description) }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Inactive classes will not be available for student enrollment</p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Update Class
                </button>
                <a href="{{ route('classes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Warning Section -->
    @if($class->students()->count() > 0 || $class->subjects()->count() > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6 max-w-2xl">
        <h3 class="text-sm font-semibold text-yellow-900 mb-2">⚠️ Important Notice</h3>
        <p class="text-sm text-yellow-800">
            This class has {{ $class->students()->count() }} student(s) and {{ $class->subjects()->count() }} subject(s) assigned. 
            Changing the department or making it inactive may affect these assignments.
        </p>
    </div>
    @endif
</div>
@endsection
