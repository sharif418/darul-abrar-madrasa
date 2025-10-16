<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimetableEntryRequest;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableEntryRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Models\ClassRoom;
use App\Models\Period;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Repositories\TimetableRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TimetableController extends Controller
{
    protected $timetableRepository;

    public function __construct(TimetableRepository $timetableRepository)
    {
        $this->timetableRepository = $timetableRepository;
    }

    /**
     * Display a listing of timetables.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Timetable::class);

            $query = Timetable::with(['creator', 'entries']);

            // Apply status filter
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'active':
                        $query->active();
                        break;
                    case 'current':
                        $query->current();
                        break;
                    case 'expired':
                        $query->expired();
                        break;
                    case 'upcoming':
                        $query->upcoming();
                        break;
                }
            }

            // Apply search filter
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            $timetables = $query->latest('effective_from')->paginate(15);

            return view('timetables.index', compact('timetables'));
        } catch (\Exception $e) {
            Log::error('Error fetching timetables', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load timetables. Please try again.');
        }
    }

    /**
     * Show the form for creating a new timetable.
     */
    public function create()
    {
        $this->authorize('create', Timetable::class);

        return view('timetables.create');
    }

    /**
     * Store a newly created timetable.
     */
    public function store(StoreTimetableRequest $request)
    {
        try {
            $this->authorize('create', Timetable::class);

            $data = $request->validated();
            $data['created_by'] = Auth::id();

            $timetable = Timetable::create($data);

            Log::info('Timetable created successfully', [
                'timetable_id' => $timetable->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('timetables.show', $timetable)->with('success', 'Timetable created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating timetable', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to create timetable. Please try again.');
        }
    }

    /**
     * Display the specified timetable.
     */
    public function show(Timetable $timetable)
    {
        try {
            $this->authorize('view', $timetable);

            $timetable->load(['creator', 'entries.class', 'entries.subject', 'entries.teacher.user', 'entries.period']);
            $stats = $this->timetableRepository->getTimetableStats($timetable->id);

            return view('timetables.show', compact('timetable', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error fetching timetable details', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load timetable details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified timetable.
     */
    public function edit(Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        return view('timetables.edit', compact('timetable'));
    }

    /**
     * Update the specified timetable.
     */
    public function update(UpdateTimetableRequest $request, Timetable $timetable)
    {
        try {
            $this->authorize('update', $timetable);

            $timetable->update($request->validated());

            Log::info('Timetable updated successfully', [
                'timetable_id' => $timetable->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('timetables.show', $timetable)->with('success', 'Timetable updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating timetable', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to update timetable. Please try again.');
        }
    }

    /**
     * Remove the specified timetable.
     */
    public function destroy(Timetable $timetable)
    {
        try {
            $this->authorize('delete', $timetable);

            if (!$timetable->canBeDeleted()) {
                return redirect()->back()->with('error', 'Cannot delete timetable. It may be currently active or have entries.');
            }

            $timetable->delete();

            Log::info('Timetable deleted successfully', [
                'timetable_id' => $timetable->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('timetables.index')->with('success', 'Timetable deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting timetable', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to delete timetable. Please try again.');
        }
    }

    /**
     * Display timetable entries with filters.
     */
    public function entries(Request $request, Timetable $timetable)
    {
        try {
            $this->authorize('view', $timetable);

            $filters = $request->only(['class_id', 'teacher_id', 'day_of_week', 'subject_id']);
            $entries = $this->timetableRepository->getEntriesWithFilters($timetable->id, $filters, 20);

            $classes = ClassRoom::active()->orderBy('name')->get();
            $teachers = Teacher::with('user')->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })->get();
            $periods = Period::active()->orderBy('day_of_week')->orderBy('order')->get();

            return view('timetables.entries.index', compact('entries', 'timetable', 'classes', 'teachers', 'periods'));
        } catch (\Exception $e) {
            Log::error('Error fetching timetable entries', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load timetable entries. Please try again.');
        }
    }

    /**
     * Show the form for creating a new entry.
     */
    public function createEntry(Timetable $timetable)
    {
        try {
            $this->authorize('createEntry', $timetable);

            $classes = ClassRoom::active()->orderBy('name')->get();
            $teachers = Teacher::with('user')->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })->get();
            $periods = Period::active()->orderBy('day_of_week')->orderBy('order')->get()->groupBy('day_of_week');
            $subjects = Subject::with('teacher')->orderBy('name')->get();

            return view('timetables.entries.create', compact('timetable', 'classes', 'teachers', 'periods', 'subjects'));
        } catch (\Exception $e) {
            Log::error('Error loading entry creation form', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load form. Please try again.');
        }
    }

    /**
     * Store a newly created entry.
     */
    public function storeEntry(StoreTimetableEntryRequest $request, Timetable $timetable)
    {
        try {
            $this->authorize('createEntry', $timetable);

            $entry = TimetableEntry::create($request->validated());

            Log::info('Timetable entry created successfully', [
                'entry_id' => $entry->id,
                'timetable_id' => $timetable->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('timetables.entries', $timetable)->with('success', 'Timetable entry created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating timetable entry', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to create entry. Please try again.');
        }
    }

    /**
     * Show the form for editing an entry.
     */
    public function editEntry(Timetable $timetable, TimetableEntry $entry)
    {
        try {
            $this->authorize('updateEntry', [$timetable, $entry]);

            $entry->load(['class', 'subject', 'teacher', 'period']);

            $classes = ClassRoom::active()->orderBy('name')->get();
            $teachers = Teacher::with('user')->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })->get();
            $periods = Period::active()->orderBy('day_of_week')->orderBy('order')->get()->groupBy('day_of_week');
            $subjects = Subject::with('teacher')->orderBy('name')->get();

            return view('timetables.entries.edit', compact('timetable', 'entry', 'classes', 'teachers', 'periods', 'subjects'));
        } catch (\Exception $e) {
            Log::error('Error loading entry edit form', [
                'entry_id' => $entry->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load form. Please try again.');
        }
    }

    /**
     * Update the specified entry.
     */
    public function updateEntry(UpdateTimetableEntryRequest $request, Timetable $timetable, TimetableEntry $entry)
    {
        try {
            $this->authorize('updateEntry', [$timetable, $entry]);

            $entry->update($request->validated());

            Log::info('Timetable entry updated successfully', [
                'entry_id' => $entry->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('timetables.entries', $timetable)->with('success', 'Timetable entry updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating timetable entry', [
                'entry_id' => $entry->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to update entry. Please try again.');
        }
    }

    /**
     * Remove the specified entry.
     */
    public function destroyEntry(Timetable $timetable, TimetableEntry $entry)
    {
        try {
            $this->authorize('deleteEntry', [$timetable, $entry]);

            $entry->delete();

            Log::info('Timetable entry deleted successfully', [
                'entry_id' => $entry->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('timetables.entries', $timetable)->with('success', 'Timetable entry deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting timetable entry', [
                'entry_id' => $entry->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to delete entry. Please try again.');
        }
    }

    /**
     * Display weekly grid view.
     */
    public function weeklyGrid(Request $request, Timetable $timetable)
    {
        try {
            $this->authorize('view', $timetable);

            $filters = $request->only(['class_id', 'teacher_id']);
            $grid = $this->timetableRepository->getWeeklyGrid($timetable->id, $filters);

            $classes = ClassRoom::active()->orderBy('name')->get();
            $teachers = Teacher::with('user')->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })->get();

            return view('timetables.views.weekly-grid', compact('timetable', 'grid', 'classes', 'teachers'));
        } catch (\Exception $e) {
            Log::error('Error generating weekly grid', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load weekly grid. Please try again.');
        }
    }

    /**
     * Display class-specific timetable.
     */
    public function classTimetable(Timetable $timetable, ClassRoom $class)
    {
        try {
            $this->authorize('viewClassTimetable', [$timetable, $class]);

            $schedule = $this->timetableRepository->getClassSchedule($timetable->id, $class->id);

            return view('timetables.views.class-timetable', compact('timetable', 'class', 'schedule'));
        } catch (\Exception $e) {
            Log::error('Error generating class timetable', [
                'timetable_id' => $timetable->id,
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load class timetable. Please try again.');
        }
    }

    /**
     * Display teacher-specific timetable.
     */
    public function teacherTimetable(Timetable $timetable, Teacher $teacher)
    {
        try {
            $this->authorize('viewTeacherTimetable', [$timetable, $teacher]);

            $schedule = $this->timetableRepository->getTeacherSchedule($timetable->id, $teacher->id);

            return view('timetables.views.teacher-timetable', compact('timetable', 'teacher', 'schedule'));
        } catch (\Exception $e) {
            Log::error('Error generating teacher timetable', [
                'timetable_id' => $timetable->id,
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load teacher timetable. Please try again.');
        }
    }

    /**
     * Display current teacher's own timetable.
     */
    public function myTimetable()
    {
        try {
            $this->authorize('viewMyTimetable', Timetable::class);

            $teacher = Auth::user()->teacher;

            if (!$teacher) {
                abort(403, 'You are not associated with a teacher profile.');
            }

            $timetable = Timetable::active()->current()->first();

            if (!$timetable) {
                return view('timetables.views.my-timetable', [
                    'timetable' => null,
                    'schedule' => null
                ])->with('info', 'No active timetable found.');
            }

            $schedule = $this->timetableRepository->getTeacherSchedule($timetable->id, $teacher->id);

            return view('timetables.views.my-timetable', compact('timetable', 'schedule'));
        } catch (\Exception $e) {
            Log::error('Error loading my timetable', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load your timetable. Please try again.');
        }
    }

    /**
     * Display conflicts in the timetable.
     */
    public function conflicts(Timetable $timetable)
    {
        try {
            $this->authorize('view', $timetable);

            $conflicts = $this->timetableRepository->detectConflicts($timetable->id);

            return view('timetables.conflicts', compact('timetable', 'conflicts'));
        } catch (\Exception $e) {
            Log::error('Error detecting conflicts', [
                'timetable_id' => $timetable->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to detect conflicts. Please try again.');
        }
    }
}
