@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Class Management</h1>
            <p class="text-gray-600 mt-1">Manage academic classes</p>
        </div>
        <a href="{{ route('classes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Class
        </a>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('classes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Class name, section..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex-1">
                    Filter
                </button>
                <a href="{{ route('classes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classes as $class)
        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $class->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $class->department->name }}</p>
                </div>
                @if($class->is_active)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                @endif
            </div>

            <div class="space-y-2 mb-4">
                @if($class->section)
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span>Section: {{ $class->section }}</span>
                </div>
                @endif

                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>{{ $class->students_count }} Students / {{ $class->capacity }} Capacity</span>
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span>{{ $class->subjects_count }} Subjects</span>
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t">
                <a href="{{ route('classes.show', $class) }}" class="flex-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-2 rounded text-sm font-medium">
                    View Details
                </a>
                <a href="{{ route('classes.edit', $class) }}" class="text-indigo-600 hover:text-indigo-900 p-2" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
                <form action="{{ route('classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900 p-2" title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <p class="mt-2">No classes found</p>
            <a href="{{ route('classes.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                Create your first class
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($classes->hasPages())
    <div class="mt-6">
        {{ $classes->links() }}
    </div>
    @endif

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">Total Classes</div>
            <div class="text-2xl font-bold text-gray-900">{{ $classes->total() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">Active Classes</div>
            <div class="text-2xl font-bold text-green-600">{{ \App\Models\ClassRoom::where('is_active', true)->count() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">Total Students</div>
            <div class="text-2xl font-bold text-blue-600">{{ \App\Models\Student::count() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">Total Subjects</div>
            <div class="text-2xl font-bold text-purple-600">{{ \App\Models\Subject::count() }}</div>
        </div>
    </div>
</div>
@endsection
