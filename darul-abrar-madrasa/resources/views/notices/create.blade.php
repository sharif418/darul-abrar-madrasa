@extends('layouts.app')

@section('header', 'Create Notice')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Create Notice</h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            <div class="font-semibold mb-2">Please fix the following errors:</div>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-card>
        <form method="POST" action="{{ route('notices.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notice For</label>
                    <select name="notice_for" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" {{ old('notice_for') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="students" {{ old('notice_for') === 'students' ? 'selected' : '' }}>Students</option>
                        <option value="teachers" {{ old('notice_for') === 'teachers' ? 'selected' : '' }}>Teachers</option>
                        <option value="staff" {{ old('notice_for') === 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                    <input type="date" name="publish_date" value="{{ old('publish_date', now()->toDateString()) }}" required
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date (optional)</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="6" required
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('notices.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Create Notice</button>
            </div>
        </form>
    </x-card>
</div>
@endsection
