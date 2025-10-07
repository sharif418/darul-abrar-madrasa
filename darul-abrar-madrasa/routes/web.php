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
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

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
    Route::middleware(['auth', 'role:admin'])->group(function () {
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
        
        // Subject management
        Route::resource('subjects', SubjectController::class);

        // Grading Scale management
        Route::resource('grading-scales', GradingScaleController::class);
Route::patch('/grading-scales/{gradingScale}/toggle-active', [GradingScaleController::class, 'toggleActive'])->name('grading-scales.toggle-active');

        // Lesson Plan management
        Route::resource('lesson-plans', LessonPlanController::class);
        
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
    });
    
    // Teacher routes
    Route::middleware(['auth', 'role:teacher'])->group(function () {
        // Attendance management
        Route::get('/attendances/create/{class_id}', [AttendanceController::class, 'createByClass'])->name('attendances.create.class');
        Route::post('/attendances/store-bulk', [AttendanceController::class, 'storeBulk'])->name('attendances.store.bulk');
        
        // Marks Entry moved to admin+teacher group
    });
    
    // Common routes for teachers and admin
    Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
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
    });
    
    // Student routes
    Route::middleware(['auth', 'role:student'])->group(function () {
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
    
    // Study materials download - accessible to all authenticated users
    Route::get('/study-materials/{studyMaterial}/download', [StudyMaterialController::class, 'download'])->name('study-materials.download');

    // Common routes for all authenticated users
    Route::get('/notices/public', [NoticeController::class, 'publicNotices'])->name('notices.public');
});
