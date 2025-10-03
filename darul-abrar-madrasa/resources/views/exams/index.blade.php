@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Exams</h1>
        <a href="{{ route('exams.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Create New Exam
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('exams.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-grow md:flex-grow-0">
                <input type="text" name="search" placeholder="Search by exam name" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="{{ request('search') }}">
            </div>
            
            <div class="w-full md:w-auto">
                <select name="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->department->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <select name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Status</option>
                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <select name="result_status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Result Status</option>
                    <option value="published" {{ request('result_status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="unpublished" {{ request('result_status') === 'unpublished' ? 'selected' : '' }}>Unpublished</option>
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Filter
                </button>
            </div>
            
            <div class="w-full md:w-auto">
                <a href="{{ route('exams.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Exams List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Class</th>
                        <th class="py-3 px-6 text-left">Schedule</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Results</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($exams as $exam)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $exam->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $exam->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $exam->class->name }}</td>
                            <td class="py-3 px-6 text-left">
                                <div>{{ $exam->start_date->format('d M, Y') }}</div>
                                <div class="text-xs text-gray-500">to {{ $exam->end_date->format('d M, Y') }}</div>
                            </td>
                            <td class="py-3 px-6 text-center">
                                @php
                                    $now = now();
                                    $status = 'upcoming';
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    $statusText = 'Upcoming';
                                    
                                    if ($now->between($exam->start_date, $exam->end_date)) {
                                        $status = 'ongoing';
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        $statusText = 'Ongoing';
                                    } elseif ($now->gt($exam->end_date)) {
                                        $status = 'completed';
                                        $statusClass = 'bg-green-100 text-green-800';
                                        $statusText = 'Completed';
                                    }
                                @endphp
                                
                                <span class="{{ $statusClass }} py-1 px-3 rounded-full text-xs">{{ $statusText }}</span>
                                
                                @if(!$exam->is_active)
                                    <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs ml-1">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($exam->is_result_published)
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Published</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">Not Published</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="{{ route('exams.show', $exam->id) }}" class="w-4 mr-2 transform hover:text-blue-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('exams.edit', $exam->id) }}" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this exam?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    
                                    @if($status === 'completed' && !$exam->is_result_published)
                                        <a href="{{ route('results.create.bulk', ['exam_id' => $exam->id, 'class_id' => $exam->class_id, 'subject_id' => 1]) }}" class="w-4 mr-2 transform hover:text-green-500 hover:scale-110 transition-all" title="Enter Results">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 px-6 text-center text-gray-500">No exams found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4">
            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection