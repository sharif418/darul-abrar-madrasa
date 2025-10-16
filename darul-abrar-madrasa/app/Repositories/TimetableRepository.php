<?php

namespace App\Repositories;

use App\Models\Period;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;

class TimetableRepository
{
    protected $timetable;
    protected $timetableEntry;

    public function __construct(Timetable $timetable, TimetableEntry $timetableEntry)
    {
        $this->timetable = $timetable;
        $this->timetableEntry = $timetableEntry;
    }

    /**
     * Get timetable statistics.
     */
    public function getTimetableStats($timetableId)
    {
        $entries = TimetableEntry::where('timetable_id', $timetableId)->get();

        return [
            'total_entries' => $entries->count(),
            'entries_by_day' => $entries->groupBy('day_of_week')->map->count()->toArray(),
            'classes_count' => $entries->pluck('class_id')->unique()->count(),
            'teachers_count' => $entries->whereNotNull('teacher_id')->pluck('teacher_id')->unique()->count(),
            'entries_without_teacher' => $entries->whereNull('teacher_id')->count(),
            'entries_without_room' => $entries->whereNull('room_number')->count(),
        ];
    }

    /**
     * Get entries with filters applied.
     */
    public function getEntriesWithFilters($timetableId, $filters, $perPage = 20)
    {
        $query = TimetableEntry::where('timetable_id', $timetableId)
            ->with(['class', 'subject', 'teacher.user', 'period']);

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        return $query->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('period_id')
            ->paginate($perPage);
    }

    /**
     * Get weekly grid structure for timetable display.
     */
    public function getWeeklyGrid($timetableId, $filters = [])
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        // Get all periods ordered by day and order
        $periods = Period::active()
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('order')
            ->get()
            ->groupBy('day_of_week');

        // Get entries with filters
        $query = TimetableEntry::where('timetable_id', $timetableId)
            ->with(['class', 'subject', 'teacher.user', 'period']);

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        $entries = $query->get();

        // Build grid structure
        $grid = [];
        foreach ($days as $day) {
            $grid[$day] = [];
            if (isset($periods[$day])) {
                foreach ($periods[$day] as $period) {
                    $dayEntries = $entries->where('day_of_week', $day)
                        ->where('period_id', $period->id);
                    
                    if (empty($filters['class_id'])) {
                        // Group by class if no class filter
                        $grid[$day][$period->id] = $dayEntries->groupBy('class_id')->toArray();
                    } else {
                        // Single entry if class filter applied
                        $grid[$day][$period->id] = $dayEntries->first();
                    }
                }
            }
        }

