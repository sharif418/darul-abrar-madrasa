@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="studentsBulkActions()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Students</h1>
        <a href="{{ route('students.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Register New Student
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('students.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-grow md:flex-grow-0">
                <input type="text" name="search" placeholder="Search by name, email, or admission number" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="{{ request('search') }}">
            </div>
            
            <div class="w-full md:w-auto">
                <select name="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->department->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <select name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Filter
                </button>
            </div>
            
            <div class="w-full md:w-auto">
                <a href="{{ route('students.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk actions toolbar -->
    <div x-show="selected.length > 0" class="mb-4 sticky top-0 z-10 bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-center justify-between">
        <div class="text-sm text-yellow-800 font-medium">
            <span x-text="selected.length"></span> students selected
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showPromote = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-3 rounded">
                Promote to Class
            </button>
            <button type="button" @click="showTransfer = true" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-3 rounded">
                Transfer to Class
            </button>
            <button type="button" @click="submitStatus(1)" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded">
                Activate
            </button>
            <button type="button" @click="submitStatus(0)" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-3 rounded">
                Deactivate
            </button>
            <button type="button" @click="clearSelection()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-3 rounded">
                Cancel
            </button>
        </div>
    </div>

    <!-- Promote Modal -->
    <div x-cloak x-show="showPromote" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-40" @click="showPromote = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Promote to Class</h3>
            <form method="POST" action="{{ route('students.bulk-promote') }}" @submit="attachSelected($event)">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Class</label>
                    <select name="target_class_id" class="block w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->department->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-6 flex items-center justify-end gap-2">
                    <button type="button" @click="showPromote = false" class="px-4 py-2 rounded border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Promote</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div x-cloak x-show="showTransfer" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-40" @click="showTransfer = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transfer to Class</h3>
            <form method="POST" action="{{ route('students.bulk-transfer') }}" @submit="attachSelected($event)">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Class</label>
                    <select name="target_class_id" class="block w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->department->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-6 flex items-center justify-end gap-2">
                    <button type="button" @click="showTransfer = false" class="px-4 py-2 rounded border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-purple-600 text-white hover:bg-purple-700">Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden status form -->
    <form x-ref="statusForm" method="POST" action="{{ route('students.bulk-status') }}" class="hidden">
        @csrf
        <input type="hidden" name="status" value="">
    </form>

    <!-- Students List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Class</th>
                        <th class="py-3 px-6 text-left">Admission No.</th>
                        <th class="py-3 px-6 text-left">Contact</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($students as $student)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">
                                <input type="checkbox" value="{{ $student->id }}" x-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-6 text-left">{{ $student->id }}</td>
                            <td class="py-3 px-6 text-left">
                                <div class="flex items-center">
                                    @if($student->user->avatar)
                                        <div class="mr-2">
                                            <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $student->user->avatar) }}" alt="{{ $student->user->name }}">
                                        </div>
                                    @endif
                                    <span>{{ $student->user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-6 text-left">{{ optional($student->class)->name ?? '—' }}</td>
                            <td class="py-3 px-6 text-left">{{ $student->admission_number }}</td>
                            <td class="py-3 px-6 text-left">
                                <div>{{ $student->user->email }}</div>
                                <div class="text-xs text-gray-500">{{ $student->user->phone }}</div>
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($student->is_active)
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                                @else
                                    <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="{{ route('students.show', $student->id) }}" class="w-4 mr-2 transform hover:text-blue-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('students.edit', $student->id) }}" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <div class="inline">
                                        <button type="button" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110 transition-all" title="Delete"
                                            @click.prevent="window.dispatchEvent(new CustomEvent('open-delete-student-{{ $student->id }}'))">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <x-confirm-delete-modal
                                            openEvent="open-delete-student-{{ $student->id }}"
                                            title="Delete Student"
                                            message="Are you sure you want to delete this student?"
                                            confirmText="Delete"
                                            cancelText="Cancel"
                                            confirmButtonColor="red"
                                            formAction="{{ route('students.destroy', $student->id) }}"
                                            formMethod="DELETE"
                                        />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 px-6 text-center text-gray-500">No students found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4">
            {{ $students->links() }}
        </div>
    </div>
</div>
<script>
function studentsBulkActions() {
    return {
        selected: [],
        selectAll: false,
        showPromote: false,
        showTransfer: false,
        toggleSelectAll() {
            const checkboxes = Array.from(document.querySelectorAll('table tbody input[type="checkbox"][x-model="selected"]'));
            if (this.selectAll) {
                this.selected = checkboxes.map(cb => cb.value);
            } else {
                this.selected = [];
            }
        },
        clearSelection() {
            this.selectAll = false;
            this.selected = [];
        },
        attachSelected(e) {
            // attach selected[] hidden inputs to the form being submitted
            const form = e.target.closest('form');
            // cleanup old
            form.querySelectorAll('input[name="student_ids[]"]').forEach(n => n.remove());
            this.selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = id;
                form.appendChild(input);
            });
        },
        submitStatus(status) {
            if (this.selected.length === 0) return;
            const form = this.$refs.statusForm;
            // cleanup old
            form.querySelectorAll('input[name="student_ids[]"]').forEach(n => n.remove());
            this.selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.querySelector('input[name="status"]').value = status ? 1 : 0;
            form.submit();
        }
    }
}
</script>
@endsection
