@extends('layouts.app')

@section('title', 'Assign Subject - ' . $class->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="assignSubjectPage()">
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('classes.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                    </svg>
                    Classes
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707A1 1 0 118.707 5.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd" />
                    </svg>
                    <a href="{{ route('classes.show', $class) }}" class="text-gray-500 hover:text-gray-700">{{ $class->name }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707A1 1 0 118.707 5.293l4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-700 font-medium">Assign Subject</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Class info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Class Information</h2>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $class->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $class->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-700">
            <div>
                <div class="text-gray-500">Class Name</div>
                <div class="font-medium">{{ $class->name }}</div>
            </div>
            <div>
                <div class="text-gray-500">Department</div>
                <div class="font-medium">{{ optional($class->department)->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-gray-500">Subjects</div>
                <div class="font-medium">{{ $class->subjects()->count() }}</div>
            </div>
            <div class="md:col-span-1 text-right">
                <a href="{{ route('classes.show', $class) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Back to Class
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 pt-4 border-b border-gray-200">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button type="button"
                        :class="tab === 'existing' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm"
                        @click="tab = 'existing'">
                    Assign Existing Subject
                </button>
                <button type="button"
                        :class="tab === 'create' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm"
                        @click="tab = 'create'">
                    Create New Subject
                </button>
            </nav>
        </div>

        <!-- Assign existing subject -->
        <div class="px-6 py-6" x-show="tab === 'existing'" x-cloak>
            <form method="POST" action="{{ route('classes.assign-subject', $class) }}">
                @csrf
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-semibold text-gray-800">Available Subjects</h3>
                    <div class="relative">
                        <input type="text"
                               x-model="searchExisting"
                               placeholder="Search subjects..."
                               class="block w-64 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <div class="absolute right-2 top-2.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1016.65 16.65z" />
                            </svg>
                        </div>
                    </div>
                </div>

                @error('subject_id')
                    <div class="mb-3 rounded-md bg-red-50 p-3 border border-red-200 text-red-700 text-sm">
                        {{ $message }}
                    </div>
                @enderror

                <div class="-mx-4 -my-2 overflow-x-auto">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Select</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Class</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Mark</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($availableSubjects as $subject)
                                        <tr x-show="matchesExisting('{{ strtolower($subject->name) }}', '{{ strtolower($subject->code) }}', '{{ strtolower(optional($subject->class)->name ?? '') }}', '{{ strtolower(optional($subject->teacher)->user->name ?? '') }}')">
                                            <td class="px-4 py-3">
                                                <input type="radio"
                                                       name="subject_id"
                                                       value="{{ $subject->id }}"
                                                       x-model="selectedExisting"
                                                       class="text-indigo-600 border-gray-300 focus:ring-indigo-500" />
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $subject->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $subject->code }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ optional($subject->class)->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ optional($subject->teacher)->user->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $subject->full_mark }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                                No subjects available for assignment.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-end space-x-3">
                            <a href="{{ route('classes.show', $class) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit"
                                    :disabled="!selectedExisting"
                                    :class="{'opacity-50 cursor-not-allowed': !selectedExisting}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                Assign Subject
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Create new subject -->
        <div class="px-6 py-6" x-show="tab === 'create'" x-cloak>
            <form method="POST" action="{{ route('classes.assign-subject', $class) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Code <span class="text-red-600">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teacher <span class="text-red-600">*</span></label>
                        <select name="teacher_id" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('teacher_id')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Mark <span class="text-red-600">*</span></label>
                        <input type="number" name="full_mark" min="1" value="{{ old('full_mark') }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('full_mark')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pass Mark <span class="text-red-600">*</span></label>
                        <input type="number" name="pass_mark" min="1" value="{{ old('pass_mark') }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('pass_mark')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('classes.show', $class) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Create &amp; Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function assignSubjectPage() {
        return {
            tab: 'existing',
            searchExisting: '',
            selectedExisting: null,
            matchesExisting(name, code, className, teacher) {
                if (!this.searchExisting) return true;
                const q = this.searchExisting.toLowerCase();
                return name.includes(q) || code.includes(q) || className.includes(q) || teacher.includes(q);
            },
        };
    }
</script>
@endsection
