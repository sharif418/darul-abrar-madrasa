<?php

namespace App\Http\Controllers;

use App\Models\LessonPlan;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LessonPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LessonPlan::with(['teacher', 'class', 'subject']);
        
        // If teacher, only show their lesson plans
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user', ['user_id' => $user->id, 'email' => $user->email]);
                return redirect()->route('dashboard')->with('error', 'Your teacher profile is incomplete. Please contact the administrator to complete your profile setup.');
            }
            $teacherId = $user->teacher->id;
            $query->where('teacher_id', $teacherId);
        }
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('subject_id') && $request->subject_id != '') {
            $query->where('subject_id', $request->subject_id);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('plan_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('plan_date', '<=', $request->date_to);
        }
        
        $lessonPlans = $query->orderBy('plan_date', 'desc')->paginate(10);
        
        // Get data for filters
        $classes = ClassRoom::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        
        return view('academic.lesson_plans.index', compact('lessonPlans', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user', ['user_id' => $user->id, 'email' => $user->email]);
                return redirect()->route('dashboard')->with('error', 'Your teacher profile is incomplete. Please contact the administrator to complete your profile setup.');
            }
            $teacher = $user->teacher;
            $teacherId = $teacher->id;
            
            // Get classes and subjects assigned to this teacher
            $subjects = Subject::where('teacher_id', $teacherId)->with('class')->get();
            $classes = $subjects->pluck('class')->unique();
            
            return view('academic.lesson_plans.create', compact('teacher', 'classes', 'subjects'));
        } else {
            // For admin, show all teachers, classes and subjects
            $teachers = Teacher::with('user')->get();
            $classes = ClassRoom::orderBy('name')->get();
            $subjects = Subject::orderBy('name')->get();
            
            return view('academic.lesson_plans.create', compact('teachers', 'classes', 'subjects'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'plan_date' => 'required|date',
            'status' => 'required|in:pending,completed',
            'completion_notes' => 'nullable|string',
        ];
        
        // If teacher, use their ID
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user attempting to create lesson plan', ['user_id' => $user->id, 'email' => $user->email]);
                return redirect()->route('lesson-plans.index')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            $request->merge(['teacher_id' => $user->teacher->id]);
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request, $user) {
                    $subject = Subject::find($value);
                    if ($subject && $user->teacher && $subject->teacher_id != $user->teacher->id) {
                        $fail('You can only create lesson plans for subjects assigned to you.');
                    }
                }
            ];
        } else {
            // For admin, validate teacher_id
            $validationRules['teacher_id'] = 'required|exists:teachers,id';
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request) {
                    $subject = Subject::find($value);
                    if ($subject && $subject->teacher_id != $request->teacher_id) {
                        $fail('The selected subject is not assigned to the selected teacher.');
                    }
                }
            ];
        }
        
        $request->validate($validationRules);
        
        LessonPlan::create($request->all());
        
        return redirect()->route('lesson-plans.index')
            ->with('success', 'Lesson plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to view this lesson plan
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user attempting to view lesson plan', ['user_id' => $user->id, 'lesson_plan_id' => $lessonPlan->id]);
                return redirect()->route('lesson-plans.index')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            if ($user->teacher->id != $lessonPlan->teacher_id) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        return view('academic.lesson_plans.show', compact('lessonPlan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to edit this lesson plan
        if ($user->isTeacher() && optional($user->teacher)->id != $lessonPlan->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user attempting to edit lesson plan', ['user_id' => $user->id, 'lesson_plan_id' => $lessonPlan->id]);
                return redirect()->route('lesson-plans.index')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            $teacher = $user->teacher;
            $teacherId = $teacher->id;
            
            // Get classes and subjects assigned to this teacher
            $subjects = Subject::where('teacher_id', $teacherId)->with('class')->get();
            $classes = $subjects->pluck('class')->unique();
            
            return view('academic.lesson_plans.edit', compact('lessonPlan', 'teacher', 'classes', 'subjects'));
        } else {
            // For admin, show all teachers, classes and subjects
            $teachers = Teacher::with('user')->get();
            $classes = ClassRoom::orderBy('name')->get();
            $subjects = Subject::orderBy('name')->get();
            
            return view('academic.lesson_plans.edit', compact('lessonPlan', 'teachers', 'classes', 'subjects'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to update this lesson plan
        if ($user->isTeacher() && optional($user->teacher)->id != $lessonPlan->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'plan_date' => 'required|date',
            'status' => 'required|in:pending,completed',
            'completion_notes' => 'nullable|string',
        ];
        
        // If teacher, use their ID
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user attempting to update lesson plan', ['user_id' => $user->id, 'lesson_plan_id' => $lessonPlan->id]);
                return redirect()->route('lesson-plans.index')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            $request->merge(['teacher_id' => $user->teacher->id]);
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request, $user) {
                    $subject = Subject::find($value);
                    if ($subject && $user->teacher && $subject->teacher_id != $user->teacher->id) {
                        $fail('You can only create lesson plans for subjects assigned to you.');
                    }
                }
            ];
        } else {
            // For admin, validate teacher_id
            $validationRules['teacher_id'] = 'required|exists:teachers,id';
            $validationRules['class_id'] = 'required|exists:classes,id';
            $validationRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($request) {
                    $subject = Subject::find($value);
                    if ($subject && $subject->teacher_id != $request->teacher_id) {
                        $fail('The selected subject is not assigned to the selected teacher.');
                    }
                }
            ];
        }
        
        $request->validate($validationRules);
        
        $lessonPlan->update($request->all());
        
        return redirect()->route('lesson-plans.index')
            ->with('success', 'Lesson plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to delete this lesson plan
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user attempting to delete lesson plan', ['user_id' => $user->id, 'lesson_plan_id' => $lessonPlan->id]);
                return redirect()->route('lesson-plans.index')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            if ($user->teacher->id != $lessonPlan->teacher_id) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        $lessonPlan->delete();
        
        return redirect()->route('lesson-plans.index')
            ->with('success', 'Lesson plan deleted successfully.');
    }
    
    /**
     * Mark lesson plan as completed.
     */
    public function markCompleted(Request $request, LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        
        // Check if the user is authorized to update this lesson plan
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user attempting to mark lesson plan completed', ['user_id' => $user->id, 'lesson_plan_id' => $lessonPlan->id]);
                return redirect()->route('lesson-plans.index')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            if ($user->teacher->id != $lessonPlan->teacher_id) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        $request->validate([
            'completion_notes' => 'nullable|string',
        ]);
        
        $lessonPlan->update([
            'status' => 'completed',
            'completion_notes' => $request->completion_notes,
        ]);
        
        return redirect()->route('lesson-plans.show', $lessonPlan->id)
            ->with('success', 'Lesson plan marked as completed.');
    }
    
    /**
     * Get subjects for a specific class and teacher.
     */
    public function getSubjects(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);
        
        $subjects = Subject::where('class_id', $request->class_id)
            ->where('teacher_id', $request->teacher_id)
            ->get();
            
        return response()->json($subjects);
    }
    
    /**
     * Calendar view of lesson plans.
     */
    public function calendar()
    {
        $user = Auth::user();
        
        if ($user->isTeacher()) {
            if (!$user->teacher) {
                Log::error('Teacher record missing for user accessing calendar', ['user_id' => $user->id]);
                return redirect()->route('dashboard')->with('error', 'Your teacher profile is incomplete. Please contact the administrator.');
            }
            $teacherId = $user->teacher->id;
            $lessonPlans = LessonPlan::where('teacher_id', $teacherId)->get();
        } else {
            $lessonPlans = LessonPlan::all();
        }
        
        // Format lesson plans for calendar
        $events = [];
        foreach ($lessonPlans as $plan) {
            $color = $plan->status === 'completed' ? '#10B981' : '#3B82F6';
            
            $events[] = [
                'id' => $plan->id,
                'title' => $plan->title,
                'start' => $plan->plan_date->format('Y-m-d'),
                'url' => route('lesson-plans.show', $plan->id),
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        }
        
        return view('academic.lesson_plans.calendar', compact('events'));
    }
}