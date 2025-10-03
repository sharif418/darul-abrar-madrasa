@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Exam</h1>
        <div class="flex space-x-2">
            <a href="{{ route('exams.show', $exam->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                View Details
            </a>
            <a href="{{ route('exams.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to List
            </a>
        </div>
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
        <form method="POST" action="{{ route('exams.update', $exam->id) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Exam Name *</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $exam->name) }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="class_id" class="block text-gray-700 text-sm font-bold mb-2">Class *</label>
                    <select name="class_id" id="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('class_id') border-red-500 @enderror" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $exam->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->department->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('start_date') border-red-500 @enderror" value="{{ old('start_date', $exam->start_date->format('Y-m-d')) }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="end_date" class="block text-gray-700 text-sm font-bold mb-2">End Date *</label>
                    <input type="date" name="end_date" id="end_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('end_date') border-red-500 @enderror" value="{{ old('end_date', $exam->end_date->format('Y-m-d')) }}" required>
                </div>
                
                <div class="mb-4 md:col-span-2">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror">{{ old('description', $exam->description) }}</textarea>
                </div>
                
                <div class="mb-4">
                    <label for="is_active" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="is_active" id="is_active" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="1" {{ old('is_active', $exam->is_active) == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $exam->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="is_result_published" class="block text-gray-700 text-sm font-bold mb-2">Result Status</label>
                    <select name="is_result_published" id="is_result_published" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" {{ $exam->is_result_published ? 'disabled' : '' }}>
                        <option value="0" {{ old('is_result_published', $exam->is_result_published) == '0' ? 'selected' : '' }}>Results Not Published</option>
                        <option value="1" {{ old('is_result_published', $exam->is_result_published) == '1' ? 'selected' : '' }}>Results Published</option>
                    </select>
                    @if($exam->is_result_published)
                        <p class="text-sm text-gray-500 mt-1">Results have been published and cannot be unpublished.</p>
                        <input type="hidden" name="is_result_published" value="1">
                    @endif
                </div>
            </div>
            
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Exam
                </button>
            </div>
        </form>
    </div>
</div>
@endsection