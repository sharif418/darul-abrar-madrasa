<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance records.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['student.user', 'class', 'markedBy']);

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->latest()->paginate(15);
        $classes = ClassRoom::with('department')->get();

        // Calculate attendance summary if class and date are provided
        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $leaveCount = 0;

        if ($request->filled('class_id') && $request->filled('date')) {
            $presentCount = Attendance::where('class_id', $request->class_id)
                ->whereDate('date', $request->date)
                ->where('status', 'present')
                ->count();
                
            $absentCount = Attendance::where('class_id', $request->class_id)
                ->whereDate('date', $request->date)
                ->where('status', 'absent')
                ->count();
                
            $lateCount = Attendance::where('class_id', $request->class_id)
                ->whereDate('date', $request->date)
                ->where('status', 'late')
                ->count();
                
            $leaveCount = Attendance::where('class_id', $request->class_id)
                ->whereDate('date', $request->date)
                ->where('status', 'leave')
                ->count();
        }

        return view('attendances.index', compact(
            'attendances', 
            'classes', 
            'presentCount', 
            'absentCount', 
            'lateCount', 
            'leaveCount'
        ));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        $classes = ClassRoom::with('department')->get();
        return view('attendances.select-class', compact('classes'));
    }

    /**
     * Show the form for creating attendance records for a specific class.
     */
    public function createByClass($class_id)
    {
        $class = ClassRoom::with('department')->findOrFail($class_id);
        $students = Student::where('class_id', $class_id)
            ->where('is_active', true)
            ->with('user')
            ->get();
        $date = now();

        // Check if attendance already taken for today
        $existingCount = Attendance::where('class_id', $class_id)
            ->whereDate('date', $date)
            ->count();

        if ($existingCount > 0) {
            return redirect()->route('attendances.index', ['class_id' => $class_id, 'date' => $date->format('Y-m-d')])
                ->with('info', 'Attendance for this class has already been taken today. You can edit the existing records.');
        }

        return view('attendances.create', compact('class', 'students', 'date'));
    }

    /**
     * Store multiple attendance records in storage.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'status' => 'required|array',
            'status.*' => 'in:present,absent,late,leave,half_day',
        ]);

        DB::beginTransaction();

        try {
            $class_id = $request->class_id;
            $date = $request->date;
            $student_ids = $request->student_ids;
            $statuses = $request->status;
            $remarks = $request->remarks ?? [];

            foreach ($student_ids as $student_id) {
                // Check if attendance already exists for this student on this date
                $existingAttendance = Attendance::where('student_id', $student_id)
                    ->whereDate('date', $date)
                    ->first();

                if ($existingAttendance) {
                    // Update existing attendance
                    $existingAttendance->update([
                        'status' => $statuses[$student_id] ?? 'absent',
                        'remarks' => $remarks[$student_id] ?? null,
                        'marked_by' => Auth::id(),
                    ]);
                } else {
                    // Create new attendance record
                    Attendance::create([
                        'student_id' => $student_id,
                        'class_id' => $class_id,
                        'date' => $date,
                        'status' => $statuses[$student_id] ?? 'absent',
                        'remarks' => $remarks[$student_id] ?? null,
                        'marked_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('attendances.index', ['class_id' => $class_id, 'date' => $date])
                ->with('success', 'Attendance records saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save attendance records. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['student.user', 'class', 'markedBy']);
        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load(['student.user', 'class', 'markedBy']);
        return view('attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,leave,half_day',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
            'marked_by' => Auth::id(),
        ]);

        return redirect()->route('attendances.index', ['class_id' => $attendance->class_id, 'date' => $attendance->date->format('Y-m-d')])
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $class_id = $attendance->class_id;
        $date = $attendance->date->format('Y-m-d');
        
        $attendance->delete();

        return redirect()->route('attendances.index', ['class_id' => $class_id, 'date' => $date])
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Display the student's own attendance records.
     */
    public function myAttendance(Request $request)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            abort(403, 'You are not registered as a student.');
        }

        // Get month and year from request or use current month/year
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        
        // Get attendance records for the selected month
        $query = Attendance::where('student_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('markedBy');
            
        $attendances = $query->orderBy('date', 'desc')->paginate(31);
        
        // Calculate attendance statistics
        $presentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();
            
        $absentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->count();
            
        $lateCount = Attendance::where('student_id', $student->id)
            ->where('status', 'late')
            ->count();
            
        $leaveCount = Attendance::where('student_id', $student->id)
            ->where('status', 'leave')
            ->count();
            
        $totalAttendance = $presentCount + $absentCount + $lateCount + $leaveCount;
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;
        
        return view('attendances.my-attendance', compact(
            'attendances',
            'presentCount',
            'absentCount',
            'lateCount',
            'leaveCount',
            'attendanceRate'
        ));
    }
}