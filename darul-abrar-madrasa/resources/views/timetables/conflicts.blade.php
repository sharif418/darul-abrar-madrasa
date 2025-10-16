@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Timetable Conflicts</h1>
        <div class="flex gap-2">
            <a href="{{ route('timetables.conflicts', $timetable) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-sync mr-2"></i>Refresh
            </a>
            <a href="{{ route('timetables.show', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6 border-l-4 {{ count($conflicts['teacherConflicts']) > 0 ? 'border-red-500' : 'border-green-500' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Teacher Conflicts</p>
                    <p class="text-3xl font-bold {{ count($conflicts['teacherConflicts']) > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">
                        {{ count($conflicts['teacherConflicts']) }}
                    </p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-user-times text-2xl text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border-l-4 {{ count($conflicts['roomConflicts']) > 0 ? 'border-orange-500' : 'border-green-500' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Room Conflicts</p>
                    <p class="text-3xl font-bold {{ count($conflicts['roomConflicts']) > 0 ? 'text-orange-600' : 'text-green-600' }} mt-2">
                        {{ count($conflicts['roomConflicts']) }}
                    </p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-door-closed text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border-l-4 {{ count($conflicts['classConflicts']) > 0 ? 'border-yellow-500' : 'border-green-500' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Class Conflicts</p>
                    <p class="text-3xl font-bold {{ count($conflicts['classConflicts']) > 0 ? 'text-yellow-600' : 'text-green-600' }} mt-2">
                        {{ count($conflicts['classConflicts']) }}
                    </p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-school text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    @if($conflicts['totalConflicts'] == 0)
        <!-- No Conflicts State -->
        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-12 text-center">
            <div class="bg-green-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-5xl text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-green-800 mb-2">No Conflicts Detected!</h2>
            <p class="text-green-700">Your timetable is perfectly scheduled with no overlapping assignments.</p>
        </div>
    @else
        <!-- Conflicts List -->
        <div class="space-y-6">
            <!-- Teacher Conflicts -->
            @if(count($conflicts['teacherConflicts']) > 0)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-red-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-user-times mr-2"></i>
                            Teacher Conflicts ({{ count($conflicts['teacherConflicts']) }})
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($conflicts['teacherConflicts'] as $conflict)
                            <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-red-900 mb-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $conflict['entry']->teacher->user->name }} - Double Booked
                                        </h3>
                                        <p class="text-sm text-red-800 mb-2">
                                            <strong>{{ ucfirst($conflict['entry']->day_of_week) }}</strong> - 
                                            {{ $conflict['entry']->period->name }} ({{ $conflict['entry']->period->getFormattedTimeRange() }})
                                        </p>
                                        <div class="text-sm text-red-700">
                                            <p><strong>Conflicting Classes:</strong></p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>{{ $conflict['entry']->class->name }} - {{ $conflict['entry']->subject->name }}</li>
                                                @foreach($conflict['conflicts_with'] as $conflictEntry)
                                                    <li>{{ $conflictEntry->class->name }} - {{ $conflictEntry->subject->name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    @can('updateEntry', [$timetable, $conflict['entry']])
                                        <a href="{{ route('timetables.entries.edit', [$timetable, $conflict['entry']]) }}" class="ml-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded transition duration-150">
                                            <i class="fas fa-edit mr-1"></i>Fix
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Room Conflicts -->
            @if(count($conflicts['roomConflicts']) > 0)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-orange-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-door-closed mr-2"></i>
                            Room Conflicts ({{ count($conflicts['roomConflicts']) }})
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($conflicts['roomConflicts'] as $conflict)
                            <div class="border-l-4 border-orange-500 bg-orange-50 p-4 rounded">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-orange-900 mb-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Room {{ $conflict['entry']->room_number }} - Double Booked
                                        </h3>
                                        <p class="text-sm text-orange-800 mb-2">
                                            <strong>{{ ucfirst($conflict['entry']->day_of_week) }}</strong> - 
                                            {{ $conflict['entry']->period->name }} ({{ $conflict['entry']->period->getFormattedTimeRange() }})
                                        </p>
                                        <div class="text-sm text-orange-700">
                                            <p><strong>Conflicting Classes:</strong></p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>{{ $conflict['entry']->class->name }}</li>
                                                @foreach($conflict['conflicts_with'] as $conflictEntry)
                                                    <li>{{ $conflictEntry->class->name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    @can('updateEntry', [$timetable, $conflict['entry']])
                                        <a href="{{ route('timetables.entries.edit', [$timetable, $conflict['entry']]) }}" class="ml-4 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold py-2 px-4 rounded transition duration-150">
                                            <i class="fas fa-edit mr-1"></i>Fix
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Class Conflicts -->
            @if(count($conflicts['classConflicts']) > 0)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-yellow-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-school mr-2"></i>
                            Class Conflicts ({{ count($conflicts['classConflicts']) }})
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($conflicts['classConflicts'] as $conflict)
                            <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-yellow-900 mb-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $conflict['entry']->class->name }} - Multiple Subjects Same Time
                                        </h3>
                                        <p class="text-sm text-yellow-800 mb-2">
                                            <strong>{{ ucfirst($conflict['entry']->day_of_week) }}</strong> - 
                                            {{ $conflict['entry']->period->name }} ({{ $conflict['entry']->period->getFormattedTimeRange() }})
                                        </p>
                                        <div class="text-sm text-yellow-700">
                                            <p><strong>Conflicting Subjects:</strong></p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>{{ $conflict['entry']->subject->name }}</li>
                                                @foreach($conflict['conflicts_with'] as $conflictEntry)
                                                    <li>{{ $conflictEntry->subject->name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    @can('updateEntry', [$timetable, $conflict['entry']])
                                        <a href="{{ route('timetables.entries.edit', [$timetable, $conflict['entry']]) }}" class="ml-4 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold py-2 px-4 rounded transition duration-150">
                                            <i class="fas fa-edit mr-1"></i>Fix
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
