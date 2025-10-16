@extends('layouts.app')

@section('title', 'Child Results')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">My Children</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Child Profile</a>
        <span class="mx-2">/</span>
        <span>Results</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Results - {{ $student->user->name ?? 'Student' }}</h1>
        <p class="text-gray-600 mt-1">Class: {{ $student->class->name ?? 'N/A' }} · Roll: {{ $student->roll_number ?? 'N/A' }}</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- GPA/Trend summary (placeholder) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @php
            $exams = collect($results ?? [])->groupBy('exam.name');
            $latestExam = $exams->keys()->last();
            $latestGpa = optional(optional($exams->last())->first())->gpa ?? null;
        @endphp
        <x-stat-card title="Latest Exam" value="{{ $latestExam ?? 'N/A' }}" icon="bookmark-square" color="indigo" />
        <x-stat-card title="Latest GPA" value="{{ $latestGpa !== null ? number_format((float)$latestGpa, 2) : 'N/A' }}" icon="academic-cap" color="green" />
        <x-stat-card title="Exams Taken" value="{{ number_format($exams->count()) }}" icon="clipboard-document-list" color="blue" />
    </div>

    {{-- Results grouped by exam --}}
    @forelse($exams as $examName => $rows)
        <x-card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $examName }}</h2>
                    @php
                        $gpa = optional($rows->first())->gpa;
                    @endphp
                    <p class="text-gray-600 text-sm">Overall GPA: {{ $gpa !== null ? number_format((float)$gpa, 2) : 'N/A' }}</p>
                </div>
                <div class="flex gap-3">
                    @php
                        $examId = optional($rows->first()->exam ?? null)->id;
                        $studentId = $student->id ?? null;
                    @endphp
                    @if($examId && $studentId)
                        <a href="{{ route('results.mark-sheet', ['exam' => $examId, 'student' => $studentId]) }}"
                           class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">
                            Download Mark Sheet
                        </a>
                    @endif
                </div>
            </div>

            <x-table>
                <x-slot name="head">
                    <tr>
                        <x-table.th>Subject</x-table.th>
                        <x-table.th class="text-right">Marks</x-table.th>
                        <x-table.th class="text-right">Grade</x-table.th>
                    </tr>
                </x-slot>
                <x-slot name="body">
                    @foreach($rows as $r)
                        <tr class="border-b">
                            <x-table.td>{{ $r->subject->name ?? 'N/A' }}</x-table.td>
                            <x-table.td class="text-right">{{ number_format((float)($r->marks ?? 0), 2) }}</x-table.td>
                            <x-table.td class="text-right">{{ $r->grade ?? '-' }}</x-table.td>
                        </tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-card>
    @empty
        <x-card>
            <div class="text-center text-gray-500 py-8">No results available.</div>
        </x-card>
    @endforelse

    {{-- Footer links --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Back to Profile</a>
        <span class="mx-2">&middot;</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">Back to Children</a>
    </div>
</div>
@endsection
