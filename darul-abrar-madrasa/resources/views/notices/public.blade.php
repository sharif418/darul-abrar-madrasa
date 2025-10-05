@extends('layouts.app')

@section('header', 'Public Notices')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Public Notices</h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <x-card>
        <div class="overflow-x-auto">
            <x-table :headers="['Title', 'Audience', 'Publish Date', 'Expires', 'Published By']">
                @forelse($notices as $notice)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left">
                            <div class="font-semibold text-gray-900">
                                {{ $notice->title }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ \Illuminate\Support\Str::limit($notice->description, 120) }}
                            </div>
                        </td>
                        <td class="py-3 px-6 capitalize">{{ $notice->notice_for }}</td>
                        <td class="py-3 px-6">
                            {{ \Illuminate\Support\Carbon::parse($notice->publish_date)->format('d M, Y') }}
                        </td>
                        <td class="py-3 px-6">
                            @if($notice->expiry_date)
                                {{ \Illuminate\Support\Carbon::parse($notice->expiry_date)->format('d M, Y') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-6">
                            {{ optional($notice->publishedBy)->name ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 px-6 text-center text-gray-500">No public notices available</td>
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
