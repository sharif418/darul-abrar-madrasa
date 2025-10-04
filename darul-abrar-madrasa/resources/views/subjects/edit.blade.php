@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('subjects.index') }}" class="hover:text-blue-600">Subjects</a>
            <span>/</span>
            <span class="text-gray-900">Edit Subject</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Edit Subject: {{ $subject->name }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form action="{{ route('subjects.update', $subject) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Subject Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $subject->name) }}" required
                    placeholder="e.g., Quran Recitation, Arabic Grammar"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    Subject Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" id="code" value="{{ old('code', $subject->code) }}" required
                    placeholder="e.g., QUR101, ARB201"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Unique code for the subject</p>
                @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" id="class_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('class_id') border-red-500 @enderror">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ old('class_id', $subject->class_id) == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} - {{ $class->department->name }}
                    </option>
                    @endforeach
                </select>
                @error('class_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Teacher
                </label>
                <select name="teacher_id" id="teacher_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('teacher_id') border-red-500 @enderror">
                    <option value="">Select Teacher (Optional)</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $subject->teacher_id) == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->user->name }} - {{ $teacher->designation }}
                    </option>
                    @endforeach
                </select>
                @error('teacher_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="full_mark" class="block text-sm font-medium text-gray-700 mb-1">
                        Full Marks <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="full_mark" id="full_mark" value="{{ old('full_mark', $subject->full_mark) }}" required min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('full_mark') border-red-500 @enderror">
                    @error('full_mark')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pass_mark" class="block text-sm font-medium text-gray-700 mb-1">
                        Pass Marks <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="pass_mark" id="pass_mark" value="{{ old('pass_mark', $subject->pass_mark) }}" required min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('pass_mark') border-red-500 @enderror">
                    @error('pass_mark')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea name="description" id="description" rows="4"
                    placeholder="Brief description of the subject..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $subject->description) }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Update Subject
                </button>
                <a href="{{ route('subjects.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @if($subject->results()->count() > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6 max-w-2xl">
        <h3 class="text-sm font-semibold text-yellow-900 mb-2">⚠️ Important Notice</h3>
        <p class="text-sm text-yellow-800">
            This subject has {{ $subject->results()->count() }} result(s) recorded. 
            Changing marks configuration may affect existing results.
        </p>
    </div>
    @endif
</div>
@endsection
