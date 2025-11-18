<?php

namespace App\Observers;

use App\Services\CacheService;

class CacheInvalidationObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle model created event
     */
    public function created($model): void
    {
        $this->invalidateRelevantCache($model);
    }

    /**
     * Handle model updated event
     */
    public function updated($model): void
    {
        $this->invalidateRelevantCache($model);
    }

    /**
     * Handle model deleted event
     */
    public function deleted($model): void
    {
        $this->invalidateRelevantCache($model);
    }

    /**
     * Invalidate relevant cache based on model type
     */
    protected function invalidateRelevantCache($model): void
    {
        $modelClass = get_class($model);

        switch ($modelClass) {
            case \App\Models\Event::class:
                $this->cacheService->clearEventsCache();
                break;

            case \App\Models\Fest::class:
                $this->cacheService->clearEventsCache(); // Fests affect events display
                break;

            case \App\Models\Blog::class:
                $this->cacheService->clearBlogsCache();
                break;

            case \App\Models\Registration::class:
                $this->cacheService->clearRegistrationCache();
                $this->cacheService->clearEventsCache(); // Registration affects event capacity
                break;

            case \App\Models\PrayerTime::class:
                $this->cacheService->clearPrayerTimesCache($model->date ?? null);
                break;

            case \App\Models\GalleryImage::class:
                $this->cacheService->clearGalleryCache();
                break;
        }
    }
}