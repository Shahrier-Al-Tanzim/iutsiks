<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display the home page with cached data
     */
    public function index()
    {
        // Get cached data for better performance
        $upcomingEvents = $this->cacheService->getUpcomingEvents(6);
        $recentBlogs = $this->cacheService->getRecentBlogs(3);
        $activeFests = $this->cacheService->getActiveFests();
        $prayerTimes = $this->cacheService->getTodaysPrayerTimes();
        $statistics = $this->cacheService->getEventStatistics();

        return view('welcome', compact(
            'upcomingEvents',
            'recentBlogs', 
            'activeFests',
            'prayerTimes',
            'statistics'
        ));
    }
}