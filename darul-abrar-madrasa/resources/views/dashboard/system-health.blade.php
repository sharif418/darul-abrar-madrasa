@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">System Health Dashboard</h1>
            <p class="text-gray-600 mt-1">Monitor data integrity, role synchronization, and system statistics</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.system-health.export') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('admin.system-health', ['refresh' => '1']) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </a>
        </div>
    </div>

    {{-- Overall Health Score (Hero Section) --}}
    <div class="rounded-xl p-8 @if($healthColor === 'green') bg-gradient-to-r from-green-500 to-green-600 @elseif($healthColor === 'yellow') bg-gradient-to-r from-yellow-500 to-yellow-600 @else bg-gradient-to-r from-red-500 to-red-600 @endif text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Overall System Health</h2>
                <p class="text-white/90">Last updated: {{ now()->format('d M Y, H:i:s') }}</p>
            </div>
            <div class="text-right">
                <div class="text-6xl font-bold">{{ number_format($healthScore, 1) }}%</div>
                <div class="text-xl mt-2 uppercase tracking-wide">{{ ucfirst($healthStatus) }}</div>
            </div>
        </div>
        @if($totalIssues > 0)
            <div class="mt-4 pt-4 border-t border-white/20">
                <p class="text-white/90">
                    <span class="font-semibold">{{ $totalIssues }}</span> issue(s) detected requiring attention
                </p>
            </div>
        @else
            <div class="mt-4 pt-4 border-t border-white/20">
                <p class="text-white/90 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    All systems operational - No issues detected
                </p>
            </div>
        @endif
    </div>

    {{-- Health Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            title="Data Integrity"
            :value="$totalMissing + $totalOrphaned"
            :color="($totalMissing + $totalOrphaned) === 0 ? 'green' : (($totalMissing + $totalOrphaned) < 5 ? 'yellow' : 'red')"
            tooltip="Missing and orphaned role records">
            <x-slot name="icon">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card 
            title="Role Synchronization"
            :value="$missingSpatieRoles->count() + $mismatchedSpatieRoles->count() + (isset($multipleSpatieRoles) ? $multipleSpatieRoles->count() : 0)"
            :color="($missingSpatieRoles->count() + $mismatchedSpatieRoles->count() + (isset($multipleSpatieRoles) ? $multipleSpatieRoles->count() : 0)) === 0 ? 'green' : 'yellow'"
            tooltip="Spatie role sync issues">
            <x-slot name="icon">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card 
            title="Migration Progress"
            :value="number_format($migrationProgress, 1) . '%'"
            :color="$migrationProgress >= 95 ? 'green' : ($migrationProgress >= 75 ? 'yellow' : 'red')"
            tooltip="Spatie role migration completion">
            <x-slot name="icon">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card 
            title="Total Users"
            :value="array_sum($usersByRole)"
            color="blue"
            tooltip="All user accounts in system">
            <x-slot name="icon">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Quick Actions --}}
    <x-card title="Quick Actions" variant="elevated">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <form action="{{ route('admin.system-health.verify') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full rounded-xl p-6 gradient-blue text-white hover-lift text-left">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-lg">Run Verification</div>
                            <div class="text-white/80 text-sm">Check for missing role records</div>
                        </div>
                    </div>
                </button>
            </form>

            <form action="{{ route('admin.system-health.sync') }}" method="POST" class="w-full" 
                  onsubmit="return !document.getElementById('sync_repair').checked || confirm('This will modify Spatie role assignments. Continue?')">
                @csrf
                <button type="submit" class="w-full rounded-xl p-6 gradient-purple text-white hover-lift text-left">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-lg">Sync Roles</div>
                            <div class="text-white/80 text-sm">Synchronize Spatie roles</div>
                            <div class="mt-2">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="repair" id="sync_repair" value="1" class="rounded border-white/30 text-purple-600 focus:ring-purple-500 focus:ring-offset-0">
                                    <span class="ml-2 text-white/90 text-sm">Apply repairs</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </button>
            </form>

            <form action="{{ route('admin.system-health.repair') }}" method="POST" class="w-full" 
                  onsubmit="return confirm('This will create missing role records. Continue?')">
                @csrf
                <button type="submit" class="w-full rounded-xl p-6 gradient-green text-white hover-lift text-left">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-lg">Repair Issues</div>
                            <div class="text-white/80 text-sm">Create missing records</div>
                        </div>
                    </div>
                </button>
            </form>
        </div>
    </x-card>

    {{-- Detailed Issues Section --}}
    @if($totalMissing > 0)
    <x-card title="Missing Role Records ({{ $totalMissing }})" variant="bordered" :collapsible="true">
        <div class="space-y-4">
            @foreach(['teacher', 'student', 'guardian', 'accountant'] as $role)
                @if($missingRoleRecords[$role]->count() > 0)
                    <div class="border-l-4 border-red-500 pl-4">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            {{ ucfirst($role) }}s: {{ $missingRoleRecords[$role]->count() }} missing
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">ID</th>
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Name</th>
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Email</th>
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($missingRoleRecords[$role]->take(10) as $user)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-2 px-4">{{ $user->id }}</td>
                                            <td class="py-2 px-4">{{ $user->name }}</td>
                                            <td class="py-2 px-4">{{ $user->email }}</td>
                                            <td class="py-2 px-4">{{ $user->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                    @if($missingRoleRecords[$role]->count() > 10)
                                        <tr>
                                            <td colspan="4" class="py-2 px-4 text-center text-gray-500 text-sm">
                                                ... and {{ $missingRoleRecords[$role]->count() - 10 }} more
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </x-card>
    @endif

    @if($totalOrphaned > 0)
    <x-card title="Orphaned Records ({{ $totalOrphaned }})" variant="bordered" :collapsible="true">
        <div class="space-y-4">
            @foreach(['teacher', 'student', 'guardian', 'accountant'] as $role)
                @if($orphanedRecords[$role]->count() > 0)
                    <div class="border-l-4 border-yellow-500 pl-4">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            {{ ucfirst($role) }}s: {{ $orphanedRecords[$role]->count() }} orphaned
                        </h4>
                        <p class="text-sm text-gray-600 mb-2">These records have no corresponding user account</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Record ID</th>
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">User ID (Missing)</th>
                                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orphanedRecords[$role]->take(10) as $record)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-2 px-4">{{ $record->id }}</td>
                                            <td class="py-2 px-4 text-red-600">{{ $record->user_id }}</td>
                                            <td class="py-2 px-4">{{ $record->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                    @if($orphanedRecords[$role]->count() > 10)
                                        <tr>
                                            <td colspan="3" class="py-2 px-4 text-center text-gray-500 text-sm">
                                                ... and {{ $orphanedRecords[$role]->count() - 10 }} more
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </x-card>
    @endif

    {{-- Spatie Role Synchronization Issues --}}
    @if($missingSpatieRoles->count() > 0 || $mismatchedSpatieRoles->count() > 0 || $multipleSpatieRoles->count() > 0)
    <x-card title="Spatie Role Synchronization Issues ({{ $missingSpatieRoles->count() + $mismatchedSpatieRoles->count() + $multipleSpatieRoles->count() }})" variant="bordered" :collapsible="true">
        <div class="space-y-4">
            @if($missingSpatieRoles->count() > 0)
                <div class="border-l-4 border-yellow-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        Missing Spatie Roles: {{ $missingSpatieRoles->count() }}
                    </h4>
                    <p class="text-sm text-gray-600 mb-2">Users without Spatie role assignment</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">ID</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Name</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Email</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Legacy Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($missingSpatieRoles->take(20) as $user)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2 px-4">{{ $user->id }}</td>
                                        <td class="py-2 px-4">{{ $user->name }}</td>
                                        <td class="py-2 px-4">{{ $user->email }}</td>
                                        <td class="py-2 px-4">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($missingSpatieRoles->count() > 20)
                                    <tr>
                                        <td colspan="4" class="py-2 px-4 text-center text-gray-500 text-sm">
                                            ... and {{ $missingSpatieRoles->count() - 20 }} more
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($mismatchedSpatieRoles->count() > 0)
                <div class="border-l-4 border-orange-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        Mismatched Spatie Roles: {{ $mismatchedSpatieRoles->count() }}
                    </h4>
                    <p class="text-sm text-gray-600 mb-2">Users with incorrect Spatie role assignment</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">ID</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Name</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Legacy Role</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Spatie Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mismatchedSpatieRoles->take(20) as $user)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2 px-4">{{ $user->id }}</td>
                                        <td class="py-2 px-4">{{ $user->name }}</td>
                                        <td class="py-2 px-4">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4">
                                            @foreach($user->roles as $role)
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 mr-1">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                @if($mismatchedSpatieRoles->count() > 20)
                                    <tr>
                                        <td colspan="4" class="py-2 px-4 text-center text-gray-500 text-sm">
                                            ... and {{ $mismatchedSpatieRoles->count() - 20 }} more
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($multipleSpatieRoles->count() > 0)
                <div class="border-l-4 border-purple-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        Multiple Spatie Roles: {{ $multipleSpatieRoles->count() }}
                    </h4>
                    <p class="text-sm text-gray-600 mb-2">Users with more than one Spatie role assigned</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">ID</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Name</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Legacy Role</th>
                                    <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Spatie Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($multipleSpatieRoles->take(20) as $user)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2 px-4">{{ $user->id }}</td>
                                        <td class="py-2 px-4">{{ $user->name }}</td>
                                        <td class="py-2 px-4">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4">
                                            @foreach($user->roles as $role)
                                                <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 mr-1">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                @if($multipleSpatieRoles->count() > 20)
                                    <tr>
                                        <td colspan="4" class="py-2 px-4 text-center text-gray-500 text-sm">
                                            ... and {{ $multipleSpatieRoles->count() - 20 }} more
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </x-card>
    @endif

    {{-- Database Statistics --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card title="Users by Role" variant="elevated">
            <div class="space-y-3">
                @foreach($usersByRole as $role => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 capitalize">{{ $role }}</span>
                        <span class="font-semibold text-gray-900">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Active vs Inactive by Role" variant="elevated">
            <div class="space-y-3">
                @foreach($activeInactiveStats as $role => $stats)
                    @if($usersByRole[$role] > 0)
                        <div class="border-b pb-2 last:border-b-0">
                            <div class="text-sm font-medium text-gray-700 capitalize mb-1">{{ $role }}</div>
                            <div class="flex items-center gap-4 text-xs">
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                                    <span class="text-gray-600">Active: {{ $stats['active'] }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span>
                                    <span class="text-gray-600">Inactive: {{ $stats['inactive'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-card>

        <x-card title="Role Record Counts" variant="elevated">
            <div class="space-y-3">
                @foreach($roleRecordCounts as $role => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 capitalize">{{ $role }}</span>
                        <span class="font-semibold text-gray-900">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    {{-- Recent Activity Logs --}}
    @if($recentLogs->count() > 0)
    <x-card title="Recent System Activity (Last 20 entries)" variant="bordered">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Timestamp</th>
                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Log Name</th>
                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">Description</th>
                        <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600">User</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-sm">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
                            <td class="py-2 px-4">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($log->log_name === 'system') bg-blue-100 text-blue-800
                                    @elseif($log->log_name === 'roles') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $log->log_name }}
                                </span>
                            </td>
                            <td class="py-2 px-4 text-sm">{{ $log->description ?? 'N/A' }}</td>
                            <td class="py-2 px-4 text-sm">{{ $log->causer_id ?? 'System' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
</div>
@endsection

@if(session('command_output'))
@push('scripts')
<script>
    alert('Command Output:\n\n' + @json(session('command_output')));
</script>
@endpush
@endif
