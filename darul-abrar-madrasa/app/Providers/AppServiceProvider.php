<?php

namespace App\Providers;

use App\Repositories\StudentRepository;
use App\Repositories\TeacherRepository;
use App\Repositories\FeeRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\ExamRepository;
use App\Repositories\ResultRepository;
use App\Services\FileUploadService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories as singletons for dependency injection
        $this->app->singleton(StudentRepository::class, function ($app) {
            return new StudentRepository(
                $app->make(\App\Models\Student::class),
                $app->make(\App\Services\FileUploadService::class)
            );
        });

        $this->app->singleton(TeacherRepository::class, function ($app) {
            return new TeacherRepository(
                $app->make(\App\Models\Teacher::class),
                $app->make(\App\Services\FileUploadService::class)
            );
        });

        $this->app->singleton(FeeRepository::class, function ($app) {
            return new FeeRepository($app->make(\App\Models\Fee::class));
        });

        $this->app->singleton(AttendanceRepository::class, function ($app) {
            return new AttendanceRepository($app->make(\App\Models\Attendance::class));
        });

        $this->app->singleton(ExamRepository::class, function ($app) {
            return new ExamRepository($app->make(\App\Models\Exam::class));
        });

        $this->app->singleton(ResultRepository::class, function ($app) {
            return new ResultRepository($app->make(\App\Models\Result::class));
        });

        // Register services as singletons
        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
