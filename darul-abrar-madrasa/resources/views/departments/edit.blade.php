@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('departments.index') }}" class="hover:text-blue-600">Departments</a>
            <span>/</span>
            <span class="text-gray-900">Edit Department</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Edit Department: {{ $department->name }}</h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form action="{{ route('departments.update', $department) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Department Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" required
                    placeholder="e.g., Hifz Department"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Code -->
            <div class="mb-4">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    Department Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" id="code" value="{{ old('code', $department->code) }}" required
                    placeholder="e.g., HIFZ"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Unique code for the department (e.g., HIFZ, QIRAT, TAFSIR)</p>
                @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea name="description" id="description" rows="4"
                    placeholder="Brief description of the department..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $department->description) }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Inactive departments will not be available for selection</p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Update Department
                </button>
                <a href="{{ route('departments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Warning Section -->
    @if($department->classes()->count() > 0 || $department->teachers()->count() > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6 max-w-2xl">
        <h3 class="text-sm font-semibold text-yellow-900 mb-2">⚠️ Important Notice</h3>
        <p class="text-sm text-yellow-800">
            This department has {{ $department->classes()->count() }} class(es) and {{ $department->teachers()->count() }} teacher(s) assigned. 
            Changing the department code or making it inactive may affect these assignments.
        </p>
    </div>
    @endif
</div>
@endsection
