<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Fest;
use App\Models\Blog;
use App\Models\PrayerTime;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CacheService
{
    const CACHE_TTL_SHORT = 300; // 5 minutes
    const CACHE_TTL_MEDIUM = 1800; // 30 minutes
    const CACHE_TTL_LONG = 3600; // 1 hour
    const CACHE_TTL_DAILY = 86400; // 24 hours

    /**
     * Get cached prayer times for today
     */
    public function getTodaysPrayerTimes(): ?PrayerTime
    {
        $cacheKey = 'prayer_times_' . Carbon::today()->format('Y-m-d');
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () {
            return PrayerTime::today()->first();
        });
    }

    /**
     * Get cached upcoming events
     */
    public function getUpcomingEvents(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "upcoming_events_{$limit}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL_SHORT, function () use ($limit) {
            return Event::with([
                'author:id,name',
                'fest:id,title',
                'approvedRegistrations:id,event_id'
            ])
            ->select('id', 'fest_id', 'title', 'description', 'event_date', 'event_time', 'type', 'registration_type', 'location', 'max_participants', 'fee_amount', 'status', 'author_id', 'image')
            ->published()
            ->upcoming()
            ->orderBy('event_date', 'asc')
            ->limit($limit)
            ->get();
        });
    }

    /**
     * Get cached recent blogs
     */
    public function getRecentBlogs(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "recent_blogs_{$limit}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($limit) {
            return Blog::with('author:id,name')
                ->select('id', 'title', 'content', 'image', 'author_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get cached active fests
     */
    public function getActiveFests(): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'active_fests';
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () {
            return Fest::with([
                'events:id,fest_id,title,event_date,status',
                'creator:id,name'
            ])
            ->select('id', 'title', 'description', 'start_date', 'end_date', 'banner_image', 'status', 'created_by')
            ->published()
            ->where(function ($query) {
                $query->active()->orWhere->upcoming();
            })
            ->orderBy('start_date', 'asc')
            ->get();
        });
    }

    /**
     * Get cached event statistics
     */
    public function getEventStatistics(): array
    {
        $cacheKey = 'event_statistics';
        
        return Cache::remember($cacheKey, self::CACHE_TTL_LONG, function () {
            return [
                'total_events' => Event::count(),
                'published_events' => Event::published()->count(),
                'upcoming_events' => Event::published()->upcoming()->count(),
                'events_with_registration' => Event::published()
                    ->where('registration_type', '!=', 'on_spot')
                    ->count(),
                'events_today' => Event::published()
                    ->whereDate('event_date', Carbon::today())
                    ->count(),
            ];
        });
    }

    /**
     * Get cached registration statistics
     */
    public function getRegistrationStatistics(): array
    {
        $cacheKey = 'registration_statistics';
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () {
            return [
                'total_registrations' => \App\Models\Registration::count(),
                'pending_registrations' => \App\Models\Registration::pending()->count(),
                'approved_registrations' => \App\Models\Registration::approved()->count(),
                'rejected_registrations' => \App\Models\Registration::rejected()->count(),
                'pending_payments' => \App\Models\Registration::where('payment_required', true)
                    ->where('payment_status', 'pending')->count(),
                'today_registrations' => \App\Models\Registration::whereDate('registered_at', Carbon::today())->count(),
            ];
        });
    }

    /**
     * Get cached gallery statistics
     */
    public function getGalleryStatistics(): array
    {
        $cacheKey = 'gallery_statistics';
        
        return Cache::remember($cacheKey, self::CACHE_TTL_LONG, function () {
            return [
                'total_images' => \App\Models\GalleryImage::count(),
                'event_images' => \App\Models\GalleryImage::forEvents()->count(),
                'fest_images' => \App\Models\GalleryImage::forFests()->count(),
                'general_images' => \App\Models\GalleryImage::generalGallery()->count(),
                'recent_uploads' => \App\Models\GalleryImage::where('created_at', '>=', Carbon::today()->subDays(7))->count(),
            ];
        });
    }

    /**
     * Clear prayer times cache
     */
    public function clearPrayerTimesCache(?Carbon $date = null): void
    {
        $date = $date ?? Carbon::today();
        $cacheKey = 'prayer_times_' . $date->format('Y-m-d');
        Cache::forget($cacheKey);
    }

    /**
     * Clear events cache
     */
    public function clearEventsCache(): void
    {
        $patterns = [
            'upcoming_events_*',
            'event_statistics',
            'active_fests'
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // For wildcard patterns, we need to clear specific keys
                for ($i = 1; $i <= 20; $i++) {
                    Cache::forget(str_replace('*', $i, $pattern));
                }
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Clear blogs cache
     */
    public function clearBlogsCache(): void
    {
        $patterns = [
            'recent_blogs_*'
        ];

        foreach ($patterns as $pattern) {
            for ($i = 1; $i <= 20; $i++) {
                Cache::forget(str_replace('*', $i, $pattern));
            }
        }
    }

    /**
     * Clear registration cache
     */
    public function clearRegistrationCache(): void
    {
        Cache::forget('registration_statistics');
    }

    /**
     * Clear gallery cache
     */
    public function clearGalleryCache(): void
    {
        Cache::forget('gallery_statistics');
    }

    /**
     * Clear all application caches
     */
    public function clearAllCache(): void
    {
        $this->clearEventsCache();
        $this->clearBlogsCache();
        $this->clearRegistrationCache();
        $this->clearGalleryCache();
        $this->clearPrayerTimesCache();
    }

    /**
     * Warm up critical caches
     */
    public function warmUpCache(): void
    {
        // Warm up frequently accessed data
        $this->getTodaysPrayerTimes();
        $this->getUpcomingEvents();
        $this->getRecentBlogs();
        $this->getActiveFests();
        $this->getEventStatistics();
        $this->getRegistrationStatistics();
        $this->getGalleryStatistics();
    }
}