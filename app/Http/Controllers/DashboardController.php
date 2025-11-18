<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\PrayerTimeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Fetch user-specific data
        $upcomingEvents = Event::with(['fest', 'author'])
            ->where('status', 'published')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(3)
            ->get();
        
        $userRegistrations = $user->registrations()
            ->with('event')
            ->latest()
            ->limit(3)
            ->get();
        
        $prayerTimeService = new PrayerTimeService();
        $todaysPrayerTimes = $prayerTimeService->getTodaysPrayerTimes();

        return view('dashboard', compact('user', 'upcomingEvents', 'userRegistrations', 'todaysPrayerTimes'));
    }
}