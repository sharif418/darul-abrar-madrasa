@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <x-card>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Financial Reports</h1>
        <div class="text-sm text-gray-600">{{ now()->format('d M Y') }}</div>
      </div>

      @if(session('success'))
        <div class="mt-4"><x-alert type="success" :message="session('success')" /></div>
      @endif
      @if(session('error'))
        <div class="mt-4"><x-alert type="error" :message="session('error')" /></div>
      @endif

      <form method="GET" class="mt-4 grid md:grid-cols-6 gap-3 text-sm">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2" />
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2" />
        <input name="class_id" value="{{ request('class_id') }}" placeholder="Class ID" class="border rounded px-3 py-2" />
        <input name="student_id" value="{{ request('student_id') }}" placeholder="Student ID" class="border rounded px-3 py-2" />
        <select name="fee_type" class="border rounded px-3 py-2">
          <option value="">All Fee Types</option>
          <option value="admission" @selected(request('fee_type')==='admission')>Admission</option>
          <option value="monthly" @selected(request('fee_type')==='monthly')>Monthly</option>
          <option value="exam" @selected(request('fee_type')==='exam')>Exam</option>
          <option value="other" @selected(request('fee_type')==='other')>Other</option>
        </select>
        <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply</button>
      </form>

      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Collection" value="৳ {{ number_format((float)($summary['total_collection'] ?? 0), 2) }}" color="green" />
        <x-stat-card title="Outstanding" value="৳ {{ number_format((float)($summary['outstanding'] ?? 0), 2) }}" color="yellow" />
        <x-stat-card title="Late Fees Collected" value="৳ {{ number_format((float)($summary['late_fees'] ?? 0), 2) }}" color="red" />
        <x-stat-card title="Waivers Granted" value="৳ {{ number_format((float)($summary['waivers'] ?? 0), 2) }}" color="indigo" />
      </div>

      <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('accountant.reports.collection', request()->all()) }}" class="block p-5 border rounded bg-white hover:bg-gray-50">
          <div class="text-lg font-semibold text-gray-800">Collection Report</div>
          <div class="text-sm text-gray-600 mt-1">Daily/Monthly collections, breakdowns and trends.</div>
        </a>
        <a href="{{ route('accountant.reports.outstanding', request()->all()) }}" class="block p-5 border rounded bg-white hover:bg-gray-50">
          <div class="text-lg font-semibold text-gray-800">Outstanding Report</div>
          <div class="text-sm text-gray-600 mt-1">Pending and overdue fees by class/student.</div>
        </a>
        <a href="{{ route('accountant.reports.waivers', request()->all()) }}" class="block p-5 border rounded bg-white hover:bg-gray-50">
          <div class="text-lg font-semibold text-gray-800">Waiver Report</div>
          <div class="text-sm text-gray-600 mt-1">Approved/Pending/Rejected waivers and totals.</div>
        </a>
        <a href="{{ route('accountant.reconciliation') }}" class="block p-5 border rounded bg-white hover:bg-gray-50">
          <div class="text-lg font-semibold text-gray-800">Reconciliation</div>
          <div class="text-sm text-gray-600 mt-1">Match payments against bank statements.</div>
        </a>
      </div>

      <div class="mt-8">
        <div class="text-lg font-semibold text-gray-800 mb-2">Quick Links</div>
        <div class="flex flex-wrap gap-3">
          <a href="{{ route('accountant.fees') }}" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">Manage Fees</a>
          <a href="{{ route('accountant.waivers') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Manage Waivers</a>
          <a href="{{ route('accountant.installments') }}" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Installment Plans</a>
          <a href="{{ route('accountant.late-fees') }}" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm">Late Fees</a>
        </div>
      </div>
    </x-card>
  </div>
</div>
@endsection
