@extends('layouts.app')

@section('header', 'Lesson Plan Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Lesson Plan Details</h1>
        <div class="flex space-x-2">
            <x-button href="{{ route('lesson-plans.edit', $lessonPlan->id) }}" color="warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </x-button>
            <x-button href="{{ route('lesson-plans.index') }}" color="secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Lesson Plans
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Lesson Plan Details -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-700">{{ $lessonPlan->title }}</h2>
                    <div>
                        @if($lessonPlan->status == 'completed')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Lesson Description</h3>
                        <div class="prose max-w-none text-gray-600">
                            {!! nl2br(e($lessonPlan->description)) !!}
                        </div>
                    </div>

                    @if($lessonPlan->status == 'completed' && $lessonPlan->completion_notes)
                        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <h3 class="text-lg font-semibold text-green-700 mb-2">Completion Notes</h3>
                            <div class="prose max-w-none text-green-600">
                                {!! nl2br(e($lessonPlan->completion_notes)) !!}
                            </div>
                        </div>
                    @endif

                    @if($lessonPlan->status == 'pending')
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Mark as Completed</h3>
                            <form action="{{ route('lesson-plans.mark-completed', $lessonPlan->id) }}" method="POST">
                                @csrf
                                <div>
                                    <x-label for="completion_notes" value="Completion Notes" />
                                    <x-textarea id="completion_notes" name="completion_notes" class="block mt-1 w-full" rows="4" placeholder="Enter notes about how the lesson went, what worked well, what could be improved, etc."></x-textarea>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <x-button type="submit" color="success">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Completed
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Study Materials -->
            <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-700">Related Study Materials</h2>
                    <x-button href="{{ route('study-materials.create', ['class_id' => $lessonPlan->class_id, 'subject_id' => $lessonPlan->subject_id]) }}" color="primary" size="sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Material
                    </x-button>
                </div>
                <div class="p-6">
                    <!-- This would be populated with related study materials -->
                    <p class="text-gray-500 text-center">No related study materials found.</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-700">Lesson Information</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Teacher</h3>
                            <p class="mt-1 text-base text-gray-900">{{ $lessonPlan->teacher->user->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Class</h3>
                            <p class="mt-1 text-base text-gray-900">{{ $lessonPlan->class->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Subject</h3>
                            <p class="mt-1 text-base text-gray-900">{{ $lessonPlan->subject->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Lesson Date</h3>
                            <p class="mt-1 text-base text-gray-900">{{ $lessonPlan->plan_date->format('F d, Y') }}</p>
                            <p class="text-sm text-gray-500">
                                @if($lessonPlan->plan_date->isPast())
                                    {{ $lessonPlan->plan_date->diffForHumans() }}
                                @elseif($lessonPlan->plan_date->isToday())
                                    <span class="text-green-600 font-medium">Today</span>
                                @else
                                    {{ $lessonPlan->plan_date->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $lessonPlan->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $lessonPlan->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-between">
                            <form action="{{ route('lesson-plans.destroy', $lessonPlan->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lesson plan?');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" color="danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </x-button>
                            </form>
                            <x-button href="{{ route('lesson-plans.edit', $lessonPlan->id) }}" color="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection