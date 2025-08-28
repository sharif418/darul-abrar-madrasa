@extends('layouts.app')

@section('header', 'Edit Grade')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Grade: {{ $gradingScale->grade_name }}</h1>
        <x-button href="{{ route('grading-scales.index') }}" color="secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Grades
        </x-button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Grade Information</h2>
            <p class="text-gray-600 mt-1">Update the grade information.</p>
        </div>

        <form action="{{ route('grading-scales.update', $gradingScale->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label for="grade_name" value="Grade Name" />
                    <x-input id="grade_name" type="text" name="grade_name" value="{{ old('grade_name', $gradingScale->grade_name) }}" class="block mt-1 w-full" required autofocus />
                    <p class="text-xs text-gray-500 mt-1">Example: A+, A, B+, etc.</p>
                    <x-input-error for="grade_name" class="mt-2" />
                </div>

                <div>
                    <x-label for="gpa_point" value="GPA Point" />
                    <x-input id="gpa_point" type="number" name="gpa_point" value="{{ old('gpa_point', $gradingScale->gpa_point) }}" class="block mt-1 w-full" step="0.01" min="0" max="5" required />
                    <p class="text-xs text-gray-500 mt-1">Example: 5.00, 4.50, 4.00, etc.</p>
                    <x-input-error for="gpa_point" class="mt-2" />
                </div>

                <div>
                    <x-label for="min_mark" value="Minimum Mark" />
                    <x-input id="min_mark" type="number" name="min_mark" value="{{ old('min_mark', $gradingScale->min_mark) }}" class="block mt-1 w-full" step="0.01" min="0" max="100" required />
                    <p class="text-xs text-gray-500 mt-1">Lowest mark to achieve this grade</p>
                    <x-input-error for="min_mark" class="mt-2" />
                </div>

                <div>
                    <x-label for="max_mark" value="Maximum Mark" />
                    <x-input id="max_mark" type="number" name="max_mark" value="{{ old('max_mark', $gradingScale->max_mark) }}" class="block mt-1 w-full" step="0.01" min="0" max="100" required />
                    <p class="text-xs text-gray-500 mt-1">Highest mark for this grade</p>
                    <x-input-error for="max_mark" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="description" value="Description (Optional)" />
                    <x-textarea id="description" name="description" class="block mt-1 w-full" rows="3">{{ old('description', $gradingScale->description) }}</x-textarea>
                    <p class="text-xs text-gray-500 mt-1">Brief description of this grade (e.g., "Excellent", "Very Good", etc.)</p>
                    <x-input-error for="description" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_active', $gradingScale->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                    <p class="text-xs text-gray-500 ml-6">Only active grades will be used in result calculations</p>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <x-button type="submit" color="primary">
                    Update Grade
                </x-button>
            </div>
        </form>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Important Notes</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Editing Considerations</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Changes will only affect future result calculations</li>
                        <li>Existing results will not be automatically recalculated</li>
                        <li>Mark ranges must not overlap with other grades</li>
                        <li>Deactivating a grade will exclude it from future calculations</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Impact on Results</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Changing grade criteria may affect result consistency</li>
                        <li>Consider recalculating existing results if needed</li>
                        <li>Ensure all possible marks have a corresponding grade</li>
                        <li>Maintain consistent grading policies throughout the academic year</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection