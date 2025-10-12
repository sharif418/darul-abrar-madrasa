@extends('layouts.app')

@section('title', 'Study Materials')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">My Children</a>
        <span class="mx-2">/</span>
        @if(isset($student))
            <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">{{ $student->user->name ?? 'Child' }}</a>
            <span class="mx-2">/</span>
        @endif
        <span>Study Materials</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Study Materials</h1>
        @if(isset($student))
            <p class="text-gray-600 mt-1">Child: {{ $student->user->name ?? 'N/A' }} · Class: {{ $student->class->name ?? 'N/A' }}</p>
        @endif
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Filters --}}
    <x-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-label for="content_type" value="Content Type" />
                @php $contentType = request('content_type'); @endphp
                <x-select id="content_type" name="content_type" class="mt-1 w-full">
                    <option value="" {{ $contentType === null || $contentType === '' ? 'selected' : '' }}>All Types</option>
                    <option value="note" {{ $contentType === 'note' ? 'selected' : '' }}>Class Notes</option>
                    <option value="suggestion" {{ $contentType === 'suggestion' ? 'selected' : '' }}>Exam Suggestions</option>
                    <option value="video_link" {{ $contentType === 'video_link' ? 'selected' : '' }}>Video Links</option>
                    <option value="document" {{ $contentType === 'document' ? 'selected' : '' }}>Documents</option>
                    <option value="image" {{ $contentType === 'image' ? 'selected' : '' }}>Images</option>
                    <option value="other" {{ $contentType === 'other' ? 'selected' : '' }}>Other</option>
                </x-select>
            </div>

            <div>
                <x-label for="subject_id" value="Subject (ID)" />
                <x-input id="subject_id" name="subject_id" type="number" min="1" value="{{ request('subject_id') }}" class="mt-1 w-full" placeholder="Subject ID" />
            </div>

            <div class="md:col-span-2 flex items-end gap-3">
                <x-button type="submit">Apply Filters</x-button>
                <a href="{{ url()->current() }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </x-card>

    {{-- Materials list --}}
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Available Materials</h2>
            <div class="text-sm text-gray-600">
                @if(isset($studyMaterials) && method_exists($studyMaterials, 'total'))
                    {{ number_format($studyMaterials->total()) }} item(s)
                @else
                    {{ number_format(($studyMaterials ?? collect())->count()) }} item(s)
                @endif
            </div>
        </div>

        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table.th>Title</x-table.th>
                    <x-table.th>Type</x-table.th>
                    <x-table.th>Subject</x-table.th>
                    <x-table.th>Teacher</x-table.th>
                    <x-table.th>Published</x-table.th>
                    <x-table.th>Actions</x-table.th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse(($studyMaterials ?? []) as $m)
                    <tr class="border-b">
                        <x-table.td class="font-medium text-gray-900">{{ $m->title ?? '-' }}</x-table.td>
                        <x-table.td>{{ ucfirst(str_replace('_',' ', $m->content_type ?? '-')) }}</x-table.td>
                        <x-table.td>{{ optional($m->subject)->name ?? '-' }}</x-table.td>
                        <x-table.td>{{ optional(optional($m->teacher)->user)->name ?? '-' }}</x-table.td>
                        <x-table.td>
                            @if(!empty($m->is_published))
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Published</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Draft</span>
                            @endif
                        </x-table.td>
                        <x-table.td>
                            <div class="flex flex-wrap gap-2">
                                @if(!empty($m->file_path))
                                    <a href="{{ route('study-materials.download', $m) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Download
                                    </a>
                                @elseif(($m->content_type ?? '') === 'video_link' && !empty($m->description))
                                    <a href="{{ $m->description }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">
                                        Open Link
                                    </a>
                                @else
                                    <span class="text-sm text-gray-500">N/A</span>
                                @endif
                            </div>
                        </x-table.td>
                    </tr>
                @empty
                    <tr>
                        <x-table.td colspan="6">
                            <div class="text-center text-gray-500 py-8">No study materials found for the selected filters.</div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>

        {{-- Pagination --}}
        @if(isset($studyMaterials) && method_exists($studyMaterials, 'links'))
            <div class="mt-4">
                {{ $studyMaterials->withQueryString()->links() }}
            </div>
        @endif
    </x-card>

    {{-- Footer links --}}
    <div class="mt-6 text-sm text-gray-600">
        @if(isset($student))
            <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Back to Profile</a>
            <span class="mx-2">&middot;</span>
        @endif
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">Back to My Children</a>
    </div>
</div>
@endsection
