@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm mb-4">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('timetables.index') }}" class="text-blue-600 hover:text-blue-800">Timetables</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
            </li>
            <li class="flex items-center">
                <a href="{{ route('timetables.show', $timetable) }}" class="text-blue-600 hover:text-blue-800">{{ $timetable->name }}</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
            </li>
            <li class="text-gray-500">Entries</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Timetable Entries</h1>
        @can('createEntry', $timetable)
            <a href="{{ route('timetables.entries.create', $timetable) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-plus mr-2"></i>Add Entry
            </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('timetables.entries', $timetable) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Class Filter -->
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select name="class_id" id="class_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Teacher Filter -->
            <div>
                <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-2">Teacher</label>
                <select name="teacher_id" id="teacher_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Day Filter -->
            <div>
                <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-2">Day</label>
                <select name="day_of_week" id="day_of_week" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Days</option>
                    <option value="monday" {{ request('day_of_week') == 'monday' ? 'selected' : '' }}>Monday</option>
                    <option value="tuesday" {{ request('day_of_week') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                    <option value="wednesday" {{ request('day_of_week') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                    <option value="thursday" {{ request('day_of_week') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                    <option value="friday" {{ request('day_of_week') == 'friday' ? 'selected' : '' }}>Friday</option>
                    <option value="saturday" {{ request('day_of_week') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                    <option value="sunday" {{ request('day_of_week') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('timetables.entries', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Entries Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($entries->count() > 0)
            <div class="overflow-x-auto">
                @php
                    $groupedEntries = $entries->groupBy('day_of_week');
                    $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    $dayColors = [
                        'monday' => 'bg-blue-50',
                        'tuesday' => 'bg-green-50',
                        'wednesday' => 'bg-yellow-50',
                        'thursday' => 'bg-purple-50',
                        'friday' => 'bg-pink-50',
                        'saturday' => 'bg-indigo-50',
                        'sunday' => 'bg-red-50'
                    ];
                @endphp

                @foreach($daysOrder as $day)
                    @if(isset($groupedEntries[$day]) && $groupedEntries[$day]->count() > 0)
                        <div class="border-b border-gray-200">
                            <h3 class="bg-gray-100 px-6 py-3 text-lg font-semibold text-gray-800 capitalize flex items-center">
                                <i class="fas fa-calendar-day mr-2 text-blue-600"></i>{{ $day }}
                                <span class="ml-auto text-sm text-gray-600">{{ $groupedEntries[$day]->count() }} entries</span>
                            </h3>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($groupedEntries[$day] as $entry)
                                        <tr class="hover:{{ $dayColors[$day] ?? 'bg-gray-50' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="font-medium">{{ $entry->period->name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500">{{ $entry->period?->getFormattedTimeRange() }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $entry->class->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->subject->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->teacher->user->name ?? 'Not Assigned' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->room_number ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($entry->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    @can('updateEntry', [$timetable, $entry])
                                                        <a href="{{ route('timetables.entries.edit', [$timetable, $entry]) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('deleteEntry', [$timetable, $entry])
                                                        <form action="{{ route('timetables.entries.destroy', [$timetable, $entry]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Pagination -->
            @if($entries->hasPages())
                <div class="px-6 py-4 bg-gray-50">
                    {{ $entries->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <i class="fas fa-calendar-times text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No entries found.</p>
                @can('createEntry', $timetable)
                    <a href="{{ route('timetables.entries.create', $timetable) }}" class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                        <i class="fas fa-plus mr-2"></i>Add First Entry
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
