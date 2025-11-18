<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PrayerTime extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'fajr',
        'dhuhr',
        'asr',
        'maghrib',
        'isha',
        'location',
        'updated_by',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the user who last updated these prayer times.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all prayer times as an array with names.
     */
    public function getAllPrayerTimes(): array
    {
        return [
            'fajr' => [
                'name' => 'Fajr',
                'time' => $this->fajr,
                'formatted' => $this->getFormattedTime('fajr'),
            ],
            'dhuhr' => [
                'name' => 'Dhuhr',
                'time' => $this->dhuhr,
                'formatted' => $this->getFormattedTime('dhuhr'),
            ],
            'asr' => [
                'name' => 'Asr',
                'time' => $this->asr,
                'formatted' => $this->getFormattedTime('asr'),
            ],
            'maghrib' => [
                'name' => 'Maghrib',
                'time' => $this->maghrib,
                'formatted' => $this->getFormattedTime('maghrib'),
            ],
            'isha' => [
                'name' => 'Isha',
                'time' => $this->isha,
                'formatted' => $this->getFormattedTime('isha'),
            ],
        ];
    }

    /**
     * Get formatted time for a specific prayer.
     */
    public function getFormattedTime(string $prayer): string
    {
        if (!in_array($prayer, ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'])) {
            return '';
        }

        $time = $this->{$prayer};
        if (!$time) {
            return '';
        }

        return Carbon::parse($time)->format('g:i A');
    }

    /**
     * Get the current prayer based on current time.
     */
    public function getCurrentPrayer(): ?array
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        
        $prayers = [
            'fajr' => Carbon::parse($this->fajr)->format('H:i:s'),
            'dhuhr' => Carbon::parse($this->dhuhr)->format('H:i:s'),
            'asr' => Carbon::parse($this->asr)->format('H:i:s'),
            'maghrib' => Carbon::parse($this->maghrib)->format('H:i:s'),
            'isha' => Carbon::parse($this->isha)->format('H:i:s'),
        ];

        $currentPrayer = null;
        foreach ($prayers as $name => $time) {
            if ($currentTime >= $time) {
                $currentPrayer = [
                    'name' => ucfirst($name),
                    'time' => $this->{$name},
                    'formatted' => $this->getFormattedTime($name),
                ];
            }
        }

        return $currentPrayer;
    }

    /**
     * Get the next prayer based on current time.
     */
    public function getNextPrayer(): ?array
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        
        $prayers = [
            'fajr' => Carbon::parse($this->fajr)->format('H:i:s'),
            'dhuhr' => Carbon::parse($this->dhuhr)->format('H:i:s'),
            'asr' => Carbon::parse($this->asr)->format('H:i:s'),
            'maghrib' => Carbon::parse($this->maghrib)->format('H:i:s'),
            'isha' => Carbon::parse($this->isha)->format('H:i:s'),
        ];

        foreach ($prayers as $name => $time) {
            if ($currentTime < $time) {
                return [
                    'name' => ucfirst($name),
                    'time' => $this->{$name},
                    'formatted' => $this->getFormattedTime($name),
                ];
            }
        }

        // If no prayer is found for today, return tomorrow's Fajr
        $tomorrow = $now->copy()->addDay();
        $tomorrowPrayerTime = static::where('date', $tomorrow->toDateString())->first();
        
        if ($tomorrowPrayerTime) {
            return [
                'name' => 'Fajr (Tomorrow)',
                'time' => $tomorrowPrayerTime->fajr,
                'formatted' => $tomorrowPrayerTime->getFormattedTime('fajr'),
            ];
        }

        return null;
    }

    /**
     * Get time remaining until next prayer.
     */
    public function getTimeUntilNextPrayer(): ?string
    {
        $nextPrayer = $this->getNextPrayer();
        if (!$nextPrayer) {
            return null;
        }

        $now = Carbon::now();
        $nextPrayerTime = Carbon::parse($nextPrayer['time']);
        
        // If next prayer is tomorrow's Fajr
        if (str_contains($nextPrayer['name'], 'Tomorrow')) {
            $nextPrayerTime = $nextPrayerTime->addDay();
        }

        $diff = $now->diff($nextPrayerTime);
        
        if ($diff->h > 0) {
            return $diff->format('%h hours %i minutes');
        }
        
        return $diff->format('%i minutes');
    }

    /**
     * Validate prayer times are in logical order.
     */
    public function validatePrayerTimesOrder(): bool
    {
        $times = [
            Carbon::parse($this->fajr),
            Carbon::parse($this->dhuhr),
            Carbon::parse($this->asr),
            Carbon::parse($this->maghrib),
            Carbon::parse($this->isha),
        ];

        for ($i = 1; $i < count($times); $i++) {
            if ($times[$i] <= $times[$i - 1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope to get prayer times for today.
     */
    public function scopeToday($query)
    {
        return $query->where('date', Carbon::today()->toDateString());
    }

    /**
     * Scope to get prayer times for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', Carbon::parse($date)->toDateString());
    }

    /**
     * Scope to get recent prayer times.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', Carbon::today()->subDays($days)->toDateString())
                    ->orderBy('date', 'desc');
    }

    /**
     * Get today's prayer times or create default if not exists.
     */
    public static function getTodaysPrayerTimes(): ?self
    {
        return static::today()->first();
    }

    /**
     * Get prayer times for a specific date.
     */
    public static function getPrayerTimesForDate($date): ?self
    {
        return static::forDate($date)->first();
    }
}