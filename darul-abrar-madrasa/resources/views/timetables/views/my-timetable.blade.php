@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">My Teaching Schedule</h1>
            @if($timetable)
                <p class="text-gray-600 mt-1">{{ $timetable->name }}</p>
            @endif
        </div>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150 print:hidden">
            <i class="fas fa-print mr-2"></i>Print Schedule
        </button>
    </div>

    @if(!$timetable)
        <!-- No Timetable State -->
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-12 text-center">
            <i class="fas fa-calendar-times text-yellow-400 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-yellow-800 mb-2">No Active Timetable</h2>
            <p class="text-yellow-700">There is currently no active timetable available. Please contact the administrator.</p>
        </div>
    @elseif(!$schedule || count($schedule['schedule']) == 0)
        <!-- No Schedule State -->
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-12 text-center">
            <i class="fas fa-calendar-plus text-blue-400 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-blue-800 mb-2">No Classes Assigned</h2>
            <p class="text-blue-700">You don't have any classes assigned in the current timetable yet.</p>
        </div>
    @else
        <!-- Welcome Message -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white mb-6 print:hidden">
            <h2 class="text-2xl font-bold mb-2">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="text-indigo-100">Here's your complete teaching schedule for the week.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white shadow-md rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Periods/Week</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $schedule['stats']['total_periods'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-clock text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Classes Teaching</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $schedule['stats']['classes_taught'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-school text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Subjects Teaching</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ $schedule['stats']['subjects_taught'] }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-book text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Schedule Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
                <h2 class="text-xl font-semibold text-white">Weekly Teaching Schedule</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Period</th>
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allPeriods = collect($schedule['schedule'])->flatten(1)->unique('period_id')->sortBy(function($entry) {
                                return $entry->period->order ?? 999;
                            });
                        @endphp
                        @foreach($allPeriods as $periodEntry)
                            <tr class="hover:bg-indigo-50">
                                <td class="border border-gray-300 px-4 py-3 bg-gray-50 font-medium text-sm">
                                    <div class="font-semibold">{{ $periodEntry->period->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $periodEntry->period->getFormattedTimeRange() }}</div>
                                </td>
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <td class="border border-gray-300 px-2 py-2 text-xs">
                                        @php
                                            $dayEntry = isset($schedule['schedule'][$day]) 
                                                ? collect($schedule['schedule'][$day])->firstWhere('period_id', $periodEntry->period_id)
                                                : null;
                                        @endphp
                                        @if($dayEntry)
                                            <div class="bg-gradient-to-br from-indigo-100 to-purple-100 border border-indigo-300 rounded p-2">
                                                <div class="font-bold text-indigo-900">{{ $dayEntry->class->name ?? 'N/A' }}</div>
                                                <div class="text-indigo-700 mt-1">{{ $dayEntry->subject->name ?? 'N/A' }}</div>
                                                @if($dayEntry->room_number)
                                                    <div class="text-indigo-600 mt-1">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $dayEntry->room_number }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-gray-400 text-center py-3 bg-gray-50 rounded">
                                                <i class="fas fa-coffee"></i>
                                                <div class="text-xs mt-1">Free</div>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Classes Summary -->
        <div class="mt-6 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">My Classes</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $classSummary = collect($schedule['schedule'])->flatten(1)->groupBy('class_id');
                @endphp
                @foreach($classSummary as $classId => $entries)
                    @php
                        $firstEntry = $entries->first();
                    @endphp
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-4 hover:shadow-md transition duration-150">
                        <h3 class="font-semibold text-indigo-900 text-lg">{{ $firstEntry->class->name ?? 'N/A' }}</h3>
                        <p class="text-sm text-indigo-700 mt-2">
                            <i class="fas fa-book mr-1"></i>{{ $firstEntry->subject->name ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-indigo-600 mt-1">
                            <i class="fas fa-clock mr-1"></i>{{ $entries->count() }} periods per week
                        </p>
                        @if($firstEntry->room_number)
                            <p class="text-sm text-indigo-600 mt-1">
                                <i class="fas fa-door-open mr-1"></i>{{ $firstEntry->room_number }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .print\\:hidden {
        display: none !important;
    }
    table {
        font-size: 10px;
    }
    th, td {
        padding: 4px !important;
    }
}
</style>
@endsection
