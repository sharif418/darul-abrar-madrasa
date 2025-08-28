<?php

namespace App\Http\Controllers;

use App\Models\StudyMaterial;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudyMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = StudyMaterial::with(['teacher', 'class', 'subject']);
        
        // If teacher, only show their study materials
        if ($user->isTeacher()) {
            $teacherId = $user->teacher->id;
            $query->where('teacher_id', $teacherId);
        }
        
        // If student, only show published materials for their class
        if ($user->isStudent()) {
            $classId = $user->student->class_id;
            $query->where('class_id', $classId)
                  ->where('is_published', true);
        }
        
        // Apply filters
        if ($request->has('content_type') && $request->content_type != '') {
            $query->where('content_type', $request->content_type);
        }
        
        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('subject_id') && $request->subject_id != '') {
            $query->where('subject_id', $request->subject_id);
        }
        
        $studyMaterials = $query->latest()->paginate(10);
        
        // Get data for filters
        $classes = ClassRoom::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $contentTypes = [
            'note' => 'Class Notes',
            'suggestion' => 'Exam Suggestions',
            'video_link' => 'Video Links',
            'document' => 'Documents',
            'image' => 'Images',
            'other' => 'Other Materials'
        ];
        
        return view('academic.study_materials.index', compact('studyMaterials', 'classes', 'subjects', 'contentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            $teacherId = $teacher->id;
            
            // Get classes and subjects assigned to this teacher
            $subjects = Subject::where('teacher_id', $teacherId)->with('class')->get();
            $classes = $subjects->pluck('class')->unique();
            
            $contentTypes = [
                'note' => 'Class Notes',
                'suggestion' => 'Exam Suggestions',
                'video_link' => 'Video Links',
                'document' => 'Documents',
                'image' => 'Images',
                'other' => 'Other Materials'
            ];
            
            return view('academic.study_materials.create', compact('teacher', 'classes', 'subjects', 'contentTypes'));
        } else {
            // For admin, show all teachers, classes and subjects
            $teachers = Teacher::with('user')->get();
            $classes = ClassRoom::orderBy('name')->get();
            $subjects = Subject::orderBy('name')->get();
            
            $contentTypes = [
                'note' => 'Class Notes',
                'suggestion' => 'Exam Suggestions',
                'video_link' => 'Video Links',
                'document' => 'Documents',
                'image' => 'Images',
                'other' => 'Other Materials'
            ];
            
            return view('academic.study_materials.create', compact('teachers', 'classes', 'subjects', 'contentTypes'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:note,suggestion,video_link,document,image,other',
            'is_published' => 'boolean',
        ];
        
        // If content type is video_link, validate URL
        if ($request->content_type === 'video_link') {
            $validationRules['description'] = 'required|url';
            $validationRules['file'] = 'nullable';
        } else {
            // For other content types, require file unless it's a note
            if ($request->content_type !== 'note') {
                $validationRules['file'] = 'required|file|max:10240'; // 10MB max
            }
        }
        
        // If teacher, use their ID
        if ($user->isTeacher()) {
            $request->merge(['teacher_id' => $user->teacher->id]);
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request, $user) {
                    $subject = Subject::find($value);
                    if ($subject && $subject->teacher_id != $user->teacher->id) {
                        $fail('You can only upload materials for subjects assigned to you.');
                    }
                }
            ];
        } else {
            // For admin, validate teacher_id
            $validationRules['teacher_id'] = 'required|exists:teachers,id';
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request) {
                    $subject = Subject::find($value);
                    if ($subject && $subject->teacher_id != $request->teacher_id) {
                        $fail('The selected subject is not assigned to the selected teacher.');
                    }
                }
            ];
        }
        
        $request->validate($validationRules);
        
        $data = $request->except('file');
        
        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('study_materials', 'public');
            $data['file_path'] = $path;
        }
        
        StudyMaterial::create($data);
        
        return redirect()->route('study-materials.index')
            ->with('success', 'Study material uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StudyMaterial $studyMaterial)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to view this study material
        if ($user->isTeacher() && $user->teacher->id != $studyMaterial->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // If student, check if the material is published and for their class
        if ($user->isStudent()) {
            if (!$studyMaterial->is_published || $user->student->class_id != $studyMaterial->class_id) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        return view('academic.study_materials.show', compact('studyMaterial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudyMaterial $studyMaterial)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to edit this study material
        if ($user->isTeacher() && $user->teacher->id != $studyMaterial->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            $teacherId = $teacher->id;
            
            // Get classes and subjects assigned to this teacher
            $subjects = Subject::where('teacher_id', $teacherId)->with('class')->get();
            $classes = $subjects->pluck('class')->unique();
            
            $contentTypes = [
                'note' => 'Class Notes',
                'suggestion' => 'Exam Suggestions',
                'video_link' => 'Video Links',
                'document' => 'Documents',
                'image' => 'Images',
                'other' => 'Other Materials'
            ];
            
            return view('academic.study_materials.edit', compact('studyMaterial', 'teacher', 'classes', 'subjects', 'contentTypes'));
        } else {
            // For admin, show all teachers, classes and subjects
            $teachers = Teacher::with('user')->get();
            $classes = ClassRoom::orderBy('name')->get();
            $subjects = Subject::orderBy('name')->get();
            
            $contentTypes = [
                'note' => 'Class Notes',
                'suggestion' => 'Exam Suggestions',
                'video_link' => 'Video Links',
                'document' => 'Documents',
                'image' => 'Images',
                'other' => 'Other Materials'
            ];
            
            return view('academic.study_materials.edit', compact('studyMaterial', 'teachers', 'classes', 'subjects', 'contentTypes'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StudyMaterial $studyMaterial)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to update this study material
        if ($user->isTeacher() && $user->teacher->id != $studyMaterial->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:note,suggestion,video_link,document,image,other',
            'is_published' => 'boolean',
        ];
        
        // If content type is video_link, validate URL
        if ($request->content_type === 'video_link') {
            $validationRules['description'] = 'required|url';
        } else {
            // For other content types, file is optional on update
            $validationRules['file'] = 'nullable|file|max:10240'; // 10MB max
        }
        
        // If teacher, use their ID
        if ($user->isTeacher()) {
            $request->merge(['teacher_id' => $user->teacher->id]);
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request, $user) {
                    $subject = Subject::find($value);
                    if ($subject && $subject->teacher_id != $user->teacher->id) {
                        $fail('You can only upload materials for subjects assigned to you.');
                    }
                }
            ];
        } else {
            // For admin, validate teacher_id
            $validationRules['teacher_id'] = 'required|exists:teachers,id';
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request) {
                    $subject = Subject::find($value);
                    if ($subject && $subject->teacher_id != $request->teacher_id) {
                        $fail('The selected subject is not assigned to the selected teacher.');
                    }
                }
            ];
        }
        
        $request->validate($validationRules);
        
        $data = $request->except('file');
        
        // Handle file upload if present
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($studyMaterial->file_path) {
                Storage::disk('public')->delete($studyMaterial->file_path);
            }
            
            $file = $request->file('file');
            $path = $file->store('study_materials', 'public');
            $data['file_path'] = $path;
        }
        
        $studyMaterial->update($data);
        
        return redirect()->route('study-materials.index')
            ->with('success', 'Study material updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudyMaterial $studyMaterial)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to delete this study material
        if ($user->isTeacher() && $user->teacher->id != $studyMaterial->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Delete file if exists
        if ($studyMaterial->file_path) {
            Storage::disk('public')->delete($studyMaterial->file_path);
        }
        
        $studyMaterial->delete();
        
        return redirect()->route('study-materials.index')
            ->with('success', 'Study material deleted successfully.');
    }
    
    /**
     * Download the study material file.
     */
    public function download(StudyMaterial $studyMaterial)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to download this study material
        if ($user->isTeacher() && $user->teacher->id != $studyMaterial->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // If student, check if the material is published and for their class
        if ($user->isStudent()) {
            if (!$studyMaterial->is_published || $user->student->class_id != $studyMaterial->class_id) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        // Check if file exists
        if (!$studyMaterial->file_path || !Storage::disk('public')->exists($studyMaterial->file_path)) {
            abort(404, 'File not found.');
        }
        
        return Storage::disk('public')->download($studyMaterial->file_path, $studyMaterial->title . '.' . $studyMaterial->fileExtension);
    }
    
    /**
     * Toggle the published status of the study material.
     */
    public function togglePublished(StudyMaterial $studyMaterial)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to update this study material
        if ($user->isTeacher() && $user->teacher->id != $studyMaterial->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $studyMaterial->update([
            'is_published' => !$studyMaterial->is_published
        ]);
        
        return redirect()->route('study-materials.index')
            ->with('success', 'Study material ' . ($studyMaterial->is_published ? 'published' : 'unpublished') . ' successfully.');
    }
    
    /**
     * Display study materials for students.
     */
    public function myMaterials(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isStudent()) {
            abort(403, 'Unauthorized action.');
        }
        
        $student = $user->student;
        $classId = $student->class_id;
        
        $query = StudyMaterial::with(['teacher', 'subject'])
            ->where('class_id', $classId)
            ->where('is_published', true);
        
        // Apply filters
        if ($request->has('content_type') && $request->content_type != '') {
            $query->where('content_type', $request->content_type);
        }
        
        if ($request->has('subject_id') && $request->subject_id != '') {
            $query->where('subject_id', $request->subject_id);
        }
        
        $studyMaterials = $query->latest()->paginate(10);
        
        // Get data for filters
        $subjects = Subject::where('class_id', $classId)->orderBy('name')->get();
        $contentTypes = [
            'note' => 'Class Notes',
            'suggestion' => 'Exam Suggestions',
            'video_link' => 'Video Links',
            'document' => 'Documents',
            'image' => 'Images',
            'other' => 'Other Materials'
        ];
        
        return view('academic.study_materials.my_materials', compact('studyMaterials', 'subjects', 'contentTypes', 'student'));
    }
}