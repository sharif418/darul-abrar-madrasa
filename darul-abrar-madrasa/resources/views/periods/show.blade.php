@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ $period->name }}</h1>
        <div class="flex gap-2">
            @can('update', $period)
                <a href="{{ route('periods.edit', $period) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            @endcan
            <a href="{{ route('periods.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Period Details Card -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Period Details</h2>
                
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $period->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Day of Week</dt>
                        <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $period->day_of_week }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Time Range</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $period->getFormattedTimeRange() }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $period->getDurationInMinutes() }} minutes</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Order</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $period->order }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if($period->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1">
                            @if($period->isBreakTime())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-coffee mr-1"></i>Break Time
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <i class="fas fa-book mr-1"></i>Class Period
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $period->created_at->format('M d, Y h:i A') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $period->updated_at->format('M d, Y h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Usage Statistics Card -->
        <div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Usage Statistics</h2>
                
                <div class="space-y-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-900">Timetable Entries</span>
                            <span class="text-2xl font-bold text-blue-600">{{ $period->timetableEntries()->count() }}</span>
                        </div>
                    </div>

                    @if($period->timetableEntries()->count() > 0)
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-green-900">Classes Using</span>
                                <span class="text-2xl font-bold text-green-600">{{ $period->timetableEntries()->distinct('class_id')->count('class_id') }}</span>
                            </div>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-purple-900">Teachers Assigned</span>
                                <span class="text-2xl font-bold text-purple-600">{{ $period->timetableEntries()->whereNotNull('teacher_id')->distinct('teacher_id')->count('teacher_id') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Timetable Entries Table -->
    @if($period->timetableEntries()->count() > 0)
        <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Classes Scheduled in This Period</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($period->timetableEntries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ $entry->day_of_week }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $entry->class->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->subject->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->teacher->user->name ?? 'Not Assigned' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->room_number ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Delete Action (Admin only, if no entries) -->
    @can('delete', $period)
        @if($period->timetableEntries()->count() === 0)
            <div class="mt-6 bg-white shadow-md rounded-lg p-6 border-2 border-red-200">
                <h2 class="text-xl font-semibold text-red-800 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone
                </h2>
                <p class="text-sm text-gray-600 mb-4">Once you delete this period, there is no going back. Please be certain.</p>
                
                <form action="{{ route('periods.destroy', $period) }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this period? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                        <i class="fas fa-trash mr-2"></i>Delete Period
                    </button>
                </form>
            </div>
        @endif
    @endcan
</div>
@endsection
