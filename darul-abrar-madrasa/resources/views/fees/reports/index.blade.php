@extends('layouts.app')

@section('header', 'Fee Reports')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Fee Reports</h1>
        <a href="{{ route('fees.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Fees
        </a>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Collection Report Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-xl font-semibold">Collection Report</h3>
                        <p class="text-green-100 text-sm mt-1">View fee collection details</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Generate detailed reports of fee collections including payment methods, dates, and amounts collected.
                </p>
                <a href="{{ route('fees.reports.collection') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center w-full justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    View Collection Report
                </a>
            </div>
        </div>

        <!-- Outstanding Report Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-xl font-semibold">Outstanding Report</h3>
                        <p class="text-red-100 text-sm mt-1">View pending payments</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    View all outstanding fees, overdue payments, and students with pending fee payments.
                </p>
                <a href="{{ route('fees.reports.outstanding') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center w-full justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    View Outstanding Report
                </a>
            </div>
        </div>

        <!-- Summary Statistics Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-xl font-semibold">Fee Summary</h3>
                        <p class="text-blue-100 text-sm mt-1">Overall statistics</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Fees:</span>
                        <span class="text-lg font-semibold text-gray-900">{{ number_format($totalFees ?? 0, 2) }} BDT</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Collected:</span>
                        <span class="text-lg font-semibold text-green-600">{{ number_format($collectedFees ?? 0, 2) }} BDT</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Outstanding:</span>
                        <span class="text-lg font-semibold text-red-600">{{ number_format($outstandingFees ?? 0, 2) }} BDT</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Collection Rate:</span>
                            <span class="text-lg font-semibold text-blue-600">{{ number_format($collectionRate ?? 0, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('fees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg inline-flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Fee
            </a>
            <a href="{{ route('fees.create-bulk') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg inline-flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Bulk Fee Creation
            </a>
            <a href="{{ route('fees.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg inline-flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                View All Fees
            </a>
        </div>
    </div>
</div>
@endsection
