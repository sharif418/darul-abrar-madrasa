@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Timetables</h1>
        @can('create', App\Models\Timetable::class)
            <a href="{{ route('timetables.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-plus mr-2"></i>Create Timetable
            </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('timetables.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="current" {{ request('status') == 'current' ? 'selected' : '' }}>Current</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name or description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('timetables.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Timetables Grid -->
    @if($timetables->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($timetables as $timetable)
                <div class="bg-white shadow-md rounded-lg overflow-hidden hover:shadow-lg transition duration-150">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h3 class="text-xl font-bold text-white">{{ $timetable->name }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if($timetable->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @endif
                            @if($timetable->isCurrent())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Current</span>
                            @endif
                            @if($timetable->isExpired())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Expired</span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-6 py-4">
                        @if($timetable->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $timetable->description }}</p>
                        @endif

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Effective From:</span>
                                <span class="font-medium text-gray-900">{{ $timetable->effective_from->format('M d, Y') }}</span>
                            </div>
                            @if($timetable->effective_to)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Effective To:</span>
                                    <span class="font-medium text-gray-900">{{ $timetable->effective_to->format('M d, Y') }}</span>
                                </div>
                            @else
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Effective To:</span>
                                    <span class="font-medium text-blue-600">Ongoing</span>
                                </div>
                            @endif
                        </div>

                        <!-- Statistics -->
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <div class="bg-blue-50 rounded p-2 text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $timetable->entries->count() }}</div>
                                <div class="text-xs text-blue-900">Entries</div>
                            </div>
                            <div class="bg-green-50 rounded p-2 text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $timetable->entries->pluck('class_id')->unique()->count() }}</div>
                                <div class="text-xs text-green-900">Classes</div>
                            </div>
                            <div class="bg-purple-50 rounded p-2 text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $timetable->entries->whereNotNull('teacher_id')->pluck('teacher_id')->unique()->count() }}</div>
                                <div class="text-xs text-purple-900">Teachers</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('timetables.show', $timetable) }}" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-3 rounded transition duration-150">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                            <a href="{{ route('timetables.weekly-grid', $timetable) }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-3 rounded transition duration-150">
                                <i class="fas fa-calendar mr-1"></i>Grid
                            </a>
                            @can('update', $timetable)
                                <a href="{{ route('timetables.edit', $timetable) }}" class="text-center bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold py-2 px-3 rounded transition duration-150">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('delete', $timetable)
                                @if($timetable->canBeDeleted())
                                    <form action="{{ route('timetables.destroy', $timetable) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this timetable?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-3 rounded transition duration-150">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($timetables->hasPages())
            <div class="mt-6">
                {{ $timetables->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="bg-white shadow-md rounded-lg p-12 text-center">
            <i class="fas fa-calendar-alt text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Timetables Found</h3>
            <p class="text-gray-500 mb-6">Create your first timetable to get started with schedule management.</p>
            @can('create', App\Models\Timetable::class)
                <a href="{{ route('timetables.create') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow transition duration-150">
                    <i class="fas fa-plus mr-2"></i>Create First Timetable
                </a>
            @endcan
        </div>
    @endif
</div>
@endsection
