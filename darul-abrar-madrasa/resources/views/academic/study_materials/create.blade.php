@extends('layouts.app')

@section('header', 'Upload Study Material')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Upload Study Material</h1>
        <x-button href="{{ route('study-materials.index') }}" color="secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Materials
        </x-button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Study Material Information</h2>
            <p class="text-gray-600 mt-1">Upload a new study material for your students.</p>
        </div>

        <form action="{{ route('study-materials.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(Auth::user()->isAdmin())
                    <div>
                        <x-label for="teacher_id" value="Teacher" />
                        <x-select id="teacher_id" name="teacher_id" class="block mt-1 w-full" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error for="teacher_id" class="mt-2" />
                    </div>
                @else
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                    <div>
                        <x-label value="Teacher" />
                        <div class="mt-1 p-2 bg-gray-100 rounded-md">
                            {{ $teacher->user->name }}
                        </div>
                    </div>
                @endif

                <div>
                    <x-label for="class_id" value="Class" />
                    <x-select id="class_id" name="class_id" class="block mt-1 w-full" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', request('class_id')) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="class_id" class="mt-2" />
                </div>

                <div>
                    <x-label for="subject_id" value="Subject" />
                    <x-select id="subject_id" name="subject_id" class="block mt-1 w-full" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', request('subject_id')) == $subject->id ? 'selected' : '' }}
                                data-class="{{ $subject->class_id }}" data-teacher="{{ $subject->teacher_id }}">
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="subject_id" class="mt-2" />
                </div>

                <div>
                    <x-label for="content_type" value="Material Type" />
                    <x-select id="content_type" name="content_type" class="block mt-1 w-full" required>
                        <option value="">Select Material Type</option>
                        @foreach($contentTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('content_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error for="content_type" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="title" value="Title" />
                    <x-input id="title" type="text" name="title" value="{{ old('title') }}" class="block mt-1 w-full" required />
                    <x-input-error for="title" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="description" value="Description" />
                    <x-textarea id="description" name="description" class="block mt-1 w-full" rows="4">{{ old('description') }}</x-textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>

                <div id="file_upload_container" class="md:col-span-2">
                    <x-label for="file" value="Upload File" />
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="file" name="file" type="file" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, etc. up to 10MB
                            </p>
                        </div>
                    </div>
                    <x-input-error for="file" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="is_published" type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_published', true) ? 'checked' : '' }}>
                    <label for="is_published" class="ml-2 text-sm text-gray-700">Publish immediately</label>
                    <p class="text-xs text-gray-500 ml-6">If unchecked, the material will be saved as a draft</p>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <x-button type="submit" color="primary">
                    Upload Material
                </x-button>
            </div>
        </form>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700">Study Material Guidelines</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Material Types</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li><strong>Class Notes:</strong> Lecture notes, summaries, and key points</li>
                        <li><strong>Exam Suggestions:</strong> Practice questions and exam preparation materials</li>
                        <li><strong>Video Links:</strong> Educational videos and tutorials</li>
                        <li><strong>Documents:</strong> Worksheets, assignments, and reference materials</li>
                        <li><strong>Images:</strong> Diagrams, charts, and visual aids</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Best Practices</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Use clear, descriptive titles for easy identification</li>
                        <li>Provide detailed descriptions to help students understand the content</li>
                        <li>Organize materials by topic or chapter when possible</li>
                        <li>Use appropriate file formats for better accessibility</li>
                        <li>Keep file sizes reasonable for easier downloading</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        const subjectSelect = document.getElementById('subject_id');
        const teacherSelect = document.getElementById('teacher_id');
        const contentTypeSelect = document.getElementById('content_type');
        const fileUploadContainer = document.getElementById('file_upload_container');
        
        // Filter subjects based on selected class and teacher
        function filterSubjects() {
            const selectedClassId = classSelect.value;
            const selectedTeacherId = teacherSelect ? teacherSelect.value : '{{ Auth::user()->isTeacher() ? Auth::user()->teacher->id : "" }}';
            
            // Hide all options first
            Array.from(subjectSelect.options).forEach(option => {
                if (option.value === '') return; // Skip the placeholder option
                
                const subjectClassId = option.getAttribute('data-class');
                const subjectTeacherId = option.getAttribute('data-teacher');
                
                // Show option only if it matches both class and teacher (if selected)
                const matchesClass = !selectedClassId || subjectClassId === selectedClassId;
                const matchesTeacher = !selectedTeacherId || subjectTeacherId === selectedTeacherId;
                
                option.hidden = !(matchesClass && matchesTeacher);
            });
            
            // If the currently selected option is now hidden, reset the selection
            if (subjectSelect.selectedOptions.length > 0 && subjectSelect.selectedOptions[0].hidden) {
                subjectSelect.value = '';
            }
        }
        
        // Toggle file upload based on content type
        function toggleFileUpload() {
            if (contentTypeSelect.value === 'video_link') {
                fileUploadContainer.classList.add('hidden');
                document.querySelector('label[for="description"]').textContent = 'Video URL';
            } else {
                fileUploadContainer.classList.remove('hidden');
                document.querySelector('label[for="description"]').textContent = 'Description';
            }
        }
        
        // Initial filtering and toggling
        if (classSelect && subjectSelect) {
            filterSubjects();
            
            // Add event listeners
            classSelect.addEventListener('change', filterSubjects);
            if (teacherSelect) {
                teacherSelect.addEventListener('change', filterSubjects);
            }
        }
        
        if (contentTypeSelect && fileUploadContainer) {
            toggleFileUpload();
            contentTypeSelect.addEventListener('change', toggleFileUpload);
        }
    });
</script>
@endpush
@endsection