<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Requests\BulkStudentActionRequest;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    /**
     * Display a listing of the students.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->search,
                'class_id' => $request->class_id,
                'status' => $request->status,
            ];

            $students = $this->studentRepository->getAllWithFilters($filters, 15);
            $classes = ClassRoom::with('department')->get();

            return view('students.index', compact('students', 'classes'));
        } catch (\Exception $e) {
            Log::error('Failed to load students list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load students. Please try again.');
        }
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $classes = ClassRoom::with('department')->get();
        return view('students.create', compact('classes'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        try {
            $student = $this->studentRepository->create($request->validated());

            Log::info('Student created successfully', [
                'student_id' => $student->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('students.show', $student->id)
                ->with('success', 'Student registered successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create student', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->except(['password', 'password_confirmation', 'avatar']),
            ]);

            return back()->withInput()->with('error', 'Failed to register student. Please try again.');
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        try {
            // Load enriched student with relationships/statistics from repository
            $student = $this->studentRepository->getWithStats($student->id);

            // Attendance summary (all-time)
            $totalAttendance = $student->attendances()->count();
            $presentCount = $student->attendances()->where('status', 'present')->count();
            $absentCount = $student->attendances()->where('status', 'absent')->count();
            $lateCount = $student->attendances()->where('status', 'late')->count();
            $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

            // Recent results (latest 10)
            $recentResults = $student->results()
                ->with(['exam', 'subject'])
                ->latest()
                ->limit(10)
                ->get();

            // Pending/partial fees list
            $pendingFees = $student->fees()
                ->where(function ($q) {
                    $q->where('status', 'unpaid')->orWhere('status', 'partial');
                })
                ->with(['student'])
                ->latest('due_date')
                ->get();

            return view('students.show', compact(
                'student',
                'presentCount',
                'absentCount',
                'lateCount',
                'attendanceRate',
                'recentResults',
                'pendingFees'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load student details', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load student details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $student->load('user');
        $classes = ClassRoom::with('department')->get();
        return view('students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $student = $this->studentRepository->update($student, $request->validated());

            Log::info('Student updated successfully', [
                'student_id' => $student->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('students.show', $student->id)
                ->with('success', 'Student updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->except(['password', 'password_confirmation', 'avatar']),
            ]);

            return back()->withInput()->with('error', 'Failed to update student. Please try again.');
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        try {
            $studentId = $student->id;
            $this->studentRepository->delete($student);

            Log::info('Student deleted successfully', [
                'student_id' => $studentId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to delete student. Please try again.');
        }
    }

    /**
     * Bulk promote students to a target class.
     */
    public function bulkPromote(BulkStudentActionRequest $request)
    {
        try {
            $data = $request->validated();

            $updated = $this->studentRepository->bulkUpdateClass(
                $data['student_ids'],
                (int) $data['target_class_id']
            );

            Log::info('Bulk promote completed', [
                'count' => $updated->count(),
                'target_class_id' => (int) $data['target_class_id'],
                'student_ids' => $data['student_ids'],
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('students.index')
                ->with('success', $updated->count() . ' students promoted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to bulk promote students', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to promote selected students. Please try again.');
        }
    }

    /**
     * Bulk transfer students to a target class (possibly different department).
     */
    public function bulkTransfer(BulkStudentActionRequest $request)
    {
        try {
            $data = $request->validated();

            $updated = $this->studentRepository->bulkUpdateClass(
                $data['student_ids'],
                (int) $data['target_class_id']
            );

            Log::info('Bulk transfer completed', [
                'count' => $updated->count(),
                'target_class_id' => (int) $data['target_class_id'],
                'student_ids' => $data['student_ids'],
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('students.index')
                ->with('success', $updated->count() . ' students transferred successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to bulk transfer students', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to transfer selected students. Please try again.');
        }
    }

    /**
     * Bulk status update (activate/deactivate) for selected students.
     */
    public function bulkStatusUpdate(BulkStudentActionRequest $request)
    {
        try {
            $data = $request->validated();

            $updated = $this->studentRepository->bulkUpdateStatus(
                $data['student_ids'],
                (bool) $data['status']
            );

            Log::info('Bulk status update completed', [
                'count' => $updated->count(),
                'status' => (bool) $data['status'],
                'student_ids' => $data['student_ids'],
                'user_id' => auth()->id(),
            ]);

            $statusText = $data['status'] ? 'activated' : 'deactivated';

            return redirect()->route('students.index')
                ->with('success', $updated->count() . " students {$statusText} successfully.");
        } catch (\Exception $e) {
            Log::error('Failed to bulk update students status', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to update status for selected students. Please try again.');
        }
    }
}
