<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define gates for authorization
        Gate::define('manage-prayer-times', function (User $user) {
            return $user->canManageContent();
        });

        Gate::define('manage-events', function (User $user) {
            return $user->canManageEvents();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-content', function (User $user) {
            return $user->canManageContent();
        });

        // Register cache invalidation observer for performance optimization
        $cacheObserver = new \App\Observers\CacheInvalidationObserver(
            app(\App\Services\CacheService::class)
        );

        \App\Models\Event::observe($cacheObserver);
        \App\Models\Fest::observe($cacheObserver);
        \App\Models\Blog::observe($cacheObserver);
        \App\Models\Registration::observe($cacheObserver);
        \App\Models\PrayerTime::observe($cacheObserver);
        \App\Models\GalleryImage::observe($cacheObserver);
    }
}