        return [
            'days' => $days,
            'periods' => $periods,
            'grid' => $grid,
            'filters' => $filters,
        ];
    }

    /**
     * Get class schedule.
     */
    public function getClassSchedule($timetableId, $classId)
    {
        $entries = TimetableEntry::where('timetable_id', $timetableId)
            ->where('class_id', $classId)
            ->with(['subject', 'teacher.user', 'period'])
            ->get();

        $days = $entries->groupBy('day_of_week');
        
        // Order entries by period order within each day
        $schedule = [];
        foreach ($days as $day => $dayEntries) {
            $schedule[$day] = $dayEntries->sortBy(function ($entry) {
                return $entry->period ? $entry->period->order : 999;
            })->values();
        }

        return [
            'days' => array_keys($schedule),
            'schedule' => $schedule,
            'totalPeriods' => $entries->count(),
        ];
    }

    /**
     * Get teacher schedule.
     */
    public function getTeacherSchedule($timetableId, $teacherId)
    {
        $entries = TimetableEntry::where('timetable_id', $timetableId)
            ->where('teacher_id', $teacherId)
            ->with(['class', 'subject', 'period'])
            ->get();

        $days = $entries->groupBy('day_of_week');
        
        // Order entries by period order within each day
        $schedule = [];
        foreach ($days as $day => $dayEntries) {
            $schedule[$day] = $dayEntries->sortBy(function ($entry) {
                return $entry->period ? $entry->period->order : 999;
            })->values();
        }

        // Calculate statistics
        $stats = [
            'total_periods' => $entries->count(),
            'classes_taught' => $entries->pluck('class_id')->unique()->count(),
            'subjects_taught' => $entries->pluck('subject_id')->unique()->count(),
        ];

        return [
            'days' => array_keys($schedule),
            'schedule' => $schedule,
            'stats' => $stats,
        ];
    }

    /**
     * Detect conflicts in timetable.
     */
    public function detectConflicts($timetableId)
    {
        $entries = TimetableEntry::where('timetable_id', $timetableId)
            ->with(['class', 'subject', 'teacher.user', 'period'])
            ->get();

        $teacherConflicts = [];
        $roomConflicts = [];
        $classConflicts = [];

        foreach ($entries as $entry) {
            // Check for teacher conflicts
            if ($entry->teacher_id) {
                $conflicts = $entries->where('id', '!=', $entry->id)
                    ->where('teacher_id', $entry->teacher_id)
                    ->where('day_of_week', $entry->day_of_week)
                    ->where('period_id', $entry->period_id);

                if ($conflicts->count() > 0) {
                    $teacherConflicts[] = [
                        'entry' => $entry,
                        'conflicts_with' => $conflicts->values(),
                    ];
                }
            }

            // Check for room conflicts
            if ($entry->room_number) {
                $conflicts = $entries->where('id', '!=', $entry->id)
                    ->where('room_number', $entry->room_number)
                    ->where('day_of_week', $entry->day_of_week)
                    ->where('period_id', $entry->period_id);

                if ($conflicts->count() > 0) {
                    $roomConflicts[] = [
                        'entry' => $entry,
                        'conflicts_with' => $conflicts->values(),
                    ];
                }
            }

            // Check for class conflicts (same class, same time)
            $conflicts = $entries->where('id', '!=', $entry->id)
                ->where('class_id', $entry->class_id)
                ->where('day_of_week', $entry->day_of_week)
                ->where('period_id', $entry->period_id);

            if ($conflicts->count() > 0) {
                $classConflicts[] = [
                    'entry' => $entry,
                    'conflicts_with' => $conflicts->values(),
                ];
            }
        }

        return [
            'teacherConflicts' => collect($teacherConflicts)->unique('entry.id')->values(),
            'roomConflicts' => collect($roomConflicts)->unique('entry.id')->values(),
            'classConflicts' => collect($classConflicts)->unique('entry.id')->values(),
            'totalConflicts' => count($teacherConflicts) + count($roomConflicts) + count($classConflicts),
        ];
    }

    /**
     * Get subjects for a specific class.
     */
    public function getSubjectsForClass($classId)
    {
        return Subject::where('class_id', $classId)
            ->with('teacher.user')
            ->get();
    }

    /**
     * Get periods for a specific day.
     */
    public function getPeriodsForDay($dayOfWeek)
    {
        return Period::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Bulk create entries.
     */
    public function bulkCreateEntries($timetableId, $entriesData)
    {
        return DB::transaction(function () use ($timetableId, $entriesData) {
            $created = 0;

            foreach ($entriesData as $entryData) {
                $entryData['timetable_id'] = $timetableId;
                
                // Check for conflicts before creating
                $hasConflict = $this->checkEntryConflict($entryData);
                
                if ($hasConflict) {
                    throw new \Exception("Conflict detected for entry: " . json_encode($entryData));
                }

                TimetableEntry::create($entryData);
                $created++;
            }

            return $created;
        });
    }

    /**
     * Copy timetable with all entries.
     */
    public function copyTimetable($sourceTimetableId, $newTimetableData)
    {
        return DB::transaction(function () use ($sourceTimetableId, $newTimetableData) {
            // Create new timetable
            $newTimetable = Timetable::create($newTimetableData);

            // Get all entries from source
            $sourceEntries = TimetableEntry::where('timetable_id', $sourceTimetableId)->get();

            // Copy entries
            foreach ($sourceEntries as $entry) {
                TimetableEntry::create([
                    'timetable_id' => $newTimetable->id,
                    'class_id' => $entry->class_id,
                    'subject_id' => $entry->subject_id,
                    'teacher_id' => $entry->teacher_id,
                    'period_id' => $entry->period_id,
                    'day_of_week' => $entry->day_of_week,
                    'room_number' => $entry->room_number,
                    'notes' => $entry->notes,
                    'is_active' => $entry->is_active,
                ]);
            }

            return [
                'timetable' => $newTimetable,
                'entries_copied' => $sourceEntries->count(),
            ];
        });
    }

    /**
     * Get utilization statistics.
     */
    public function getUtilizationStats($timetableId)
    {
        $entries = TimetableEntry::where('timetable_id', $timetableId)
            ->with(['teacher', 'class', 'period'])
            ->get();

        // Teacher utilization
        $teacherUtilization = [];
        $teacherEntries = $entries->whereNotNull('teacher_id')->groupBy('teacher_id');
        foreach ($teacherEntries as $teacherId => $teacherEntries) {
            $totalPeriods = Period::active()->count();
            $usedPeriods = $teacherEntries->count();
            $teacherUtilization[$teacherId] = [
                'teacher' => $teacherEntries->first()->teacher,
                'used_periods' => $usedPeriods,
                'total_periods' => $totalPeriods,
                'percentage' => $totalPeriods > 0 ? round(($usedPeriods / $totalPeriods) * 100, 2) : 0,
            ];
        }

        // Class utilization
        $classUtilization = [];
        $classEntries = $entries->groupBy('class_id');
        foreach ($classEntries as $classId => $classEntries) {
            $totalPeriods = Period::active()->count();
            $usedPeriods = $classEntries->count();
            $classUtilization[$classId] = [
                'class' => $classEntries->first()->class,
                'used_periods' => $usedPeriods,
                'total_periods' => $totalPeriods,
                'percentage' => $totalPeriods > 0 ? round(($usedPeriods / $totalPeriods) * 100, 2) : 0,
            ];
        }

        // Period utilization
        $periodUtilization = [];
        $periodEntries = $entries->groupBy('period_id');
        foreach ($periodEntries as $periodId => $periodEntries) {
            $periodUtilization[$periodId] = [
                'period' => $periodEntries->first()->period,
                'classes_using' => $periodEntries->pluck('class_id')->unique()->count(),
                'total_entries' => $periodEntries->count(),
            ];
        }

        // Room utilization
        $roomUtilization = [];
        $roomEntries = $entries->whereNotNull('room_number')->groupBy('room_number');
        foreach ($roomEntries as $room => $roomEntries) {
            $roomUtilization[$room] = [
                'room' => $room,
                'usage_count' => $roomEntries->count(),
                'classes' => $roomEntries->pluck('class_id')->unique()->count(),
            ];
        }

        return [
            'teacher_utilization' => $teacherUtilization,
            'class_utilization' => $classUtilization,
            'period_utilization' => $periodUtilization,
            'room_utilization' => $roomUtilization,
        ];
    }

    /**
     * Check if entry has conflicts.
     */
    protected function checkEntryConflict($entryData)
    {
        $query = TimetableEntry::where('timetable_id', $entryData['timetable_id'])
            ->where('day_of_week', $entryData['day_of_week'])
            ->where('period_id', $entryData['period_id']);

        // Check teacher conflict
        if (!empty($entryData['teacher_id'])) {
            if ($query->clone()->where('teacher_id', $entryData['teacher_id'])->exists()) {
                return true;
            }
        }

        // Check room conflict
        if (!empty($entryData['room_number'])) {
            if ($query->clone()->where('room_number', $entryData['room_number'])->exists()) {
                return true;
            }
        }

        // Check class conflict
        if ($query->clone()->where('class_id', $entryData['class_id'])->exists()) {
            return true;
        }

        return false;
    }
}
