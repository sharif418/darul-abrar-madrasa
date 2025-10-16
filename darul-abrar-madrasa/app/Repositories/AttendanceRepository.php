<?php

namespace App\Repositories;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceRepository
{
    protected $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    /**
     * Get all attendance records with filters and pagination
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->attendance->with(['student.user', 'class', 'markedBy']);

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        // Date filter
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('date')->paginate($perPage);
    }

    /**
     * Get attendance summary for a class and date
     */
    public function getSummary($classId, $date)
    {
        $attendances = $this->attendance
            ->where('class_id', $classId)
            ->whereDate('date', $date)
            ->get();

        return [
            'presentCount' => $attendances->where('status', 'present')->count(),
            'absentCount' => $attendances->where('status', 'absent')->count(),
            'lateCount' => $attendances->where('status', 'late')->count(),
            'leaveCount' => $attendances->where('status', 'leave')->count(),
            'halfDayCount' => $attendances->where('status', 'half_day')->count(),
            'totalCount' => $attendances->count(),
        ];
    }

    /**
     * Check if attendance already exists for class and date
     */
    public function checkExisting($classId, $date)
    {
        return $this->attendance
            ->where('class_id', $classId)
            ->whereDate('date', $date)
            ->count();
    }

    /**
     * Store bulk attendance records
     */
    public function storeBulk($classId, $date, $studentData, $markedBy)
    {
        return DB::transaction(function () use ($classId, $date, $studentData, $markedBy) {
            $count = 0;

            foreach ($studentData as $studentId => $data) {
                $this->attendance->updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'date' => $date,
                    ],
                    [
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => $markedBy,
                    ]
                );
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update an attendance record
     */
    public function update($attendance, $data)
    {
        $attendance->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'marked_by' => Auth::id(),
        ]);

        return $attendance->fresh(['student.user', 'class', 'markedBy']);
    }

    /**
     * Get student attendance for a specific month
     */
    public function getStudentAttendance($studentId, $month, $year, $perPage = 31)
    {
        $query = $this->attendance
            ->with(['class', 'markedBy'])
            ->where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    /**
     * Get student attendance statistics
     */
    public function getStudentStats($studentId)
    {
        $attendances = $this->attendance
            ->where('student_id', $studentId)
            ->get();

        $totalCount = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $leaveCount = $attendances->where('status', 'leave')->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();

        $attendanceRate = $totalCount > 0 ? ($presentCount / $totalCount) * 100 : 0;

        return [
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'lateCount' => $lateCount,
            'leaveCount' => $leaveCount,
            'halfDayCount' => $halfDayCount,
            'totalCount' => $totalCount,
            'attendanceRate' => round($attendanceRate, 2),
        ];
    }
}
