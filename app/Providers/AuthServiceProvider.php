<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin Gates
        Gate::define('access-admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-events', function ($user) {
            return $user->canManageEvents();
        });

        Gate::define('manage-content', function ($user) {
            return $user->canManageContent();
        });

        Gate::define('manage-prayer-times', function ($user) {
            return $user->canManagePrayerTimes();
        });

        Gate::define('manage-gallery', function ($user) {
            return $user->canManageGallery();
        });

        Gate::define('manage-registrations', function ($user) {
            return $user->canManageRegistrations();
        });

        Gate::define('view-analytics', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('view-activity-logs', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-system-settings', function ($user) {
            return $user->isSuperAdmin();
        });
    }
}
