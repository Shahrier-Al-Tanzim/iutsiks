<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PrayerTime;
use App\Services\PrayerTimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PrayerTimeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $prayerTimeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'super_admin'
        ]);
        
        $this->prayerTimeService = app(PrayerTimeService::class);
    }

    public function test_can_view_prayer_times_index()
    {
        $response = $this->get(route('prayer-times.index'));
        $response->assertStatus(200);
        $response->assertViewIs('prayer-times.index');
    }

    public function test_can_create_prayer_times()
    {
        $times = [
            'date' => Carbon::today()->format('Y-m-d'),
            'fajr' => '05:30',
            'dhuhr' => '12:15',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '19:45',
            'location' => 'IOT Masjid'
        ];

        $prayerTime = $this->prayerTimeService->updatePrayerTimes($times, $this->admin);

        $this->assertInstanceOf(PrayerTime::class, $prayerTime);
        $this->assertEquals($times['date'], $prayerTime->date->format('Y-m-d'));
        $this->assertEquals($times['fajr'] . ':00', $prayerTime->fajr);
    }

    public function test_admin_can_access_prayer_times_management()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.prayer-times.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.prayer-times.index');
    }

    public function test_regular_user_cannot_access_prayer_times_management()
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $response = $this->actingAs($user)
            ->get(route('admin.prayer-times.index'));
        
        $response->assertStatus(403);
    }

    public function test_prayer_times_widget_endpoint()
    {
        // Create prayer times for today
        $times = [
            'date' => Carbon::today()->format('Y-m-d'),
            'fajr' => '05:30',
            'dhuhr' => '12:15',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '19:45',
            'location' => 'IOT Masjid'
        ];

        $this->prayerTimeService->updatePrayerTimes($times, $this->admin);

        $response = $this->get(route('prayer-times.widget'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'prayer_times' => [
                'date',
                'location',
                'prayers' => [
                    'fajr' => ['name', 'time', 'formatted'],
                    'dhuhr' => ['name', 'time', 'formatted'],
                    'asr' => ['name', 'time', 'formatted'],
                    'maghrib' => ['name', 'time', 'formatted'],
                    'isha' => ['name', 'time', 'formatted'],
                ]
            ],
            'current_prayer',
            'next_prayer'
        ]);
    }

    public function test_prayer_time_validation()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        // Invalid times (Dhuhr before Fajr)
        $times = [
            'date' => Carbon::today()->format('Y-m-d'),
            'fajr' => '12:30',
            'dhuhr' => '05:15',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '19:45',
            'location' => 'IOT Masjid'
        ];

        $this->prayerTimeService->updatePrayerTimes($times, $this->admin);
    }
}