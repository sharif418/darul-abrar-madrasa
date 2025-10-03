@extends('layouts.app')

@section('header', 'Grade Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Grade: {{ $gradingScale->grade_name }}</h1>
        <div class="flex space-x-2">
            <x-button href="{{ route('grading-scales.edit', $gradingScale->id) }}" color="warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </x-button>
            <x-button href="{{ route('grading-scales.index') }}" color="secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Grades
            </x-button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Grade Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Basic Details</h3>
                    <table class="min-w-full">
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">Grade Name:</td>
                            <td class="py-2 text-sm text-gray-900">{{ $gradingScale->grade_name }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">GPA Point:</td>
                            <td class="py-2 text-sm text-gray-900">{{ number_format($gradingScale->gpa_point, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">Mark Range:</td>
                            <td class="py-2 text-sm text-gray-900">{{ number_format($gradingScale->min_mark, 2) }} - {{ number_format($gradingScale->max_mark, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">Status:</td>
                            <td class="py-2 text-sm">
                                @if($gradingScale->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Additional Information</h3>
                    <table class="min-w-full">
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">Description:</td>
                            <td class="py-2 text-sm text-gray-900">{{ $gradingScale->description ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">Created At:</td>
                            <td class="py-2 text-sm text-gray-900">{{ $gradingScale->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 text-sm font-medium text-gray-500">Last Updated:</td>
                            <td class="py-2 text-sm text-gray-900">{{ $gradingScale->updated_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Grade Visualization</h2>
        </div>
        <div class="p-6">
            <div class="w-full bg-gray-200 rounded-full h-4 mb-6">
                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ ($gradingScale->max_mark - $gradingScale->min_mark) }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>0</span>
                <span>{{ $gradingScale->min_mark }}</span>
                <span>{{ $gradingScale->max_mark }}</span>
                <span>100</span>
            </div>
            <div class="mt-6 flex justify-center">
                <div class="inline-block px-6 py-3 rounded-lg {{ $gradingScale->gpa_point > 2.0 ? 'bg-green-100 text-green-800' : ($gradingScale->gpa_point > 1.0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    <span class="text-3xl font-bold">{{ $gradingScale->grade_name }}</span>
                    <span class="ml-2 text-lg">({{ number_format($gradingScale->gpa_point, 2) }})</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-between">
        <form action="{{ route('grading-scales.toggle-active', $gradingScale->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <x-button type="submit" color="{{ $gradingScale->is_active ? 'danger' : 'success' }}">
                @if($gradingScale->is_active)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Deactivate Grade
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Activate Grade
                @endif
            </x-button>
        </form>
        <form action="{{ route('grading-scales.destroy', $gradingScale->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this grade?');">
            @csrf
            @method('DELETE')
            <x-button type="submit" color="danger">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Grade
            </x-button>
        </form>
    </div>
</div>
@endsection