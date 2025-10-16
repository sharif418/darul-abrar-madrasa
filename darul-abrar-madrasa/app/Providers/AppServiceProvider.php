<?php

namespace App\Providers;

use App\Repositories\StudentRepository;
use App\Repositories\TeacherRepository;
use App\Repositories\FeeRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\ExamRepository;
use App\Repositories\ResultRepository;
use App\Services\FileUploadService;
use App\Services\ActivityLogService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

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
                $app->make(\App\Services\FileUploadService::class),
                $app->make(\App\Services\GuardianService::class)
            );
        });

        $this->app->singleton(TeacherRepository::class, function ($app) {
            return new TeacherRepository(
                $app->make(\App\Models\Teacher::class),
                $app->make(\App\Services\FileUploadService::class)
            );
        });

        $this->app->singleton(FeeRepository::class, function ($app) {
            return new FeeRepository(
                $app->make(\App\Models\Fee::class),
                $app->make(\App\Services\ActivityLogService::class)
            );
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
        $appUrl = config('app.url');

        // Force HTTPS scheme if the app URL is https
        if ($appUrl && str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            // In HTTPS environments, secure cookies are appropriate
            Config::set('session.secure', true);
        }

        // Ensure cookies function locally over HTTP (e.g., 127.0.0.1:8080)
        if ($appUrl && str_starts_with($appUrl, 'http://')) {
            Config::set('session.secure', false);
            // Lax same-site supports typical CSRF-protected POST flows
            Config::set('session.same_site', 'lax');
        }

        // Normalize session cookie domain: let Laravel default to the current host.
        // Explicit, incorrect domains (e.g., 127.0.0.1) break session persistence under real hostnames.
        Config::set('session.domain', null);
    }
}
