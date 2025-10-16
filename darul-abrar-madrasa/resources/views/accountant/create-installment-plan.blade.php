@extends('layouts.app')

@section('title', 'Create Installment Plan')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('accountant.dashboard') }}" class="text-indigo-600 hover:underline">Accountant Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('accountant.installments') }}" class="text-indigo-600 hover:underline">Installments</a>
        <span class="mx-2">/</span>
        <span>Create Plan</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Create Installment Plan</h1>
        <p class="text-gray-600 mt-1">Fee: <span class="font-medium">#{{ $fee->id }}</span> ·
            Student: <span class="font-medium">{{ $fee->student->user->name ?? 'N/A' }}</span> ·
            Type: <span class="font-medium">{{ ucfirst($fee->fee_type) }}</span> ·
            Amount: <span class="font-medium">{{ number_format((float)$fee->amount, 2) }}</span>
        </p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="mb-4">
            <x-alert type="error">
                <div class="font-semibold">Please fix the following:</div>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        </div>
    @endif

    {{-- Card --}}
    <x-card>
        <form method="POST" action="{{ route('accountant.installments.store', $fee) }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Number of installments --}}
                <div>
                    <x-label for="number_of_installments" value="Number of Installments" />
                    <x-input id="number_of_installments" name="number_of_installments" type="number" min="1" step="1"
                             value="{{ old('number_of_installments', 3) }}" class="mt-1 w-full" required />
                    <x-input-error for="number_of_installments" class="mt-1" />
                </div>

                {{-- Start date --}}
                <div>
                    <x-label for="start_date" value="Start Date" />
                    <x-input id="start_date" name="start_date" type="date"
                             value="{{ old('start_date', now()->toDateString()) }}" class="mt-1 w-full" required />
                    <x-input-error for="start_date" class="mt-1" />
                </div>

                {{-- Frequency --}}
                <div>
                    <x-label for="frequency" value="Frequency" />
                    <x-select id="frequency" name="frequency" class="mt-1 w-full">
                        @php
                            $freq = old('frequency', 'monthly');
                        @endphp
                        <option value="weekly" {{ $freq === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="biweekly" {{ $freq === 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                        <option value="monthly" {{ $freq === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </x-select>
                    <x-input-error for="frequency" class="mt-1" />
                </div>
            </div>

            {{-- Preview helper (static guidance) --}}
            <div class="bg-gray-50 border border-gray-200 rounded p-4">
                <div class="text-sm text-gray-700">
                    The plan will evenly distribute the fee amount across the selected number of installments, starting from the given date,
                    using the chosen frequency. You can adjust individual installment amounts later if needed.
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('accountant.installments') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <x-button type="submit" class="inline-flex items-center">
                    Create Plan
                </x-button>
            </div>
        </form>
    </x-card>

    {{-- Quick links --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('accountant.fees') }}" class="text-indigo-600 hover:underline">Back to Fees</a>
        <span class="mx-2">&middot;</span>
        <a href="{{ route('accountant.dashboard') }}" class="text-indigo-600 hover:underline">Back to Dashboard</a>
    </div>
</div>
@endsection
