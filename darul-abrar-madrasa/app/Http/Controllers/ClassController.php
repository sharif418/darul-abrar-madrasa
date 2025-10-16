<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\EnrollStudentRequest;
use App\Http\Requests\AssignSubjectRequest;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request)
    {
        try {
            $query = ClassRoom::with('department')->withCount(['students', 'subjects']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('class_numeric', 'like', "%{$search}%")
                      ->orWhere('section', 'like', "%{$search}%");
                });
            }

            // Filter by department
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Filter by status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $classes = $query->latest()->paginate(15);
            $departments = Department::where('is_active', true)->get();

            return view('classes.index', compact('classes', 'departments'));
        } catch (\Exception $e) {
            Log::error('Failed to load classes list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load classes. Please try again.');
        }
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->with('user')->get();
        return view('classes.create', compact('departments', 'teachers'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(StoreClassRequest $request)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $class = ClassRoom::create($data);

            Log::info('Class created successfully', [
                'class_id' => $class->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.index')
                ->with('success', 'Class created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create class', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create class. Please try again.');
        }
    }

    /**
     * Display the specified class.
     */
    public function show(ClassRoom $class)
    {
        try {
            $class->load(['department', 'classTeacher.user', 'students.user', 'subjects.teacher.user']);
            $class->loadCount(['students', 'subjects', 'exams']);

            // Get recent exams
            $recentExams = $class->exams()
                ->latest()
                ->take(5)
                ->get();

            // Get available teachers for assignment (only if no class teacher assigned)
            $teachers = $class->hasClassTeacher() ? [] : Teacher::where('is_active', true)->with('user')->get();

            return view('classes.show', compact('class', 'recentExams', 'teachers'));
        } catch (\Exception $e) {
            Log::error('Failed to load class details', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load class details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassRoom $class)
    {
        $departments = Department::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->with('user')->get();
        return view('classes.edit', compact('class', 'departments', 'teachers'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(UpdateClassRequest $request, ClassRoom $class)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $class->update($data);

            Log::info('Class updated successfully', [
                'class_id' => $class->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.index')
                ->with('success', 'Class updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update class', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update class. Please try again.');
        }
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassRoom $class)
    {
        try {
            // Check if class has students
            if ($class->students()->count() > 0) {
                Log::warning('Attempted to delete class with enrolled students', [
                    'class_id' => $class->id,
                    'students_count' => $class->students()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('classes.index')
                    ->with('error', 'Cannot delete class with enrolled students. Please transfer or remove students first.');
            }

            // Check if class has subjects
            if ($class->subjects()->count() > 0) {
                Log::warning('Attempted to delete class with assigned subjects', [
                    'class_id' => $class->id,
                    'subjects_count' => $class->subjects()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('classes.index')
                    ->with('error', 'Cannot delete class with assigned subjects. Please remove subjects first.');
            }

            $classId = $class->id;
            $class->delete();

            Log::info('Class deleted successfully', [
                'class_id' => $classId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.index')
                ->with('success', 'Class deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete class', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to delete class. Please try again.');
        }
    }

    /**
     * Show the form for enrolling an existing student into this class.
     */
    public function showEnrollForm(Request $request, ClassRoom $class)
    {
        try {
            $class->load(['department', 'students.user']);

            $availableStudentsQuery = Student::where('is_active', true)
                ->whereNotIn('id', $class->students->pluck('id'))
                ->with(['user', 'class']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $availableStudentsQuery->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })->orWhere('admission_number', 'like', "%{$search}%");
                });
            }

            $availableStudents = $availableStudentsQuery->orderByDesc('id')->paginate(20);

            if ($class->isFull()) {
                session()->flash('warning', 'This class has reached its maximum capacity.');
            }

            return view('classes.enroll-student', compact('class', 'availableStudents'));
        } catch (\Exception $e) {
            Log::error('Failed to load enroll student form', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load enroll student form. Please try again.');
        }
    }

    /**
     * Enroll an existing student into this class.
     */
    public function enrollStudent(EnrollStudentRequest $request, ClassRoom $class)
    {
        try {
            $data = $request->validated();

            if ($class->isFull()) {
                return back()->with('error', 'This class has reached its maximum capacity.');
            }

            $student = Student::findOrFail($data['student_id']);

            if ((int)$student->class_id === (int)$class->id) {
                return redirect()->route('classes.show', $class)
                    ->with('info', 'The student is already enrolled in this class.');
            }

            $student->class_id = $class->id;
            $student->save();

            Log::info('Student enrolled into class', [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.show', $class)
                ->with('success', 'Student enrolled successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to enroll student', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all(),
            ]);

            return back()->with('error', 'Failed to enroll student. Please try again.');
        }
    }

    /**
     * Show the form for assigning an existing subject or creating a new one for this class.
     */
    public function showAssignSubjectForm(ClassRoom $class)
    {
        try {
            $class->load('department', 'subjects.teacher.user');

            $availableSubjects = Subject::where('is_active', true)
                ->whereNotIn('id', $class->subjects->pluck('id'))
                ->whereDoesntHave('results')
                ->with(['teacher.user', 'class'])
                ->get();

            $teachers = Teacher::where('is_active', true)->with('user')->get();

            return view('classes.assign-subject', compact('class', 'availableSubjects', 'teachers'));
        } catch (\Exception $e) {
            Log::error('Failed to load assign subject form', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load assign subject form. Please try again.');
        }
    }

    /**
     * Assign an existing subject or create a new subject for this class.
     */
    public function assignSubject(AssignSubjectRequest $request, ClassRoom $class)
    {
        try {
            $data = $request->validated();

            if (!empty($data['subject_id'])) {
                // Assign existing subject to this class
                $subject = Subject::findOrFail($data['subject_id']);

                // Prevent reassigning a subject that already has results
                if ($subject->results()->exists()) {
                    return back()->with('error', 'Cannot reassign a subject that already has results.');
                }

                $subject->class_id = $class->id;
                $subject->save();

                Log::info('Existing subject assigned to class', [
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'user_id' => auth()->id(),
                ]);
            } else {
                // Create new subject and assign to class
                $subject = new Subject();
                $subject->name = $data['name'];
                $subject->code = $data['code'];
                $subject->teacher_id = $data['teacher_id'];
                $subject->full_mark = $data['full_mark'];
                $subject->pass_mark = $data['pass_mark'];
                $subject->description = $data['description'] ?? null;
                $subject->is_active = true;
                $subject->class_id = $class->id;
                $subject->save();

                Log::info('New subject created and assigned to class', [
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'user_id' => auth()->id(),
                ]);
            }

            return redirect()->route('classes.show', $class)
                ->with('success', 'Subject assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to assign subject', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all(),
            ]);

            return back()->with('error', 'Failed to assign subject. Please try again.');
        }
    }

    /**
     * Unenroll a student from this class.
     */
    public function unenrollStudent(ClassRoom $class, Student $student)
    {
        try {
            if ((int)$student->class_id !== (int)$class->id) {
                return back()->with('error', 'The selected student does not belong to this class.');
            }

            // Business checks (fees, attendance, results) can be added as needed.

            $student->class_id = null;
            $student->save();

            Log::info('Student unenrolled from class', [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Student unenrolled successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to unenroll student', [
                'class_id' => $class->id,
                'student_id' => $student->id ?? null,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to unenroll student. Please try again.');
        }
    }

    /**
     * Unassign a subject from this class.
     */
    public function unassignSubject(ClassRoom $class, Subject $subject)
    {
        try {
            if ((int)$subject->class_id !== (int)$class->id) {
                return back()->with('error', 'The selected subject does not belong to this class.');
            }

            if ($subject->results()->exists()) {
                return back()->with('error', 'Cannot unassign subject with existing results.');
            }

            $subject->class_id = null;
            $subject->save();

            Log::info('Subject unassigned from class', [
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Subject unassigned successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to unassign subject', [
                'class_id' => $class->id,
                'subject_id' => $subject->id ?? null,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to unassign subject. Please try again.');
        }
    }

    /**
     * Assign a class teacher to this class.
     */
    public function assignClassTeacher(Request $request, ClassRoom $class)
    {
        try {
            // Validate the request
            $request->validate([
                'teacher_id' => 'required|exists:teachers,id'
            ]);

            // Retrieve the teacher
            $teacher = Teacher::findOrFail($request->teacher_id);

            // Check if the teacher's user account is active
            if (!$teacher->user || !$teacher->user->is_active) {
                return back()->with('error', 'Cannot assign inactive teacher as class teacher.');
            }

            // Guard against overwriting an existing class teacher
            if ($class->class_teacher_id && $class->class_teacher_id !== (int) $teacher->id) {
                return back()->with('error', 'A class teacher is already assigned. Remove the current teacher before assigning a new one.');
            }

            // Check if the teacher is already the class teacher for this class
            if ($class->class_teacher_id === $teacher->id) {
                return redirect()->route('classes.show', $class)
                    ->with('info', 'This teacher is already the class teacher for this class.');
            }

            // Update the class
            $class->class_teacher_id = $request->teacher_id;
            $class->save();

            Log::info('Class teacher assigned', [
                'class_id' => $class->id,
                'teacher_id' => $request->teacher_id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('classes.show', $class)
                ->with('success', 'Class teacher assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to assign class teacher', [
                'error' => $e->getMessage(),
                'class_id' => $class->id,
                'teacher_id' => $request->teacher_id ?? null,
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Failed to assign class teacher. Please try again.');
        }
    }

    /**
     * Remove the class teacher from this class.
     */
    public function removeClassTeacher(ClassRoom $class)
    {
        try {
            // Check if class has a class teacher
            if (!$class->class_teacher_id) {
                return back()->with('info', 'This class does not have a class teacher assigned.');
            }

            // Store the teacher_id for logging
            $teacherId = $class->class_teacher_id;

            // Remove the class teacher
            $class->class_teacher_id = null;
            $class->save();

            Log::info('Class teacher removed', [
                'class_id' => $class->id,
                'teacher_id' => $teacherId,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('classes.show', $class)
                ->with('success', 'Class teacher removed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to remove class teacher', [
                'error' => $e->getMessage(),
                'class_id' => $class->id,
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Failed to remove class teacher. Please try again.');
        }
    }
}
