<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PeriodController extends Controller
{
    /**
     * Display a listing of the periods.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Period::class);

            $query = Period::query();

            // Apply day filter
            if ($request->filled('day_of_week')) {
                $query->forDay($request->day_of_week);
            }

            // Apply active filter
            if ($request->filled('is_active') && $request->is_active === '1') {
                $query->active();
            }

            // Apply search filter
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Order by day and order
            $periods = $query->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
                ->orderBy('order')
                ->paginate(20);

            return view('periods.index', compact('periods'));
        } catch (\Exception $e) {
            Log::error('Error fetching periods', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load periods. Please try again.');
        }
    }

    /**
     * Show the form for creating a new period.
     */
    public function create()
    {
        $this->authorize('create', Period::class);

        return view('periods.create');
    }

    /**
     * Store a newly created period in storage.
     */
    public function store(StorePeriodRequest $request)
    {
        try {
            $this->authorize('create', Period::class);

            $period = Period::create($request->validated());

            Log::info('Period created successfully', [
                'period_id' => $period->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('periods.index')->with('success', 'Period created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating period', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to create period. Please try again.');
        }
    }

    /**
     * Display the specified period.
     */
    public function show(Period $period)
    {
        try {
            $this->authorize('view', $period);

            $period->load(['timetableEntries.class', 'timetableEntries.subject', 'timetableEntries.teacher.user']);

            return view('periods.show', compact('period'));
        } catch (\Exception $e) {
            Log::error('Error fetching period details', [
                'period_id' => $period->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load period details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified period.
     */
    public function edit(Period $period)
    {
        $this->authorize('update', $period);

        return view('periods.edit', compact('period'));
    }

    /**
     * Update the specified period in storage.
     */
    public function update(UpdatePeriodRequest $request, Period $period)
    {
        try {
            $this->authorize('update', $period);

            $period->update($request->validated());

            Log::info('Period updated successfully', [
                'period_id' => $period->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('periods.index')->with('success', 'Period updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating period', [
                'period_id' => $period->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to update period. Please try again.');
        }
    }

    /**
     * Remove the specified period from storage.
     */
    public function destroy(Period $period)
    {
        try {
            $this->authorize('delete', $period);

            // Check if period has timetable entries
            if ($period->timetableEntries()->exists()) {
                return redirect()->back()->with('error', 'Cannot delete period with existing timetable entries.');
            }

            $period->delete();

            Log::info('Period deleted successfully', [
                'period_id' => $period->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('periods.index')->with('success', 'Period deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting period', [
                'period_id' => $period->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to delete period. Please try again.');
        }
    }
}
