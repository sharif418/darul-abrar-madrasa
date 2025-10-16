<?php

namespace App\Repositories;

use App\Models\TeacherAttendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherAttendanceRepository
{
    protected $teacherAttendance;

    public function __construct(TeacherAttendance $teacherAttendance)
    {
        $this->teacherAttendance = $teacherAttendance;
    }

    /**
     * Get all teacher attendance records with filters and pagination.
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->teacherAttendance->with(['teacher.user', 'teacher.department', 'markedBy']);

        // Apply teacher filter
        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        // Apply department filter
        if (!empty($filters['department_id'])) {
            $query->whereHas('teacher.department', function ($q) use ($filters) {
                $q->where('id', $filters['department_id']);
            });
        }

        // Apply date filter
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        // Apply date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Order by date descending
        $query->latest('date');

        return $query->paginate($perPage);
    }

    /**
     * Get summary statistics for teacher attendance.
     */
    public function getSummary($filters)
    {
        $query = $this->teacherAttendance->query();

        // Apply same filters as getAllWithFilters
        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('teacher.department', function ($q) use ($filters) {
                $q->where('id', $filters['department_id']);
            });
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $attendances = $query->get();

        // Calculate statistics
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $leaveCount = $attendances->where('status', 'leave')->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();
        $lateCount = $attendances->filter(fn($a) => $a->isLate())->count();
        $earlyLeaveCount = $attendances->filter(fn($a) => $a->isEarlyLeave())->count();
        $totalCount = $attendances->count();

        // Calculate attendance rate
        $attendanceRate = $totalCount > 0 
            ? round(($presentCount + $halfDayCount) / $totalCount * 100, 2) 
            : 0;

        // Calculate average working hours
        $workingHoursRecords = $attendances->filter(function ($a) {
            return $a->check_in_time && $a->check_out_time;
        });

        $averageWorkingHours = $workingHoursRecords->count() > 0
            ? round($workingHoursRecords->avg(fn($a) => $a->getWorkingHours()), 2)
            : 0;

        return [
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'leaveCount' => $leaveCount,
            'halfDayCount' => $halfDayCount,
            'lateCount' => $lateCount,
            'earlyLeaveCount' => $earlyLeaveCount,
            'totalCount' => $totalCount,
            'attendanceRate' => $attendanceRate,
            'averageWorkingHours' => $averageWorkingHours,
        ];
    }

    /**
     * Get attendance trends for dashboard chart.
     */
    public function getAttendanceTrends($filters)
    {
        // Determine date range
        $dateFrom = !empty($filters['date_from']) 
            ? Carbon::parse($filters['date_from']) 
            : now()->subDays(6);
        
        $dateTo = !empty($filters['date_to']) 
            ? Carbon::parse($filters['date_to']) 
            : now();

        $query = $this->teacherAttendance->query()
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Apply other filters
        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('teacher.department', function ($q) use ($filters) {
                $q->where('id', $filters['department_id']);
            });
        }

        $attendances = $query->get();

        // Group by date and status
        $trends = [];
        $dates = [];
        
        $currentDate = $dateFrom->copy();
        while ($currentDate <= $dateTo) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('M d');
            
            $dayAttendances = $attendances->filter(function ($a) use ($dateStr) {
                return $a->date->format('Y-m-d') === $dateStr;
            });

            $trends[$dateStr] = [
                'present' => $dayAttendances->where('status', 'present')->count(),
                'absent' => $dayAttendances->where('status', 'absent')->count(),
                'leave' => $dayAttendances->where('status', 'leave')->count(),
                'half_day' => $dayAttendances->where('status', 'half_day')->count(),
            ];

            $currentDate->addDay();
        }

        // Format for Chart.js
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => array_column($trends, 'present'),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => 'Absent',
                    'data' => array_column($trends, 'absent'),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Leave',
                    'data' => array_column($trends, 'leave'),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Half Day',
                    'data' => array_column($trends, 'half_day'),
                    'borderColor' => 'rgb(234, 179, 8)',
                    'backgroundColor' => 'rgba(234, 179, 8, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Check if attendance already exists for a date.
     */
    public function checkExisting($date)
    {
        return $this->teacherAttendance->whereDate('date', $date)->count();
    }

    /**
     * Store bulk teacher attendance records.
     */
    public function storeBulk($date, $teacherData, $markedBy)
    {
        return DB::transaction(function () use ($date, $teacherData, $markedBy) {
            $count = 0;

            foreach ($teacherData as $teacherId => $data) {
                $this->teacherAttendance->updateOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'date' => $date,
                    ],
                    [
                        'status' => $data['status'],
                        'check_in_time' => $data['check_in_time'] ?? null,
                        'check_out_time' => $data['check_out_time'] ?? null,
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
     * Update teacher attendance record.
     */
    public function update($attendance, $data)
    {
        $attendance->update($data);

        return $attendance->fresh(['teacher.user', 'teacher.department', 'markedBy']);
    }

    /**
     * Get teacher statistics for a date range.
     */
    public function getTeacherStats($teacherId, $dateFrom = null, $dateTo = null)
    {
        $query = $this->teacherAttendance->where('teacher_id', $teacherId);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        }

        $attendances = $query->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $leaveDays = $attendances->where('status', 'leave')->count();
        $halfDays = $attendances->where('status', 'half_day')->count();
        $lateDays = $attendances->filter(fn($a) => $a->isLate())->count();
        $earlyLeaveDays = $attendances->filter(fn($a) => $a->isEarlyLeave())->count();

        $attendanceRate = $totalDays > 0 
            ? round(($presentDays + $halfDays) / $totalDays * 100, 2) 
            : 0;

        $workingHoursRecords = $attendances->filter(function ($a) {
            return $a->check_in_time && $a->check_out_time;
        });

        $averageWorkingHours = $workingHoursRecords->count() > 0
            ? round($workingHoursRecords->avg(fn($a) => $a->getWorkingHours()), 2)
            : 0;

        return [
            'totalDays' => $totalDays,
            'presentDays' => $presentDays,
            'absentDays' => $absentDays,
            'leaveDays' => $leaveDays,
            'halfDays' => $halfDays,
            'lateDays' => $lateDays,
            'earlyLeaveDays' => $earlyLeaveDays,
            'attendanceRate' => $attendanceRate,
            'averageWorkingHours' => $averageWorkingHours,
        ];
    }

    /**
     * Get department statistics for a date range.
     */
    public function getDepartmentStats($departmentId, $dateFrom = null, $dateTo = null)
    {
        $query = $this->teacherAttendance->whereHas('teacher', function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });

        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        }

        $attendances = $query->get();

        $totalRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $leaveCount = $attendances->where('status', 'leave')->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();

        $attendanceRate = $totalRecords > 0 
            ? round(($presentCount + $halfDayCount) / $totalRecords * 100, 2) 
            : 0;

        return [
            'totalRecords' => $totalRecords,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'leaveCount' => $leaveCount,
            'halfDayCount' => $halfDayCount,
            'attendanceRate' => $attendanceRate,
        ];
    }

    /**
     * Get monthly report for all teachers.
     */
    public function getMonthlyReport($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = $this->teacherAttendance
            ->with(['teacher.user', 'teacher.department'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $report = [];

        $teacherIds = $attendances->pluck('teacher_id')->unique();

        foreach ($teacherIds as $teacherId) {
            $teacherAttendances = $attendances->where('teacher_id', $teacherId);
            $teacher = $teacherAttendances->first()->teacher;

            $report[$teacherId] = [
                'teacher' => $teacher,
                'totalDays' => $teacherAttendances->count(),
                'presentDays' => $teacherAttendances->where('status', 'present')->count(),
                'absentDays' => $teacherAttendances->where('status', 'absent')->count(),
                'leaveDays' => $teacherAttendances->where('status', 'leave')->count(),
                'halfDays' => $teacherAttendances->where('status', 'half_day')->count(),
                'lateDays' => $teacherAttendances->filter(fn($a) => $a->isLate())->count(),
                'earlyLeaveDays' => $teacherAttendances->filter(fn($a) => $a->isEarlyLeave())->count(),
            ];
        }

        return $report;
    }
}
