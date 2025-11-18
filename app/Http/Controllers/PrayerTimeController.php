<?php

namespace App\Http\Controllers;

use App\Models\PrayerTime;
use App\Services\PrayerTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PrayerTimeController extends Controller
{
    protected $prayerTimeService;

    public function __construct(PrayerTimeService $prayerTimeService)
    {
        $this->prayerTimeService = $prayerTimeService;
    }

    /**
     * Display today's prayer times (public page)
     */
    public function index()
    {
        $cacheService = app(\App\Services\CacheService::class);
        $todaysPrayerTimes = $cacheService->getTodaysPrayerTimes();
        $currentPrayer = $todaysPrayerTimes ? $this->prayerTimeService->getCurrentPrayer() : null;
        $nextPrayer = $todaysPrayerTimes ? $this->prayerTimeService->getUpcomingPrayer() : null;
        $formattedTimes = $this->prayerTimeService->getFormattedPrayerTimes($todaysPrayerTimes);

        return view('prayer-times.index', compact(
            'todaysPrayerTimes',
            'currentPrayer',
            'nextPrayer',
            'formattedTimes'
        ));
    }

    /**
     * Show prayer times for a specific date
     */
    public function show($date)
    {
        try {
            $requestedDate = Carbon::parse($date);
        } catch (\Exception $e) {
            abort(404, 'Invalid date format');
        }

        $prayerTimes = $this->prayerTimeService->getPrayerTimesForDate($requestedDate);
        $formattedTimes = $this->prayerTimeService->getFormattedPrayerTimes($prayerTimes);

        return view('prayer-times.show', compact('prayerTimes', 'formattedTimes', 'requestedDate'));
    }

    /**
     * Display admin prayer times management page
     */
    public function admin()
    {
        Gate::authorize('manage-prayer-times');

        $todaysPrayerTimes = $this->prayerTimeService->getTodaysPrayerTimes();
        $recentPrayerTimes = PrayerTime::with('updatedBy')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        return view('admin.prayer-times.index', compact('todaysPrayerTimes', 'recentPrayerTimes'));
    }

    /**
     * Show form to edit prayer times for a specific date
     */
    public function edit($date = null)
    {
        Gate::authorize('manage-prayer-times');

        $date = $date ? Carbon::parse($date) : Carbon::today();
        $prayerTimes = $this->prayerTimeService->getPrayerTimesForDate($date);

        return view('admin.prayer-times.edit', compact('prayerTimes', 'date'));
    }

    /**
     * Update prayer times for a specific date
     */
    public function update(Request $request)
    {
        Gate::authorize('manage-prayer-times');

        $validated = $request->validate([
            'date' => 'required|date',
            'fajr' => 'required|date_format:H:i',
            'dhuhr' => 'required|date_format:H:i|after:fajr',
            'asr' => 'required|date_format:H:i|after:dhuhr',
            'maghrib' => 'required|date_format:H:i|after:asr',
            'isha' => 'required|date_format:H:i|after:maghrib',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $prayerTime = $this->prayerTimeService->updatePrayerTimes($validated, Auth::user());

            return redirect()
                ->route('admin.prayer-times.index')
                ->with('success', 'Prayer times updated successfully for ' . Carbon::parse($validated['date'])->format('F j, Y'));
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'times' => [$e->getMessage()]
            ]);
        }
    }

    /**
     * Show bulk update form
     */
    public function bulkEdit()
    {
        Gate::authorize('manage-prayer-times');

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(6); // Next 7 days
        $existingTimes = $this->prayerTimeService->getPrayerTimesForRange($startDate, $endDate);

        return view('admin.prayer-times.bulk-edit', compact('startDate', 'endDate', 'existingTimes'));
    }

    /**
     * Process bulk update
     */
    public function bulkUpdate(Request $request)
    {
        Gate::authorize('manage-prayer-times');

        $request->validate([
            'prayer_times' => 'required|array|min:1',
            'prayer_times.*.date' => 'required|date',
            'prayer_times.*.fajr' => 'required|date_format:H:i',
            'prayer_times.*.dhuhr' => 'required|date_format:H:i',
            'prayer_times.*.asr' => 'required|date_format:H:i',
            'prayer_times.*.maghrib' => 'required|date_format:H:i',
            'prayer_times.*.isha' => 'required|date_format:H:i',
            'prayer_times.*.location' => 'nullable|string|max:255',
            'prayer_times.*.notes' => 'nullable|string|max:1000',
        ]);

        // Validate each day's prayer times sequence
        foreach ($request->prayer_times as $index => $times) {
            $this->validatePrayerTimeSequence($times, $index);
        }

        try {
            $updatedTimes = $this->prayerTimeService->bulkUpdatePrayerTimes(
                $request->prayer_times,
                Auth::user()
            );

            return redirect()
                ->route('admin.prayer-times.index')
                ->with('success', 'Successfully updated prayer times for ' . $updatedTimes->count() . ' days');
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['bulk_update' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete prayer times for a specific date
     */
    public function destroy($date)
    {
        Gate::authorize('manage-prayer-times');

        try {
            $requestedDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return back()->withErrors(['date' => 'Invalid date format']);
        }

        $deleted = $this->prayerTimeService->deletePrayerTimes($requestedDate);

        if ($deleted) {
            return redirect()
                ->route('admin.prayer-times.index')
                ->with('success', 'Prayer times deleted for ' . $requestedDate->format('F j, Y'));
        }

        return back()->withErrors(['delete' => 'No prayer times found for the specified date']);
    }

    /**
     * Get prayer times history with pagination
     */
    public function history()
    {
        Gate::authorize('manage-prayer-times');

        $prayerTimes = $this->prayerTimeService->getAllPrayerTimes(20);

        return view('admin.prayer-times.history', compact('prayerTimes'));
    }

    /**
     * API endpoint for prayer times widget
     */
    public function widget()
    {
        $todaysPrayerTimes = $this->prayerTimeService->getTodaysPrayerTimes();
        $currentPrayer = $todaysPrayerTimes ? $this->prayerTimeService->getCurrentPrayer() : null;
        $nextPrayer = $todaysPrayerTimes ? $this->prayerTimeService->getUpcomingPrayer() : null;

        return response()->json([
            'prayer_times' => $todaysPrayerTimes ? $this->prayerTimeService->getFormattedPrayerTimes($todaysPrayerTimes) : null,
            'current_prayer' => $currentPrayer,
            'next_prayer' => $nextPrayer,
        ]);
    }

    /**
     * Validate prayer time sequence for bulk update
     */
    private function validatePrayerTimeSequence(array $times, int $index)
    {
        $timeOrder = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];

        for ($i = 0; $i < count($timeOrder) - 1; $i++) {
            $current = Carbon::createFromFormat('H:i', $times[$timeOrder[$i]]);
            $next = Carbon::createFromFormat('H:i', $times[$timeOrder[$i + 1]]);

            if ($next->lte($current)) {
                throw ValidationException::withMessages([
                    "prayer_times.{$index}.{$timeOrder[$i + 1]}" => [
                        "Prayer time {$timeOrder[$i + 1]} must be after {$timeOrder[$i]} for " . Carbon::parse($times['date'])->format('F j, Y')
                    ]
                ]);
            }
        }
    }
}