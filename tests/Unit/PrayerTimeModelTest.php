<?php

namespace Tests\Unit;

use App\Models\PrayerTime;
use App\Models\User;
use Tests\TestCase;
use Carbon\Carbon;

class PrayerTimeModelTest extends TestCase
{
    /** @test */
    public function it_has_fillable_attributes()
    {
        $prayerTime = new PrayerTime();
        $expected = [
            'date', 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha',
            'location', 'updated_by', 'notes'
        ];
        
        $this->assertEquals($expected, $prayerTime->getFillable());
    }

    /** @test */
    public function it_casts_date_correctly()
    {
        $prayerTime = PrayerTime::factory()->create([
            'date' => '2024-12-25'
        ]);
        
        $this->assertInstanceOf(Carbon::class, $prayerTime->date);
    }

    /** @test */
    public function it_belongs_to_updated_by_user()
    {
        $user = User::factory()->create();
        $prayerTime = PrayerTime::factory()->create(['updated_by' => $user->id]);
        
        $this->assertInstanceOf(User::class, $prayerTime->updatedBy);
        $this->assertEquals($user->id, $prayerTime->updatedBy->id);
    }

    /** @test */
    public function it_gets_all_prayer_times_as_array()
    {
        $prayerTime = PrayerTime::factory()->create([
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        $allPrayerTimes = $prayerTime->getAllPrayerTimes();
        
        $this->assertIsArray($allPrayerTimes);
        $this->assertArrayHasKey('fajr', $allPrayerTimes);
        $this->assertArrayHasKey('dhuhr', $allPrayerTimes);
        $this->assertArrayHasKey('asr', $allPrayerTimes);
        $this->assertArrayHasKey('maghrib', $allPrayerTimes);
        $this->assertArrayHasKey('isha', $allPrayerTimes);
        
        $this->assertEquals('Fajr', $allPrayerTimes['fajr']['name']);
        $this->assertEquals('05:30:00', $allPrayerTimes['fajr']['time']);
        $this->assertEquals('5:30 AM', $allPrayerTimes['fajr']['formatted']);
    }

    /** @test */
    public function it_formats_time_correctly()
    {
        $prayerTime = PrayerTime::factory()->create([
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        $this->assertEquals('5:30 AM', $prayerTime->getFormattedTime('fajr'));
        $this->assertEquals('12:15 PM', $prayerTime->getFormattedTime('dhuhr'));
        $this->assertEquals('3:45 PM', $prayerTime->getFormattedTime('asr'));
        $this->assertEquals('6:30 PM', $prayerTime->getFormattedTime('maghrib'));
        $this->assertEquals('8:00 PM', $prayerTime->getFormattedTime('isha'));
    }

    /** @test */
    public function it_returns_empty_string_for_invalid_prayer_name()
    {
        $prayerTime = PrayerTime::factory()->create();
        
        $this->assertEquals('', $prayerTime->getFormattedTime('invalid'));
    }

    /** @test */
    public function it_gets_current_prayer_correctly()
    {
        $prayerTime = PrayerTime::factory()->create([
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        // Mock current time to be 14:00 (2 PM)
        Carbon::setTestNow(Carbon::today()->setTime(14, 0, 0));
        
        $currentPrayer = $prayerTime->getCurrentPrayer();
        
        $this->assertNotNull($currentPrayer);
        $this->assertEquals('Dhuhr', $currentPrayer['name']);
        $this->assertEquals('12:15:00', $currentPrayer['time']);
        $this->assertEquals('12:15 PM', $currentPrayer['formatted']);
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function it_gets_next_prayer_correctly()
    {
        $prayerTime = PrayerTime::factory()->create([
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        // Mock current time to be 10:00 AM
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));
        
        $nextPrayer = $prayerTime->getNextPrayer();
        
        $this->assertNotNull($nextPrayer);
        $this->assertEquals('Dhuhr', $nextPrayer['name']);
        $this->assertEquals('12:15:00', $nextPrayer['time']);
        $this->assertEquals('12:15 PM', $nextPrayer['formatted']);
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function it_gets_tomorrows_fajr_when_all_prayers_passed()
    {
        $todayPrayerTime = PrayerTime::factory()->create([
            'date' => Carbon::today(),
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        $tomorrowPrayerTime = PrayerTime::factory()->create([
            'date' => Carbon::tomorrow(),
            'fajr' => '05:31:00',
            'dhuhr' => '12:16:00',
            'asr' => '15:46:00',
            'maghrib' => '18:31:00',
            'isha' => '20:01:00'
        ]);
        
        // Mock current time to be 22:00 (10 PM) - after all prayers
        Carbon::setTestNow(Carbon::today()->setTime(22, 0, 0));
        
        $nextPrayer = $todayPrayerTime->getNextPrayer();
        
        $this->assertNotNull($nextPrayer);
        $this->assertEquals('Fajr (Tomorrow)', $nextPrayer['name']);
        $this->assertEquals('05:31:00', $nextPrayer['time']);
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function it_calculates_time_until_next_prayer()
    {
        $prayerTime = PrayerTime::factory()->create([
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        // Mock current time to be 11:00 AM (1 hour 15 minutes before Dhuhr)
        Carbon::setTestNow(Carbon::today()->setTime(11, 0, 0));
        
        $timeUntil = $prayerTime->getTimeUntilNextPrayer();
        
        $this->assertNotNull($timeUntil);
        $this->assertEquals('1 hours 15 minutes', $timeUntil);
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function it_validates_prayer_times_order()
    {
        $validPrayerTime = PrayerTime::factory()->create([
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        $invalidPrayerTime = PrayerTime::factory()->create([
            'fajr' => '12:00:00', // Invalid: after Dhuhr
            'dhuhr' => '11:00:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00'
        ]);
        
        $this->assertTrue($validPrayerTime->validatePrayerTimesOrder());
        $this->assertFalse($invalidPrayerTime->validatePrayerTimesOrder());
    }

    /** @test */
    public function it_scopes_today_prayer_times()
    {
        PrayerTime::factory()->create(['date' => Carbon::today()]);
        PrayerTime::factory()->create(['date' => Carbon::yesterday()]);
        PrayerTime::factory()->create(['date' => Carbon::tomorrow()]);
        
        $todayPrayerTimes = PrayerTime::today()->get();
        
        $this->assertCount(1, $todayPrayerTimes);
        $this->assertEquals(Carbon::today()->toDateString(), $todayPrayerTimes->first()->date->toDateString());
    }

    /** @test */
    public function it_scopes_prayer_times_for_specific_date()
    {
        $specificDate = Carbon::parse('2024-01-15');
        PrayerTime::factory()->create(['date' => $specificDate]);
        PrayerTime::factory()->create(['date' => Carbon::today()]);
        
        $specificDatePrayerTimes = PrayerTime::forDate($specificDate)->get();
        
        $this->assertCount(1, $specificDatePrayerTimes);
        $this->assertEquals($specificDate->toDateString(), $specificDatePrayerTimes->first()->date->toDateString());
    }

    /** @test */
    public function it_scopes_recent_prayer_times()
    {
        // Create prayer times for the last 10 days
        for ($i = 0; $i < 10; $i++) {
            PrayerTime::factory()->create([
                'date' => Carbon::today()->subDays($i)
            ]);
        }
        
        $recentPrayerTimes = PrayerTime::recent(7)->get();
        
        $this->assertCount(8, $recentPrayerTimes); // 7 days + today
    }

    /** @test */
    public function it_gets_todays_prayer_times_statically()
    {
        $todayPrayerTime = PrayerTime::factory()->create(['date' => Carbon::today()]);
        PrayerTime::factory()->create(['date' => Carbon::yesterday()]);
        
        $result = PrayerTime::getTodaysPrayerTimes();
        
        $this->assertNotNull($result);
        $this->assertEquals($todayPrayerTime->id, $result->id);
    }

    /** @test */
    public function it_gets_prayer_times_for_date_statically()
    {
        $specificDate = Carbon::parse('2024-01-15');
        $specificPrayerTime = PrayerTime::factory()->create(['date' => $specificDate]);
        PrayerTime::factory()->create(['date' => Carbon::today()]);
        
        $result = PrayerTime::getPrayerTimesForDate($specificDate);
        
        $this->assertNotNull($result);
        $this->assertEquals($specificPrayerTime->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_no_prayer_times_found()
    {
        $result = PrayerTime::getTodaysPrayerTimes();
        $this->assertNull($result);
        
        $result = PrayerTime::getPrayerTimesForDate(Carbon::today());
        $this->assertNull($result);
    }
}