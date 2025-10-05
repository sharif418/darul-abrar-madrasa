@extends('layouts.app')

@section('header', 'Notices')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Notices</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('notices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Notice
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <x-card>
        <div class="overflow-x-auto">
            <x-table :headers="['Title', 'For', 'Publish Date', 'Expiry Date', 'Active', 'Actions']">
                @forelse($notices as $notice)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left font-medium">
                            <a href="{{ route('notices.show', $notice->id) }}" class="text-blue-600 hover:underline">
                                {{ $notice->title }}
                            </a>
                            <div class="text-xs text-gray-500">By: {{ optional($notice->publishedBy)->name }}</div>
                        </td>
                        <td class="py-3 px-6 text-left capitalize">{{ $notice->notice_for }}</td>
                        <td class="py-3 px-6 text-left">{{ \Illuminate\Support\Carbon::parse($notice->publish_date)->format('d M, Y') }}</td>
                        <td class="py-3 px-6 text-left">
                            @if($notice->expiry_date)
                                {{ \Illuminate\Support\Carbon::parse($notice->expiry_date)->format('d M, Y') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($notice->is_active)
                                <x-badge color="green">Active</x-badge>
                            @else
                                <x-badge color="gray">Inactive</x-badge>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <a href="{{ route('notices.show', $notice->id) }}" class="w-4 mr-4 transform hover:text-blue-500 hover:scale-110 transition-all" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('notices.edit', $notice->id) }}" class="w-4 mr-4 transform hover:text-yellow-500 hover:scale-110 transition-all" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M7.5 20.5L19 9l-3.5-3.5L4 17v3.5h3.5z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('notices.destroy', $notice->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this notice?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-4 transform hover:text-red-500 hover:scale-110 transition-all" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 px-6 text-center text-gray-500">No notices found</td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <div class="mt-4">
            {{ $notices->links() }}
        </div>
    </x-card>
</div>
@endsection
