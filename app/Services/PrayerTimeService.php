<?php

namespace App\Services;

use App\Models\PrayerTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PrayerTimeService
{
    /**
     * Get today's prayer times
     */
    public function getTodaysPrayerTimes(): ?PrayerTime
    {
        return PrayerTime::where('date', today())->first();
    }
    
    /**
     * Update prayer times for a specific date
     */
    public function updatePrayerTimes(array $times, User $admin): PrayerTime
    {
        $this->validatePrayerTimes($times);
        
        $prayerTime = PrayerTime::updateOrCreate(
            ['date' => $times['date']],
            [
                'fajr' => $times['fajr'] . (strlen($times['fajr']) === 5 ? ':00' : ''),
                'dhuhr' => $times['dhuhr'] . (strlen($times['dhuhr']) === 5 ? ':00' : ''),
                'asr' => $times['asr'] . (strlen($times['asr']) === 5 ? ':00' : ''),
                'maghrib' => $times['maghrib'] . (strlen($times['maghrib']) === 5 ? ':00' : ''),
                'isha' => $times['isha'] . (strlen($times['isha']) === 5 ? ':00' : ''),
                'location' => $times['location'] ?? 'IOT Masjid',
                'updated_by' => $admin->id,
                'notes' => $times['notes'] ?? null,
            ]
        );
        
        return $prayerTime;
    }
    
    /**
     * Get upcoming prayer information
     */
    public function getUpcomingPrayer(): ?array
    {
        $todaysPrayerTimes = $this->getTodaysPrayerTimes();
        
        if (!$todaysPrayerTimes) {
            return null;
        }
        
        $now = now();
        $currentTime = $now->format('H:i:s');
        
        $prayers = [
            'fajr' => $todaysPrayerTimes->fajr,
            'dhuhr' => $todaysPrayerTimes->dhuhr,
            'asr' => $todaysPrayerTimes->asr,
            'maghrib' => $todaysPrayerTimes->maghrib,
            'isha' => $todaysPrayerTimes->isha,
        ];
        
        // Find current or next prayer
        foreach ($prayers as $name => $time) {
            // Handle both H:i and H:i:s formats
            $timeFormat = strlen($time) > 5 ? 'H:i:s' : 'H:i';
            $prayerTime = Carbon::createFromFormat($timeFormat, $time);
            $currentTimeCarbon = Carbon::createFromFormat('H:i:s', $currentTime);
            
            if ($prayerTime->gt($currentTimeCarbon)) {
                return [
                    'name' => ucfirst($name),
                    'time' => $time,
                    'formatted_time' => $prayerTime->format('g:i A'),
                    'is_current' => false,
                    'time_until' => $this->getTimeUntil($prayerTime),
                ];
            }
        }
        
        // If no prayer found for today, get tomorrow's Fajr
        $tomorrowPrayerTimes = PrayerTime::where('date', today()->addDay())->first();
        if ($tomorrowPrayerTimes) {
            $timeFormat = strlen($tomorrowPrayerTimes->fajr) > 5 ? 'H:i:s' : 'H:i';
            $fajrTime = Carbon::createFromFormat($timeFormat, $tomorrowPrayerTimes->fajr)->addDay();
            return [
                'name' => 'Fajr (Tomorrow)',
                'time' => $tomorrowPrayerTimes->fajr,
                'formatted_time' => $fajrTime->format('g:i A'),
                'is_current' => false,
                'time_until' => $this->getTimeUntil($fajrTime),
            ];
        }
        
        return null;
    }
    
    /**
     * Get current prayer (if within prayer time window)
     */
    public function getCurrentPrayer(): ?array
    {
        $todaysPrayerTimes = $this->getTodaysPrayerTimes();
        
        if (!$todaysPrayerTimes) {
            return null;
        }
        
        $now = now();
        $currentTime = $now->format('H:i:s');
        
        $prayers = [
            'fajr' => ['time' => $todaysPrayerTimes->fajr, 'duration' => 90], // 1.5 hours
            'dhuhr' => ['time' => $todaysPrayerTimes->dhuhr, 'duration' => 60], // 1 hour
            'asr' => ['time' => $todaysPrayerTimes->asr, 'duration' => 60], // 1 hour
            'maghrib' => ['time' => $todaysPrayerTimes->maghrib, 'duration' => 45], // 45 minutes
            'isha' => ['time' => $todaysPrayerTimes->isha, 'duration' => 120], // 2 hours
        ];
        
        foreach ($prayers as $name => $prayer) {
            $timeFormat = strlen($prayer['time']) > 5 ? 'H:i:s' : 'H:i';
            $prayerStart = Carbon::createFromFormat($timeFormat, $prayer['time']);
            $prayerEnd = $prayerStart->copy()->addMinutes($prayer['duration']);
            $currentTimeCarbon = Carbon::createFromFormat('H:i:s', $currentTime);
            
            if ($currentTimeCarbon->between($prayerStart, $prayerEnd)) {
                return [
                    'name' => ucfirst($name),
                    'time' => $prayer['time'],
                    'formatted_time' => $prayerStart->format('g:i A'),
                    'is_current' => true,
                    'ends_at' => $prayerEnd->format('g:i A'),
                    'time_remaining' => $this->getTimeUntil($prayerEnd),
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Get prayer times for a specific date
     */
    public function getPrayerTimesForDate(Carbon $date): ?PrayerTime
    {
        return PrayerTime::where('date', $date->format('Y-m-d'))->first();
    }
    
    /**
     * Get prayer times for a date range
     */
    public function getPrayerTimesForRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return PrayerTime::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])->orderBy('date')->get();
    }
    
    /**
     * Get all prayer times with pagination
     */
    public function getAllPrayerTimes(int $perPage = 15)
    {
        return PrayerTime::with('updatedBy')
            ->orderBy('date', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Delete prayer times for a specific date
     */
    public function deletePrayerTimes(Carbon $date): bool
    {
        return PrayerTime::where('date', $date->format('Y-m-d'))->delete() > 0;
    }
    
    /**
     * Bulk update prayer times
     */
    public function bulkUpdatePrayerTimes(array $prayerTimesData, User $admin): Collection
    {
        $updatedPrayerTimes = collect();
        
        foreach ($prayerTimesData as $data) {
            $prayerTime = $this->updatePrayerTimes($data, $admin);
            $updatedPrayerTimes->push($prayerTime);
        }
        
        return $updatedPrayerTimes;
    }
    
    /**
     * Get formatted prayer times for display
     */
    public function getFormattedPrayerTimes(?PrayerTime $prayerTime = null): array
    {
        if (!$prayerTime) {
            $prayerTime = $this->getTodaysPrayerTimes();
        }
        
        if (!$prayerTime) {
            return [];
        }
        
        return [
            'date' => Carbon::parse($prayerTime->date)->format('l, F j, Y'),
            'location' => $prayerTime->location,
            'prayers' => [
                'fajr' => [
                    'name' => 'Fajr',
                    'time' => $prayerTime->fajr,
                    'formatted' => Carbon::createFromFormat(strlen($prayerTime->fajr) > 5 ? 'H:i:s' : 'H:i', $prayerTime->fajr)->format('g:i A'),
                ],
                'dhuhr' => [
                    'name' => 'Dhuhr',
                    'time' => $prayerTime->dhuhr,
                    'formatted' => Carbon::createFromFormat(strlen($prayerTime->dhuhr) > 5 ? 'H:i:s' : 'H:i', $prayerTime->dhuhr)->format('g:i A'),
                ],
                'asr' => [
                    'name' => 'Asr',
                    'time' => $prayerTime->asr,
                    'formatted' => Carbon::createFromFormat(strlen($prayerTime->asr) > 5 ? 'H:i:s' : 'H:i', $prayerTime->asr)->format('g:i A'),
                ],
                'maghrib' => [
                    'name' => 'Maghrib',
                    'time' => $prayerTime->maghrib,
                    'formatted' => Carbon::createFromFormat(strlen($prayerTime->maghrib) > 5 ? 'H:i:s' : 'H:i', $prayerTime->maghrib)->format('g:i A'),
                ],
                'isha' => [
                    'name' => 'Isha',
                    'time' => $prayerTime->isha,
                    'formatted' => Carbon::createFromFormat(strlen($prayerTime->isha) > 5 ? 'H:i:s' : 'H:i', $prayerTime->isha)->format('g:i A'),
                ],
            ],
        ];
    }
    
    /**
     * Validate prayer times
     */
    private function validatePrayerTimes(array $times): void
    {
        $required = ['date', 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
        
        foreach ($required as $field) {
            if (!isset($times[$field]) || empty($times[$field])) {
                throw new \InvalidArgumentException("Field {$field} is required");
            }
        }
        
        // Validate time format
        $timeFields = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
        foreach ($timeFields as $field) {
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $times[$field])) {
                throw new \InvalidArgumentException("Invalid time format for {$field}");
            }
        }
        
        // Validate time sequence
        $this->validateTimeSequence($times);
    }
    
    /**
     * Validate that prayer times are in correct sequence
     */
    private function validateTimeSequence(array $times): void
    {
        $timeOrder = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
        
        for ($i = 0; $i < count($timeOrder) - 1; $i++) {
            $current = Carbon::createFromFormat('H:i', $times[$timeOrder[$i]]);
            $next = Carbon::createFromFormat('H:i', $times[$timeOrder[$i + 1]]);
            
            if ($next->lte($current)) {
                throw new \InvalidArgumentException(
                    "Prayer time {$timeOrder[$i + 1]} must be after {$timeOrder[$i]}"
                );
            }
        }
    }
    
    /**
     * Calculate time until a specific time
     */
    private function getTimeUntil(Carbon $targetTime): string
    {
        $now = now();
        $diff = $now->diff($targetTime);
        
        if ($diff->days > 0) {
            return $diff->days . ' day(s), ' . $diff->h . ' hour(s), ' . $diff->i . ' minute(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour(s), ' . $diff->i . ' minute(s)';
        } else {
            return $diff->i . ' minute(s)';
        }
    }
}