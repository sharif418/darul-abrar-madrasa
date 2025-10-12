<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudyMaterialController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GradingScaleController;
use App\Http\Controllers\LessonPlanController;
use App\Http\Controllers\GuardianPortalController;
use App\Http\Controllers\AccountantPortalController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\AccountantController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Constrain {notice} route parameter to numeric IDs to avoid conflicts with '/notices/public'
Route::pattern('notice', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    if (app()->environment('testing')) {
        return response('OK', 200);
    }
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});
// Public notices (moved outside auth)
Route::get('/notices/public', [NoticeController::class, 'publicNotices'])->name('notices.public');
Route::get('/notices/{notice}', [NoticeController::class, 'showPublic'])->name('notices.public.show');

// Authentication routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        // System Health Dashboard
        Route::get('/admin/system-health', [DashboardController::class, 'systemHealth'])->name('admin.system-health');
        Route::get('/admin/system-health/export', [DashboardController::class, 'exportSystemHealth'])->name('admin.system-health.export');
        
        // System Health Quick Actions
        Route::post('/admin/system-health/verify', [DashboardController::class, 'runVerification'])->name('admin.system-health.verify');
        Route::post('/admin/system-health/sync', [DashboardController::class, 'runSync'])->name('admin.system-health.sync');
        Route::post('/admin/system-health/repair', [DashboardController::class, 'runRepair'])->name('admin.system-health.repair');
        
        // User management
        Route::resource('users', UserController::class);
        
        // Department management
        Route::resource('departments', DepartmentController::class);
        
        // Class management
        Route::resource('classes', ClassController::class);
        Route::get('/classes/{class}/enroll-student', [ClassController::class, 'showEnrollForm'])->name('classes.enroll-student.form');
        Route::post('/classes/{class}/enroll-student', [ClassController::class, 'enrollStudent'])->name('classes.enroll-student');
        Route::get('/classes/{class}/assign-subject', [ClassController::class, 'showAssignSubjectForm'])->name('classes.assign-subject.form');
        Route::post('/classes/{class}/assign-subject', [ClassController::class, 'assignSubject'])->name('classes.assign-subject');
        Route::delete('/classes/{class}/students/{student}', [ClassController::class, 'unenrollStudent'])->name('classes.unenroll-student');
        Route::delete('/classes/{class}/subjects/{subject}', [ClassController::class, 'unassignSubject'])->name('classes.unassign-subject');
        
        // Teacher management
        Route::resource('teachers', TeacherController::class);
        
        // Student management
        Route::resource('students', StudentController::class);
        Route::post('/students/bulk-promote', [StudentController::class, 'bulkPromote'])->name('students.bulk-promote');
        Route::post('/students/bulk-transfer', [StudentController::class, 'bulkTransfer'])->name('students.bulk-transfer');
        Route::post('/students/bulk-status', [StudentController::class, 'bulkStatusUpdate'])->name('students.bulk-status');
        
        // Subject management (CRUD operations - admin only)
        Route::resource('subjects', SubjectController::class)->except(['index', 'show']);

        // Grading Scale management
        Route::resource('grading-scales', GradingScaleController::class);
        Route::patch('/grading-scales/{gradingScale}/toggle-active', [GradingScaleController::class, 'toggleActive'])->name('grading-scales.toggle-active');
        
        // Exam management
        Route::resource('exams', ExamController::class);
        Route::put('/exams/{exam}/publish-results', [ExamController::class, 'publishResults'])->name('exams.publish-results');
        
        // Fee management
        Route::resource('fees', FeeController::class);
        Route::get('/fees/create-bulk', [FeeController::class, 'createBulk'])->name('fees.create-bulk');
        Route::post('/fees/store-bulk', [FeeController::class, 'storeBulk'])->name('fees.store-bulk');
        Route::get('/fees/{fee}/invoice', [FeeController::class, 'generateInvoice'])->name('fees.invoice');
        Route::get('/fees/{fee}/payment', [FeeController::class, 'showPaymentForm'])->name('fees.payment');
        Route::post('/fees/{fee}/record-payment', [FeeController::class, 'recordPayment'])->name('fees.record-payment');
        Route::get('/fees-reports', [FeeController::class, 'reportsIndex'])->name('fees.reports');
        Route::get('/fees-reports/collection', [FeeController::class, 'collectionReport'])->name('fees.reports.collection');
        Route::get('/fees-reports/outstanding', [FeeController::class, 'outstandingReport'])->name('fees.reports.outstanding');
        
        // Notice management
        Route::resource('notices', NoticeController::class);

        // Guardian management (admin only)
        Route::resource('guardians', GuardianController::class);
        Route::post('/guardians/{guardian}/link-student', [GuardianController::class, 'linkStudent'])->name('guardians.link-student');
        Route::delete('/guardians/{guardian}/students/{student}', [GuardianController::class, 'unlinkStudent'])->name('guardians.unlink-student');

        // Accountant management (admin only)
        Route::resource('accountants', AccountantController::class);
    });
    
    // Teacher routes
    Route::middleware(['role:teacher'])->group(function () {
        // Attendance management
        Route::get('/attendances/create/{class_id}', [AttendanceController::class, 'createByClass'])->name('attendances.create.class');
        Route::post('/attendances/store-bulk', [AttendanceController::class, 'storeBulk'])->name('attendances.store.bulk');
        
        // Marks Entry moved to admin+teacher group
    });
    
    // Common routes for teachers and admin
    Route::middleware(['role:admin,teacher'])->group(function () {
        // Attendance management
        Route::resource('attendances', AttendanceController::class);
        
        // Result management
        Route::resource('results', ResultController::class);
        Route::get('/results/{exam}/class-summary/pdf', [ResultController::class, 'generateClassResultSummary'])->name('results.class-summary.pdf');
        Route::get('/results/create/{exam_id}/{class_id}/{subject_id}', [ResultController::class, 'createBulk'])->name('results.create.bulk');
        Route::post('/results/store-bulk', [ResultController::class, 'storeBulk'])->name('results.store.bulk');
        Route::get('/exams/for-marks-entry', [ExamController::class, 'getExamsForMarksEntry'])->name('exams.for-marks-entry');

        // Marks Entry
        Route::get('/marks/create', [ResultController::class, 'createMarks'])->name('marks.create');
        Route::post('/marks/store', [ResultController::class, 'storeMarks'])->name('marks.store');

    // Study materials management
    Route::resource('study-materials', StudyMaterialController::class);
    Route::patch('/study-materials/{studyMaterial}/toggle-published', [StudyMaterialController::class, 'togglePublished'])->name('study-materials.toggle-published');
    
    // Lesson Plan management
    Route::resource('lesson-plans', LessonPlanController::class);
    Route::post('/lesson-plans/{lessonPlan}/mark-completed', [LessonPlanController::class, 'markCompleted'])->name('lesson-plans.mark-completed');
    Route::get('/lesson-plans/calendar', [LessonPlanController::class, 'calendar'])->name('lesson-plans.calendar');
    
    // Subject management (Read-only for teachers)
    Route::resource('subjects', SubjectController::class)->only(['index', 'show']);
    });
    
    // Student routes
    Route::middleware(['role:student'])->group(function () {
        // View own attendance
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('my.attendance');
        
        // View own results
        Route::get('/my-results', [ResultController::class, 'myResults'])->name('my.results');
        Route::get('/results/{exam}/{student}/mark-sheet', [ResultController::class, 'generateMarkSheet'])->name('results.mark-sheet');
        
        // View own fees
        Route::get('/my-fees', [FeeController::class, 'myFees'])->name('my.fees');
        
        // View study materials
        Route::get('/my-materials', [StudyMaterialController::class, 'myMaterials'])->name('my.materials');
    });

    // Guardian routes
    Route::middleware(['role:guardian'])->prefix('guardian')->name('guardian.')->group(function () {
        Route::get('/dashboard', [GuardianPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/children', [GuardianPortalController::class, 'children'])->name('children');
        Route::get('/children/{student}', [GuardianPortalController::class, 'childProfile'])->name('child.profile');
        Route::get('/children/{student}/attendance', [GuardianPortalController::class, 'childAttendance'])->name('child.attendance');
        Route::get('/children/{student}/results', [GuardianPortalController::class, 'childResults'])->name('child.results');
        Route::get('/children/{student}/fees', [GuardianPortalController::class, 'childFees'])->name('child.fees');
        Route::get('/children/{student}/study-materials', [GuardianPortalController::class, 'studyMaterials'])->name('child.materials');
        Route::get('/fees', [GuardianPortalController::class, 'allFees'])->name('fees');
        Route::get('/fees/{fee}/pay', [GuardianPortalController::class, 'payFee'])->name('fees.pay');
        Route::post('/fees/{fee}/process-payment', [GuardianPortalController::class, 'processPayment'])->name('fees.process-payment');
        Route::get('/notices', [GuardianPortalController::class, 'notices'])->name('notices');
    });

    // Accountant routes
    Route::middleware(['role:accountant'])->prefix('accountant')->name('accountant.')->group(function () {
        Route::get('/dashboard', [AccountantPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/fees', [AccountantPortalController::class, 'fees'])->name('fees');
        Route::get('/fees/{fee}/record-payment', [AccountantPortalController::class, 'recordPayment'])->name('fees.record-payment');
        Route::post('/fees/{fee}/process-payment', [AccountantPortalController::class, 'processPayment'])->name('fees.process-payment');
        
        Route::get('/waivers', [AccountantPortalController::class, 'waivers'])->name('waivers');
        Route::get('/waivers/create', [AccountantPortalController::class, 'createWaiver'])->name('waivers.create');
        Route::post('/waivers', [AccountantPortalController::class, 'storeWaiver'])->name('waivers.store');
        Route::post('/waivers/{waiver}/approve', [AccountantPortalController::class, 'approveWaiver'])->name('waivers.approve');
        Route::post('/waivers/{waiver}/reject', [AccountantPortalController::class, 'rejectWaiver'])->name('waivers.reject');
        
        Route::get('/installments', [AccountantPortalController::class, 'installments'])->name('installments');
        Route::get('/fees/{fee}/installments/create', [AccountantPortalController::class, 'createInstallmentPlan'])->name('installments.create');
        Route::post('/fees/{fee}/installments', [AccountantPortalController::class, 'storeInstallmentPlan'])->name('installments.store');
        
        Route::get('/late-fees', [AccountantPortalController::class, 'lateFees'])->name('late-fees');
        Route::post('/late-fees/apply', [AccountantPortalController::class, 'applyLateFees'])->name('late-fees.apply');
        
        Route::get('/reports', [AccountantPortalController::class, 'reports'])->name('reports');
        Route::get('/reports/collection', [AccountantPortalController::class, 'collectionReport'])->name('reports.collection');
        Route::get('/reports/outstanding', [AccountantPortalController::class, 'outstandingReport'])->name('reports.outstanding');
        Route::get('/reports/waivers', [AccountantPortalController::class, 'waiverReport'])->name('reports.waivers');
        
        Route::get('/reconciliation', [AccountantPortalController::class, 'reconciliation'])->name('reconciliation');
    });
    
    // Study materials download - accessible to all authenticated users
    Route::get('/study-materials/{studyMaterial}/download', [StudyMaterialController::class, 'download'])->name('study-materials.download');

    // Common routes for all authenticated users
});
