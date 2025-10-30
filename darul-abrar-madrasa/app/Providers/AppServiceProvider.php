<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Helpers\PermissionHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/PermissionHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('permission', function ($permission) {
            return PermissionHelper::can($permission);
        });

        Blade::if('anypermission', function ($permissions) {
            return PermissionHelper::canAny($permissions);
        });

        Blade::if('role', function ($role) {
            return PermissionHelper::hasRole($role);
        });

        Blade::if('anyrole', function ($roles) {
            return PermissionHelper::hasAnyRole($roles);
        });
    }
}
