@extends('layouts.app')

@section('title', 'Child Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">My Children</a>
        <span class="mx-2">/</span>
        <span>Child Profile</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Child Profile</h1>
        <p class="text-gray-600 mt-1">Welcome, {{ $guardian->user->name ?? 'Guardian' }}. You are viewing the profile of {{ $student->user->name ?? 'Student' }}.</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Child summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-card class="lg:col-span-2">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-gray-800">{{ $student->user->name ?? 'N/A' }}</div>
                    <div class="text-gray-600 text-sm">Roll: {{ $student->roll_number ?? 'N/A' }}</div>
                    <div class="text-gray-600 text-sm">Class: {{ $student->class->name ?? 'N/A' }}</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('guardian.child.performance-report', $student) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Performance Report
                    </a>
                    <a href="{{ route('guardian.child.attendance', $student) }}" class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View Attendance</a>
                    <a href="{{ route('guardian.child.results', $student) }}" class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View Results</a>
                    <a href="{{ route('guardian.child.fees', $student) }}" class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View Fees</a>
                    <a href="{{ route('guardian.child.materials', $student) }}" class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">Study Materials</a>
                </div>
            </div>
        </x-card>

        {{-- Quick stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-4">
            <x-stat-card title="Class" value="{{ $student->class->name ?? 'N/A' }}" icon="academic-cap" color="indigo" />
            <x-stat-card title="Roll" value="{{ $student->roll_number ?? 'N/A' }}" icon="identification" color="blue" />
            <x-stat-card title="Status" value="{{ ucfirst($student->status ?? 'active') }}" icon="check-badge" color="{{ ($student->status ?? 'active') === 'active' ? 'green' : 'yellow' }}" />
        </div>
    </div>

    {{-- Details sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Personal Info --}}
        <x-card class="lg:col-span-1">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h2>
            <dl class="divide-y divide-gray-200">
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Name</dt>
                    <dd class="font-medium text-gray-900">{{ $student->user->name ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Email</dt>
                    <dd class="font-medium text-gray-900">{{ $student->user->email ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Phone</dt>
                    <dd class="font-medium text-gray-900">{{ $student->user->phone ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Gender</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst($student->gender ?? 'N/A') }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Date of Birth</dt>
                    <dd class="font-medium text-gray-900">{{ optional($student->date_of_birth)->format('Y-m-d') ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Address</dt>
                    <dd class="font-medium text-gray-900 text-right">{{ $student->address ?? 'N/A' }}</dd>
                </div>
            </dl>
        </x-card>

        {{-- Academic Info --}}
        <x-card class="lg:col-span-1">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Academic Information</h2>
            <dl class="divide-y divide-gray-200">
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Class</dt>
                    <dd class="font-medium text-gray-900">{{ $student->class->name ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Section</dt>
                    <dd class="font-medium text-gray-900">{{ $student->section ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Enrollment No.</dt>
                    <dd class="font-medium text-gray-900">{{ $student->enrollment_number ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Session</dt>
                    <dd class="font-medium text-gray-900">{{ $student->session ?? 'N/A' }}</dd>
                </div>
            </dl>
        </x-card>

        {{-- Guardian Info --}}
        <x-card class="lg:col-span-1">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Guardian Information</h2>
            <dl class="divide-y divide-gray-200">
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Primary Guardian</dt>
                    <dd class="font-medium text-gray-900">{{ $guardian->user->name ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Relationship</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst($guardian->relationship_type ?? 'guardian') }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Phone</dt>
                    <dd class="font-medium text-gray-900">{{ $guardian->phone ?? 'N/A' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-gray-600">Email</dt>
                    <dd class="font-medium text-gray-900">{{ $guardian->user->email ?? $guardian->email ?? 'N/A' }}</dd>
                </div>
                <div class="py-2">
                    <dt class="text-gray-600 mb-1">Address</dt>
                    <dd class="font-medium text-gray-900">{{ $guardian->address ?? 'N/A' }}</dd>
                </div>
            </dl>
        </x-card>
    </div>

    {{-- Quick actions --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('guardian.child.performance-report', $student) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            View Performance Report
        </a>
        <a href="{{ route('guardian.child.attendance', $student) }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View Attendance</a>
        <a href="{{ route('guardian.child.results', $student) }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View Results</a>
        <a href="{{ route('guardian.child.fees', $student) }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">View Fees</a>
        <a href="{{ route('guardian.children') }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">Back to Children</a>
    </div>
</div>
@endsection
