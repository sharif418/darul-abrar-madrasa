<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Repositories\AttendanceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    protected $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Display a listing of the attendance records.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Attendance::class);
        try {
            $filters = [
                'class_id' => $request->class_id,
                'date' => $request->date,
                'status' => $request->status,
            ];

            $attendances = $this->attendanceRepository->getAllWithFilters($filters, 15);
            $classes = ClassRoom::with('department')->get();

            // Calculate attendance summary if class and date are provided
            $summary = null;
            if ($request->filled('class_id') && $request->filled('date')) {
                $summary = $this->attendanceRepository->getSummary($request->class_id, $request->date);
            }

            return view('attendances.index', compact('attendances', 'classes', 'summary'));
        } catch (\Exception $e) {
            Log::error('Failed to load attendance records', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load attendance records. Please try again.');
        }
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        $this->authorize('create', \App\Models\Attendance::class);
        $classes = ClassRoom::with('department')->get();
        return view('attendances.select-class', compact('classes'));
    }

    /**
     * Show the form for creating attendance records for a specific class.
     */
    public function createByClass($class_id)
    {
        $this->authorize('createForClass', [\App\Models\Attendance::class, (int) $class_id]);
        try {
            $class = ClassRoom::with('department')->findOrFail($class_id);
            $students = Student::where('class_id', $class_id)
                ->where('is_active', true)
                ->with('user')
                ->get();
            $date = now();

            // Check if attendance already taken for today
            $existingCount = $this->attendanceRepository->checkExisting($class_id, $date->format('Y-m-d'));

            if ($existingCount > 0) {
                return redirect()->route('attendances.index', [
                    'class_id' => $class_id, 
                    'date' => $date->format('Y-m-d')
                ])->with('info', 'Attendance for this class has already been taken today. You can edit the existing records.');
            }

            return view('attendances.create', compact('class', 'students', 'date'));
        } catch (\Exception $e) {
            Log::error('Failed to load attendance form', [
                'class_id' => $class_id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load attendance form. Please try again.');
        }
    }

    /**
     * Store multiple attendance records in storage.
     */
    public function storeBulk(StoreAttendanceRequest $request)
    {
        try {
            $validated = $request->validated();
            $this->authorize('createForClass', [\App\Models\Attendance::class, (int) $validated['class_id']]);
            
            $studentData = [];
            foreach ($validated['student_ids'] as $student_id) {
                $studentData[$student_id] = [
                    'status' => $validated['status'][$student_id] ?? 'absent',
                    'remarks' => $validated['remarks'][$student_id] ?? null,
                ];
            }

            $count = $this->attendanceRepository->storeBulk(
                $validated['class_id'],
                $validated['date'],
                $studentData,
                Auth::id()
            );

            Log::info('Bulk attendance records saved', [
                'class_id' => $validated['class_id'],
                'date' => $validated['date'],
                'count' => $count,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('attendances.index', [
                'class_id' => $validated['class_id'], 
                'date' => $validated['date']
            ])->with('success', "Attendance records saved successfully. {$count} records processed.");
        } catch (\Exception $e) {
            Log::error('Failed to save bulk attendance', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to save attendance records. Please try again.');
        }
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);
        try {
            $attendance->load(['student.user', 'class', 'markedBy']);
            return view('attendances.show', compact('attendance'));
        } catch (\Exception $e) {
            Log::error('Failed to load attendance details', [
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load attendance details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        $attendance->load(['student.user', 'class', 'markedBy']);
        return view('attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        $request->validate([
            'status' => 'required|in:present,absent,late,leave,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            $data = [
                'status' => $request->status,
                'remarks' => $request->remarks,
                'marked_by' => Auth::id(),
            ];

            $this->attendanceRepository->update($attendance, $data);

            Log::info('Attendance record updated', [
                'attendance_id' => $attendance->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('attendances.index', [
                'class_id' => $attendance->class_id, 
                'date' => $attendance->date->format('Y-m-d')
            ])->with('success', 'Attendance record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update attendance', [
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Failed to update attendance record. Please try again.');
        }
    }

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $this->authorize('delete', $attendance);
        try {
            $class_id = $attendance->class_id;
            $date = $attendance->date->format('Y-m-d');
            $attendanceId = $attendance->id;
            
            $attendance->delete();

            Log::info('Attendance record deleted', [
                'attendance_id' => $attendanceId,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('attendances.index', [
                'class_id' => $class_id, 
                'date' => $date
            ])->with('success', 'Attendance record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete attendance', [
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to delete attendance record. Please try again.');
        }
    }

    /**
     * Display the student's own attendance records.
     */
    public function myAttendance(Request $request)
    {
        $this->authorize('myAttendance', \App\Models\Attendance::class);
        try {
            $student = Auth::user()->student;
            
            if (!$student) {
                abort(403, 'You are not registered as a student.');
            }

            // Get month and year from request or use current month/year
            $month = $request->input('month', date('n'));
            $year = $request->input('year', date('Y'));
            
            // Get attendance records for the selected month
            $attendances = $this->attendanceRepository->getStudentAttendance(
                $student->id, 
                $month, 
                $year, 
                31
            );
            
            // Calculate attendance statistics
            $stats = $this->attendanceRepository->getStudentStats($student->id);
            
            return view('attendances.my-attendance', compact('attendances', 'stats', 'month', 'year'));
        } catch (\Exception $e) {
            Log::error('Failed to load student attendance', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load your attendance records. Please try again.');
        }
    }
}
