@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Teachers</h1>
        <a href="{{ route('teachers.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Register New Teacher
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('teachers.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-grow md:flex-grow-0">
                <input type="text" name="search" placeholder="Search by name, email, or designation" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="{{ request('search') }}">
            </div>
            
            <div class="w-full md:w-auto">
                <select name="department_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <select name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Filter
                </button>
            </div>
            
            <div class="w-full md:w-auto">
                <a href="{{ route('teachers.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Teachers List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Department</th>
                        <th class="py-3 px-6 text-left">Designation</th>
                        <th class="py-3 px-6 text-left">Contact</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($teachers as $teacher)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $teacher->id }}</td>
                            <td class="py-3 px-6 text-left">
                                <div class="flex items-center">
                                    @if($teacher->user->avatar)
                                        <div class="mr-2">
                                            <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $teacher->user->avatar) }}" alt="{{ $teacher->user->name }}">
                                        </div>
                                    @endif
                                    <span>{{ $teacher->user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-6 text-left">{{ $teacher->department->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $teacher->designation }}</td>
                            <td class="py-3 px-6 text-left">
                                <div>{{ $teacher->user->email }}</div>
                                <div class="text-xs text-gray-500">{{ $teacher->phone }}</div>
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($teacher->is_active)
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                                @else
                                    <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="{{ route('teachers.show', $teacher->id) }}" class="w-4 mr-2 transform hover:text-blue-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('teachers.edit', $teacher->id) }}" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 px-6 text-center text-gray-500">No teachers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4">
            {{ $teachers->links() }}
        </div>
    </div>
</div>
@endsection