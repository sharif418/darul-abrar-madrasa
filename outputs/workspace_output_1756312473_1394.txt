<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isTeacher()) {
            return $this->teacherDashboard();
        } elseif ($user->isStudent()) {
            return $this->studentDashboard();
        } else {
            return $this->staffDashboard();
        }
    }

    /**
     * Display the admin dashboard.
     */
    private function adminDashboard()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassRoom::count();
        $totalDepartments = Department::count();
        $totalSubjects = Subject::count();
        $totalUsers = User::count();
        
        $recentFees = Fee::with('student.user')
            ->latest()
            ->take(5)
            ->get();
            
        $totalFeesCollected = Fee::where('status', 'paid')
            ->sum('paid_amount');
            
        $pendingFees = Fee::whereIn('status', ['unpaid', 'partial'])
            ->sum('amount');
            
        $upcomingExams = Exam::with('class')
            ->upcoming()
            ->take(5)
            ->get();
            
        $recentNotices = Notice::with('publishedBy')
            ->published()
            ->notExpired()
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalDepartments',
            'totalSubjects',
            'totalUsers',
            'recentFees',
            'totalFeesCollected',
            'pendingFees',
            'upcomingExams',
            'recentNotices'
        ));
    }

    /**
     * Display the teacher dashboard.
     */
    private function teacherDashboard()
    {
        $teacher = Auth::user()->teacher;
        
        $assignedSubjects = Subject::where('teacher_id', $teacher->id)
            ->with('class')
            ->get();
            
        $assignedClasses = ClassRoom::whereHas('subjects', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        $upcomingExams = Exam::whereIn('class_id', $assignedClasses->pluck('id'))
            ->upcoming()
            ->take(5)
            ->get();
            
        $recentNotices = Notice::published()
            ->notExpired()
            ->where(function ($query) {
                $query->where('notice_for', 'teachers')
                    ->orWhere('notice_for', 'all');
            })
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.teacher', compact(
            'teacher',
            'assignedSubjects',
            'assignedClasses',
            'upcomingExams',
            'recentNotices'
        ));
    }

    /**
     * Display the student dashboard.
     */
    private function studentDashboard()
    {
        $student = Auth::user()->student;
        
        $attendanceCount = $student->attendances()
            ->whereMonth('date', now()->month)
            ->count();
            
        $presentCount = $student->attendances()
            ->whereMonth('date', now()->month)
            ->where('status', 'present')
            ->count();
            
        $attendancePercentage = $attendanceCount > 0 
            ? round(($presentCount / $attendanceCount) * 100) 
            : 0;
            
        $upcomingExams = Exam::where('class_id', $student->class_id)
            ->upcoming()
            ->take(5)
            ->get();
            
        $recentResults = $student->results()
            ->with(['exam', 'subject'])
            ->latest()
            ->take(5)
            ->get();
            
        $pendingFees = $student->fees()
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();
            
        $recentNotices = Notice::published()
            ->notExpired()
            ->where(function ($query) {
                $query->where('notice_for', 'students')
                    ->orWhere('notice_for', 'all');
            })
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.student', compact(
            'student',
            'attendanceCount',
            'presentCount',
            'attendancePercentage',
            'upcomingExams',
            'recentResults',
            'pendingFees',
            'recentNotices'
        ));
    }

    /**
     * Display the staff dashboard.
     */
    private function staffDashboard()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassRoom::count();
        
        $recentFees = Fee::with('student.user')
            ->latest()
            ->take(5)
            ->get();
            
        $recentNotices = Notice::published()
            ->notExpired()
            ->where(function ($query) {
                $query->where('notice_for', 'staff')
                    ->orWhere('notice_for', 'all');
            })
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.staff', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'recentFees',
            'recentNotices'
        ));
    }
}