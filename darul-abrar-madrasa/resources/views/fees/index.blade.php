@extends('layouts.app')

@section('header', 'Fee Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Fee Management</h1>
        <div class="flex flex-wrap gap-2">
            <x-button href="{{ route('fees.create') }}" color="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New Fee
            </x-button>
            <x-button href="{{ route('fees.reports') }}" color="secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Fee Reports
            </x-button>
        </div>
    </div>

    <!-- Fee Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-stat-card 
            title="Total Fees" 
            value="{{ number_format($totalFees, 2) }} BDT" 
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>'
            color="blue"
        />
        
        <x-stat-card 
            title="Collected Fees" 
            value="{{ number_format($collectedFees, 2) }} BDT" 
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>'
            color="green"
            change="{{ number_format($collectionRate, 1) }}%"
            changeText="collection rate"
        />
        
        <x-stat-card 
            title="Pending Fees" 
            value="{{ number_format($pendingFees, 2) }} BDT" 
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>'
            color="yellow"
            change="{{ $pendingFeesCount }}"
            changeText="pending invoices"
        />
        
        <x-stat-card 
            title="Overdue Fees" 
            value="{{ number_format($overdueFees, 2) }} BDT" 
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>'
            color="red"
            change="{{ $overdueFeesCount }}"
            changeText="overdue invoices"
        />
    </div>

    <!-- Search and Filter -->
    <x-search-filter route="{{ route('fees.index') }}">
        <x-filter-input name="search" label="Search" placeholder="Search by student name or invoice number" width="half" />
        
        <x-filter-input 
            name="fee_type" 
            label="Fee Type" 
            type="select" 
            :options="$feeTypes" 
            placeholder="All Fee Types" 
            width="quarter"
        />
        
        <x-filter-input 
            name="status" 
            label="Payment Status" 
            type="select" 
            :options="[
                'paid' => 'Paid',
                'unpaid' => 'Unpaid',
                'partial' => 'Partially Paid',
                'overdue' => 'Overdue'
            ]" 
            placeholder="All Statuses" 
            width="quarter"
        />
        
        <x-filter-input 
            name="class_id" 
            label="Class" 
            type="select" 
            :options="$classes->pluck('name', 'id')->toArray()" 
            placeholder="All Classes" 
            width="quarter"
        />
        
        <x-filter-input name="date_from" label="From Date" type="date" width="quarter" />
        <x-filter-input name="date_to" label="To Date" type="date" width="quarter" />
    </x-search-filter>

    <!-- Fee List -->
    <x-card>
        <div class="overflow-x-auto">
            <x-table :headers="['Invoice #', 'Student', 'Fee Type', 'Amount', 'Due Date', 'Status', 'Actions']">
                @forelse($fees as $fee)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left">{{ $fee->invoice_number }}</td>
                        <td class="py-3 px-6 text-left">
                            <div class="flex items-center">
                                @if($fee->student->user->avatar)
                                    <div class="mr-2">
                                        <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $fee->student->user->avatar) }}" alt="{{ $fee->student->user->name }}">
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium">{{ $fee->student->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $fee->student->admission_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-6 text-left">{{ $fee->fee_type }}</td>
                        <td class="py-3 px-6 text-left">
                            <div class="font-medium">{{ number_format($fee->amount, 2) }} BDT</div>
                            @if($fee->status === 'partial')
                                <div class="text-xs text-gray-500">Paid: {{ number_format($fee->paid_amount, 2) }} BDT</div>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-left">
                            <div class="{{ $fee->due_date < now() && $fee->status !== 'paid' ? 'text-red-600 font-medium' : '' }}">
                                {{ $fee->due_date->format('d M, Y') }}
                            </div>
                            @if($fee->due_date < now() && $fee->status !== 'paid')
                                <div class="text-xs text-red-500">
                                    {{ $fee->due_date->diffForHumans() }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($fee->status === 'paid')
                                <x-badge color="green">Paid</x-badge>
                            @elseif($fee->status === 'partial')
                                <x-badge color="yellow">Partial</x-badge>
                            @elseif($fee->due_date < now())
                                <x-badge color="red">Overdue</x-badge>
                            @else
                                <x-badge color="gray">Unpaid</x-badge>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <a href="{{ route('fees.show', $fee->id) }}" class="w-4 mr-4 transform hover:text-blue-500 hover:scale-110 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                
                                @if($fee->status !== 'paid')
                                    <a href="{{ route('fees.edit', $fee->id) }}" class="w-4 mr-4 transform hover:text-yellow-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    
                                    <a href="{{ route('fees.payment', $fee->id) }}" class="w-4 mr-4 transform hover:text-green-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </a>
                                @endif
                                
                                <a href="{{ route('fees.invoice', $fee->id) }}" class="w-4 mr-4 transform hover:text-blue-500 hover:scale-110 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </a>
                                
                                <form action="{{ route('fees.destroy', $fee->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this fee record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-4 transform hover:text-red-500 hover:scale-110 transition-all">
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
                        <td colspan="7" class="py-6 px-6 text-center text-gray-500">No fee records found</td>
                    </tr>
                @endforelse
            </x-table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $fees->links() }}
        </div>
    </x-card>
</div>
@endsection