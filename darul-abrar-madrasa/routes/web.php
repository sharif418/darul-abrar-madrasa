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
use Illuminate\Support\Facades\Route;

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
        
        // Teacher management
        Route::resource('teachers', TeacherController::class);
        
        // Student management
        Route::resource('students', StudentController::class);
        
        // Subject management
        Route::resource('subjects', SubjectController::class);
        
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
        
        // Result management
        Route::get('/results/create/{exam_id}/{class_id}/{subject_id}', [ResultController::class, 'createBulk'])->name('results.create.bulk');
        Route::post('/results/store-bulk', [ResultController::class, 'storeBulk'])->name('results.store.bulk');
        
        // Marks Entry
        Route::get('/marks/create', [ResultController::class, 'createMarks'])->name('marks.create');
        Route::post('/marks/store', [ResultController::class, 'storeMarks'])->name('marks.store');
    });
    
    // Common routes for teachers and admin
    Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
        // Attendance management
        Route::resource('attendances', AttendanceController::class);
        
        // Result management
        Route::resource('results', ResultController::class);
    });
    
    // Student routes
    Route::middleware(['auth', 'role:student'])->group(function () {
        // View own attendance
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('my.attendance');
        
        // View own results
        Route::get('/my-results', [ResultController::class, 'myResults'])->name('my.results');
        Route::get('/results/download/{exam}', [ResultController::class, 'downloadResult'])->name('results.download');
        
        // View own fees
        Route::get('/my-fees', [FeeController::class, 'myFees'])->name('my.fees');
        
        // View study materials
        Route::get('/my-materials', [StudyMaterialController::class, 'myMaterials'])->name('my.materials');
    });
    
    // Common routes for all authenticated users
    Route::get('/notices/public', [NoticeController::class, 'publicNotices'])->name('notices.public');
});