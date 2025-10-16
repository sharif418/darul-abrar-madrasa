@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $timetable->name }}</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($timetable->is_active)
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Active
                    </span>
                @endif
                @if($timetable->isCurrent())
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        <i class="fas fa-calendar-check mr-1"></i>Current
                    </span>
                @endif
                @if($timetable->isExpired())
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        <i class="fas fa-history mr-1"></i>Expired
                    </span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            @can('update', $timetable)
                <a href="{{ route('timetables.edit', $timetable) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            @endcan
            @can('delete', $timetable)
                @if($timetable->canBeDeleted())
                    <form action="{{ route('timetables.destroy', $timetable) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this timetable?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                @endif
            @endcan
            <a href="{{ route('timetables.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Timetable Info Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Timetable Information</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $timetable->name }}</dd>
            </div>
            @if($timetable->description)
                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $timetable->description }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Effective From</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $timetable->effective_from->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Effective To</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $timetable->effective_to ? $timetable->effective_to->format('M d, Y') : 'Ongoing' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $timetable->creator->name ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $timetable->created_at->format('M d, Y') }}</dd>
            </div>
        </dl>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Entries</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_entries'] }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-list text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Classes Covered</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['classes_count'] }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-school text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Teachers Assigned</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['teachers_count'] }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-chalkboard-teacher text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Unassigned</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['entries_without_teacher'] }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('timetables.weekly-grid', $timetable) }}" class="flex items-center p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition duration-150 shadow-md">
                <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                    <i class="fas fa-calendar-week text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">View Weekly Grid</h3>
                    <p class="text-indigo-100 text-sm">Calendar-like timetable view</p>
                </div>
            </a>

            @can('createEntry', $timetable)
                <a href="{{ route('timetables.entries', $timetable) }}" class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition duration-150 shadow-md">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-tasks text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Manage Entries</h3>
                        <p class="text-green-100 text-sm">Add, edit, or remove entries</p>
                    </div>
                </a>
            @endcan

            <a href="{{ route('timetables.conflicts', $timetable) }}" class="flex items-center p-4 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition duration-150 shadow-md">
                <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Check Conflicts</h3>
                    <p class="text-red-100 text-sm">Detect scheduling conflicts</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Entries -->
    @if($timetable->entries->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Recent Entries</h2>
                @can('createEntry', $timetable)
                    <a href="{{ route('timetables.entries', $timetable) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                @endcan
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($timetable->entries->take(10) as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ $entry->day_of_week }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->period->name ?? 'N/A' }}
                                    <span class="text-xs text-gray-500 block">{{ $entry->period?->getFormattedTimeRange() }}</span>
                                </td>
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
    @else
        <div class="bg-white shadow-md rounded-lg p-12 text-center">
            <i class="fas fa-calendar-plus text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Entries Yet</h3>
            <p class="text-gray-500 mb-6">Start adding entries to build your timetable schedule.</p>
            @can('createEntry', $timetable)
                <a href="{{ route('timetables.entries.create', $timetable) }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-plus mr-2"></i>Add First Entry
                </a>
            @endcan
        </div>
    @endif
</div>
@endsection
