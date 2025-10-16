<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherAttendanceRequest;
use App\Http\Requests\UpdateTeacherAttendanceRequest;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Repositories\TeacherAttendanceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeacherAttendanceController extends Controller
{
    protected $teacherAttendanceRepository;

    public function __construct(TeacherAttendanceRepository $teacherAttendanceRepository)
    {
        $this->teacherAttendanceRepository = $teacherAttendanceRepository;
    }

    /**
     * Display a listing of teacher attendance records.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TeacherAttendance::class);

        try {
            // Build filters array from request
            $filters = [
                'teacher_id' => $request->input('teacher_id'),
                'department_id' => $request->input('department_id'),
                'date' => $request->input('date'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'status' => $request->input('status'),
            ];

            // Get paginated attendance records with filters
            $attendances = $this->teacherAttendanceRepository->getAllWithFilters($filters, 15);

            // Load departments and teachers for filter dropdowns
            $departments = Department::with('teachers')->get();
            $teachers = Teacher::where('is_active', true)->with('user')->get();

            // Calculate summary statistics if filters are applied
            $summary = null;
            if (array_filter($filters)) {
                $summary = $this->teacherAttendanceRepository->getSummary($filters);
            }

            // Calculate attendance trends for dashboard
            $trends = $this->teacherAttendanceRepository->getAttendanceTrends($filters);

            return view('teacher-attendances.index', compact(
                'attendances',
                'departments',
                'teachers',
                'summary',
                'trends'
            ));
        } catch (\Exception $e) {
            Log::error('Error fetching teacher attendance records', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to load teacher attendance records. Please try again.');
        }
    }

    /**
     * Show the form for creating new teacher attendance records.
     */
    public function create()
    {
        $this->authorize('create', TeacherAttendance::class);

        try {
            // Load active teachers with relationships
            $teachers = Teacher::where('is_active', true)
                ->with(['user', 'department'])
                ->get();

            // Set default date to today
            $date = now();

            // Check if attendance already exists for today
            $existingCount = $this->teacherAttendanceRepository->checkExisting($date->format('Y-m-d'));
            
            // Get list of teacher IDs with existing records for today
            $existingTeacherIds = [];
            if ($existingCount > 0) {
                $existingTeacherIds = \App\Models\TeacherAttendance::whereDate('date', $date->format('Y-m-d'))
                    ->pluck('teacher_id')
                    ->toArray();
            }

            return view('teacher-attendances.create', compact('teachers', 'date', 'existingTeacherIds'));
        } catch (\Exception $e) {
            Log::error('Error loading teacher attendance create form', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to load attendance form. Please try again.');
        }
    }

    /**
     * Store bulk teacher attendance records.
     */
    public function storeBulk(StoreTeacherAttendanceRequest $request)
    {
        $this->authorize('create', TeacherAttendance::class);

        try {
            $validated = $request->validated();

            // Build teacher data array
            $teacherData = [];
            foreach ($validated['teacher_ids'] as $teacherId) {
                $teacherData[$teacherId] = [
                    'status' => $validated['status'][$teacherId] ?? 'present',
                    'check_in_time' => $validated['check_in_time'][$teacherId] ?? null,
                    'check_out_time' => $validated['check_out_time'][$teacherId] ?? null,
                    'remarks' => $validated['remarks'][$teacherId] ?? null,
                ];
            }

            // Store bulk attendance
            $count = $this->teacherAttendanceRepository->storeBulk(
                $validated['date'],
                $teacherData,
                Auth::id()
            );

            Log::info('Teacher attendance records saved successfully', [
                'date' => $validated['date'],
                'count' => $count,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('teacher-attendances.index')
                ->with('success', "Teacher attendance records saved successfully. {$count} records processed.");
        } catch (\Exception $e) {
            Log::error('Error storing bulk teacher attendance', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save attendance records. Please try again.');
        }
    }

    /**
     * Display the specified teacher attendance record.
     */
    public function show(TeacherAttendance $teacherAttendance)
    {
        $this->authorize('view', $teacherAttendance);

        try {
            // Eager load relationships
            $teacherAttendance->load(['teacher.user', 'teacher.department', 'markedBy']);

            // Get teacher statistics for current month
            $teacherStats = $this->teacherAttendanceRepository->getTeacherStats(
                $teacherAttendance->teacher_id,
                now()->startOfMonth()->format('Y-m-d'),
                now()->endOfMonth()->format('Y-m-d')
            );

            return view('teacher-attendances.show', compact('teacherAttendance', 'teacherStats'));
        } catch (\Exception $e) {
            Log::error('Error displaying teacher attendance record', [
                'error' => $e->getMessage(),
                'attendance_id' => $teacherAttendance->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to load attendance record. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified teacher attendance record.
     */
    public function edit(TeacherAttendance $teacherAttendance)
    {
        $this->authorize('update', $teacherAttendance);

        try {
            // Eager load relationships
            $teacherAttendance->load(['teacher.user', 'teacher.department', 'markedBy']);

            return view('teacher-attendances.edit', compact('teacherAttendance'));
        } catch (\Exception $e) {
            Log::error('Error loading teacher attendance edit form', [
                'error' => $e->getMessage(),
                'attendance_id' => $teacherAttendance->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to load edit form. Please try again.');
        }
    }

    /**
     * Update the specified teacher attendance record.
     */
    public function update(UpdateTeacherAttendanceRequest $request, TeacherAttendance $teacherAttendance)
    {
        $this->authorize('update', $teacherAttendance);

        try {
            $validated = $request->validated();

            // Add marked_by to data
            $validated['marked_by'] = Auth::id();

            // Update attendance record
            $this->teacherAttendanceRepository->update($teacherAttendance, $validated);

            Log::info('Teacher attendance record updated successfully', [
                'attendance_id' => $teacherAttendance->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('teacher-attendances.index')
                ->with('success', 'Teacher attendance record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating teacher attendance record', [
                'error' => $e->getMessage(),
                'attendance_id' => $teacherAttendance->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update attendance record. Please try again.');
        }
    }

    /**
     * Remove the specified teacher attendance record.
     */
    public function destroy(TeacherAttendance $teacherAttendance)
    {
        $this->authorize('delete', $teacherAttendance);

        try {
            $attendanceId = $teacherAttendance->id;
            $date = $teacherAttendance->date->format('Y-m-d');

            $teacherAttendance->delete();

            Log::info('Teacher attendance record deleted successfully', [
                'attendance_id' => $attendanceId,
                'date' => $date,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('teacher-attendances.index')
                ->with('success', 'Teacher attendance record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting teacher attendance record', [
                'error' => $e->getMessage(),
                'attendance_id' => $teacherAttendance->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete attendance record. Please try again.');
        }
    }
}
