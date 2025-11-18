<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PrayerTimeService;
use App\Models\PrayerTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PrayerTimeServiceTest extends TestCase
{
    use RefreshDatabase;

    private PrayerTimeService $prayerTimeService;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->prayerTimeService = new PrayerTimeService();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    public function test_get_todays_prayer_times()
    {
        $prayerTime = PrayerTime::factory()->create([
            'date' => today(),
        ]);

        $result = $this->prayerTimeService->getTodaysPrayerTimes();

        $this->assertInstanceOf(PrayerTime::class, $result);
        $this->assertEquals($prayerTime->id, $result->id);
    }

    public function test_get_todays_prayer_times_returns_null_when_not_found()
    {
        $result = $this->prayerTimeService->getTodaysPrayerTimes();

        $this->assertNull($result);
    }

    public function test_update_prayer_times_creates_new_record()
    {
        $times = [
            'date' => today()->format('Y-m-d'),
            'fajr' => '05:30',
            'dhuhr' => '12:15',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '20:00',
            'location' => 'IOT Masjid',
            'notes' => 'Test prayer times',
        ];

        $prayerTime = $this->prayerTimeService->updatePrayerTimes($times, $this->admin);

        $this->assertInstanceOf(PrayerTime::class, $prayerTime);
        $this->assertEquals('05:30:00', $prayerTime->fajr);
        $this->assertEquals('12:15:00', $prayerTime->dhuhr);
        $this->assertEquals('15:45:00', $prayerTime->asr);
        $this->assertEquals('18:30:00', $prayerTime->maghrib);
        $this->assertEquals('20:00:00', $prayerTime->isha);
        $this->assertEquals('IOT Masjid', $prayerTime->location);
        $this->assertEquals('Test prayer times', $prayerTime->notes);
        $this->assertEquals($this->admin->id, $prayerTime->updated_by);
    }

    public function test_update_prayer_times_updates_existing_record()
    {
        $existingPrayerTime = PrayerTime::factory()->create([
            'date' => today(),
            'fajr' => '05:00:00',
        ]);

        $times = [
            'date' => today()->format('Y-m-d'),
            'fajr' => '05:30',
            'dhuhr' => '12:15',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '20:00',
        ];

        $prayerTime = $this->prayerTimeService->updatePrayerTimes($times, $this->admin);

        $this->assertEquals($existingPrayerTime->id, $prayerTime->id);
        $this->assertEquals('05:30:00', $prayerTime->fajr);
    }

    public function test_update_prayer_times_validates_time_sequence()
    {
        $times = [
            'date' => today()->format('Y-m-d'),
            'fajr' => '12:00', // Invalid: Fajr after Dhuhr
            'dhuhr' => '11:00',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '20:00',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Prayer time dhuhr must be after fajr');

        $this->prayerTimeService->updatePrayerTimes($times, $this->admin);
    }

    public function test_update_prayer_times_validates_required_fields()
    {
        $times = [
            'date' => today()->format('Y-m-d'),
            'fajr' => '05:30',
            // Missing other required fields
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field dhuhr is required');

        $this->prayerTimeService->updatePrayerTimes($times, $this->admin);
    }

    public function test_update_prayer_times_validates_time_format()
    {
        $times = [
            'date' => today()->format('Y-m-d'),
            'fajr' => '25:30', // Invalid hour
            'dhuhr' => '12:15',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '20:00',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time format for fajr');

        $this->prayerTimeService->updatePrayerTimes($times, $this->admin);
    }

    public function test_get_upcoming_prayer()
    {
        // Create prayer times for today
        PrayerTime::factory()->create([
            'date' => today(),
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00',
        ]);

        // Mock current time to be 10:00 AM
        Carbon::setTestNow(today()->setTime(10, 0, 0));

        $upcomingPrayer = $this->prayerTimeService->getUpcomingPrayer();

        $this->assertNotNull($upcomingPrayer);
        $this->assertEquals('Dhuhr', $upcomingPrayer['name']);
        $this->assertEquals('12:15:00', $upcomingPrayer['time']);
        $this->assertEquals('12:15 PM', $upcomingPrayer['formatted_time']);
        $this->assertFalse($upcomingPrayer['is_current']);

        Carbon::setTestNow(); // Reset
    }

    public function test_get_upcoming_prayer_returns_null_when_no_prayer_times()
    {
        $upcomingPrayer = $this->prayerTimeService->getUpcomingPrayer();

        $this->assertNull($upcomingPrayer);
    }

    public function test_get_current_prayer()
    {
        // Create prayer times for today
        PrayerTime::factory()->create([
            'date' => today(),
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00',
        ]);

        // Mock current time to be during Dhuhr prayer (12:30 PM)
        Carbon::setTestNow(today()->setTime(12, 30, 0));

        $currentPrayer = $this->prayerTimeService->getCurrentPrayer();

        $this->assertNotNull($currentPrayer);
        $this->assertEquals('Dhuhr', $currentPrayer['name']);
        $this->assertEquals('12:15:00', $currentPrayer['time']);
        $this->assertTrue($currentPrayer['is_current']);
        $this->assertEquals('1:15 PM', $currentPrayer['ends_at']);

        Carbon::setTestNow(); // Reset
    }

    public function test_get_prayer_times_for_date()
    {
        $testDate = Carbon::parse('2024-01-15');
        $prayerTime = PrayerTime::factory()->create([
            'date' => $testDate->format('Y-m-d'),
        ]);

        $result = $this->prayerTimeService->getPrayerTimesForDate($testDate);

        $this->assertInstanceOf(PrayerTime::class, $result);
        $this->assertEquals($prayerTime->id, $result->id);
    }

    public function test_get_prayer_times_for_range()
    {
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-03');

        // Create prayer times for the range
        PrayerTime::factory()->create(['date' => '2024-01-01']);
        PrayerTime::factory()->create(['date' => '2024-01-02']);
        PrayerTime::factory()->create(['date' => '2024-01-03']);
        PrayerTime::factory()->create(['date' => '2024-01-04']); // Outside range

        $result = $this->prayerTimeService->getPrayerTimesForRange($startDate, $endDate);

        $this->assertCount(3, $result);
        $this->assertEquals('2024-01-01', $result->first()->date->format('Y-m-d'));
        $this->assertEquals('2024-01-03', $result->last()->date->format('Y-m-d'));
    }

    public function test_delete_prayer_times()
    {
        $testDate = Carbon::parse('2024-01-15');
        PrayerTime::factory()->create([
            'date' => $testDate->format('Y-m-d'),
        ]);

        $result = $this->prayerTimeService->deletePrayerTimes($testDate);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('prayer_times', [
            'date' => $testDate->format('Y-m-d'),
        ]);
    }

    public function test_bulk_update_prayer_times()
    {
        $prayerTimesData = [
            [
                'date' => '2024-01-01',
                'fajr' => '05:30',
                'dhuhr' => '12:15',
                'asr' => '15:45',
                'maghrib' => '18:30',
                'isha' => '20:00',
            ],
            [
                'date' => '2024-01-02',
                'fajr' => '05:31',
                'dhuhr' => '12:16',
                'asr' => '15:46',
                'maghrib' => '18:31',
                'isha' => '20:01',
            ],
        ];

        $result = $this->prayerTimeService->bulkUpdatePrayerTimes($prayerTimesData, $this->admin);

        $this->assertCount(2, $result);
        $this->assertDatabaseHas('prayer_times', ['date' => '2024-01-01']);
        $this->assertDatabaseHas('prayer_times', ['date' => '2024-01-02']);
    }

    public function test_get_formatted_prayer_times()
    {
        $prayerTime = PrayerTime::factory()->create([
            'date' => '2024-01-15',
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00',
            'location' => 'IOT Masjid',
        ]);

        $formatted = $this->prayerTimeService->getFormattedPrayerTimes($prayerTime);

        $this->assertEquals('Monday, January 15, 2024', $formatted['date']);
        $this->assertEquals('IOT Masjid', $formatted['location']);
        $this->assertArrayHasKey('prayers', $formatted);
        $this->assertEquals('5:30 AM', $formatted['prayers']['fajr']['formatted']);
        $this->assertEquals('12:15 PM', $formatted['prayers']['dhuhr']['formatted']);
        $this->assertEquals('3:45 PM', $formatted['prayers']['asr']['formatted']);
        $this->assertEquals('6:30 PM', $formatted['prayers']['maghrib']['formatted']);
        $this->assertEquals('8:00 PM', $formatted['prayers']['isha']['formatted']);
    }

    public function test_get_formatted_prayer_times_uses_todays_when_null()
    {
        PrayerTime::factory()->create([
            'date' => today(),
            'location' => 'Test Masjid',
        ]);

        $formatted = $this->prayerTimeService->getFormattedPrayerTimes();

        $this->assertEquals('Test Masjid', $formatted['location']);
        $this->assertArrayHasKey('prayers', $formatted);
    }

    public function test_get_formatted_prayer_times_returns_empty_when_no_data()
    {
        $formatted = $this->prayerTimeService->getFormattedPrayerTimes();

        $this->assertEquals([], $formatted);
    }
}