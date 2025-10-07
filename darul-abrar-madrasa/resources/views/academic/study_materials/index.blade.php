@extends('layouts.app')

@section('header', 'Study Materials')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Study Materials</h1>
        <x-button href="{{ route('study-materials.create') }}" color="primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Upload Material
        </x-button>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filter Materials</h2>
        <form action="{{ route('study-materials.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <x-label for="content_type" value="Material Type" />
                <x-select id="content_type" name="content_type" class="block mt-1 w-full">
                    <option value="">All Types</option>
                    @foreach($contentTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('content_type') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="class_id" value="Class" />
                <x-select id="class_id" name="class_id" class="block mt-1 w-full">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="subject_id" value="Subject" />
                <x-select id="subject_id" name="subject_id" class="block mt-1 w-full">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-label for="is_published" value="Status" />
                <x-select id="is_published" name="is_published" class="block mt-1 w-full">
                    <option value="">All</option>
                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft</option>
                </x-select>
            </div>
            
            <div class="flex items-end">
                <x-button type="submit" color="primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Apply Filters
                </x-button>
                <a href="{{ route('study-materials.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Study Materials Grid -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Study Materials</h2>
        </div>
        
        @if($studyMaterials->isEmpty())
            <div class="p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-gray-500">No study materials found.</p>
                <div class="mt-4">
                    <x-button href="{{ route('study-materials.create') }}" color="primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Upload First Material
                    </x-button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @foreach($studyMaterials as $material)
                    <div class="bg-white border rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="p-4 border-b bg-gray-50">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-gray-800 truncate" title="{{ $material->title }}">
                                    {{ $material->title }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    @if($material->is_published)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Published
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Draft
                                        </span>
                                    @endif
                                    @if(auth()->user()->isAdmin() || (isset($material->teacher) && $material->teacher->user && $material->teacher->user->id === auth()->id()))
                                        <form method="POST" action="{{ route('study-materials.toggle-published', $material) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-50">
                                                {{ $material->is_published ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                <span class="truncate">{{ $material->class->name }} | {{ $material->subject->name }}</span>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <div class="flex items-center mb-3">
                                <div class="bg-blue-100 p-2 rounded-full mr-3">
                                    @if($material->content_type == 'note')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @elseif($material->content_type == 'suggestion')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    @elseif($material->content_type == 'video_link')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    @elseif($material->content_type == 'document')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    @elseif($material->content_type == 'image')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">{{ $contentTypes[$material->content_type] }}</p>
                                    <p class="text-xs text-gray-500">{{ $material->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ $material->description ?: 'No description provided.' }}
                            </p>
                            
                            <div class="flex justify-between items-center">
                                <div class="text-xs text-gray-500">
                                    By: {{ $material->teacher->user->name }}
                                </div>
                                <div class="flex space-x-2 items-center">
                                    <a href="{{ route('study-materials.show', $material->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if($material->file_path)
                                        <a href="{{ route('study-materials.download', $material->id) }}" class="text-green-600 hover:text-green-800" title="Download">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isAdmin() || (isset($material->teacher) && $material->teacher->user && $material->teacher->user->id === auth()->id()))
                                        <a href="{{ route('study-materials.edit', $material) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <div class="inline">
                                            <button type="button" class="text-red-600 hover:text-red-800" title="Delete" @click.prevent="window.dispatchEvent(new CustomEvent('open-delete-material-{{ $material->id }}'))">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <x-confirm-delete-modal
                                                openEvent="open-delete-material-{{ $material->id }}"
                                                title="Delete Material"
                                                message="Are you sure you want to delete this material?"
                                                confirmText="Delete"
                                                cancelText="Cancel"
                                                confirmButtonColor="red"
                                                formAction="{{ route('study-materials.destroy', $material) }}"
                                                formMethod="DELETE"
                                            />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="px-6 py-4 border-t">
                {{ $studyMaterials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection