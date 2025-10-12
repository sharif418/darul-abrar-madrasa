<?php

namespace App\Providers;

use App\Models\StudyMaterial;
use App\Policies\StudyMaterialPolicy;
use App\Models\Fee;
use App\Policies\FeePolicy;
use App\Models\FeeWaiver;
use App\Policies\FeeWaiverPolicy;
use App\Models\Guardian;
use App\Policies\GuardianPolicy;
use App\Models\Accountant;
use App\Policies\AccountantPolicy;
use App\Models\Student;
use App\Policies\StudentPolicy;
use App\Models\Result;
use App\Policies\ResultPolicy;
use App\Models\Attendance;
use App\Policies\AttendancePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        StudyMaterial::class => StudyMaterialPolicy::class,
        Fee::class => FeePolicy::class,
        FeeWaiver::class => FeeWaiverPolicy::class,
        Guardian::class => GuardianPolicy::class,
        Accountant::class => AccountantPolicy::class,
        Student::class => StudentPolicy::class,
        Result::class => ResultPolicy::class,
        Attendance::class => AttendancePolicy::class,
        // Additional policies can be registered here as they are implemented.
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        // You can define custom gates here if needed.
    }
}
