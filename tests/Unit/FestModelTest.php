<?php

namespace Tests\Unit;

use App\Models\Fest;
use App\Models\User;
use App\Models\Event;
use App\Models\GalleryImage;
use Tests\TestCase;
use Carbon\Carbon;

class FestModelTest extends TestCase
{
    /** @test */
    public function it_has_fillable_attributes()
    {
        $fest = new Fest();
        $expected = [
            'title', 'description', 'start_date', 'end_date',
            'banner_image', 'status', 'created_by'
        ];
        
        $this->assertEquals($expected, $fest->getFillable());
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $fest = Fest::factory()->create([
            'start_date' => '2024-12-25',
            'end_date' => '2024-12-27'
        ]);
        
        $this->assertInstanceOf(Carbon::class, $fest->start_date);
        $this->assertInstanceOf(Carbon::class, $fest->end_date);
    }

    /** @test */
    public function it_belongs_to_creator()
    {
        $user = User::factory()->create();
        $fest = Fest::factory()->create(['created_by' => $user->id]);
        
        $this->assertInstanceOf(User::class, $fest->creator);
        $this->assertEquals($user->id, $fest->creator->id);
    }

    /** @test */
    public function it_has_many_events()
    {
        $fest = Fest::factory()->create();
        Event::factory()->count(3)->create(['fest_id' => $fest->id]);
        
        $this->assertCount(3, $fest->events);
        $this->assertInstanceOf(Event::class, $fest->events->first());
    }

    /** @test */
    public function it_has_many_gallery_images()
    {
        $fest = Fest::factory()->create();
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Fest::class,
            'imageable_id' => $fest->id
        ]);
        
        $this->assertCount(2, $fest->gallery);
        $this->assertInstanceOf(GalleryImage::class, $fest->gallery->first());
    }

    /** @test */
    public function it_checks_if_fest_is_active()
    {
        $activeFest = Fest::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);
        
        $upcomingFest = Fest::factory()->create([
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3)
        ]);
        
        $pastFest = Fest::factory()->create([
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDay()
        ]);
        
        $this->assertTrue($activeFest->isActive());
        $this->assertFalse($upcomingFest->isActive());
        $this->assertFalse($pastFest->isActive());
    }

    /** @test */
    public function it_checks_if_fest_is_upcoming()
    {
        $upcomingFest = Fest::factory()->create([
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3)
        ]);
        
        $activeFest = Fest::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);
        
        $this->assertTrue($upcomingFest->isUpcoming());
        $this->assertFalse($activeFest->isUpcoming());
    }

    /** @test */
    public function it_checks_if_fest_is_completed()
    {
        $pastFest = Fest::factory()->create([
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDay()
        ]);
        
        $completedFest = Fest::factory()->create([
            'status' => 'completed',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3)
        ]);
        
        $activeFest = Fest::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);
        
        $this->assertTrue($pastFest->isCompleted());
        $this->assertTrue($completedFest->isCompleted());
        $this->assertFalse($activeFest->isCompleted());
    }

    /** @test */
    public function it_calculates_duration_in_days()
    {
        $singleDayFest = Fest::factory()->create([
            'start_date' => '2024-12-25',
            'end_date' => '2024-12-25'
        ]);
        
        $threeDayFest = Fest::factory()->create([
            'start_date' => '2024-12-25',
            'end_date' => '2024-12-27'
        ]);
        
        $this->assertEquals(1, $singleDayFest->getDurationInDays());
        $this->assertEquals(3, $threeDayFest->getDurationInDays());
    }

    /** @test */
    public function it_scopes_published_fests()
    {
        Fest::factory()->create(['status' => 'published']);
        Fest::factory()->create(['status' => 'draft']);
        Fest::factory()->create(['status' => 'completed']);
        
        $publishedFests = Fest::published()->get();
        
        $this->assertCount(1, $publishedFests);
        $this->assertEquals('published', $publishedFests->first()->status);
    }

    /** @test */
    public function it_scopes_active_fests()
    {
        Fest::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);
        
        Fest::factory()->create([
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3)
        ]);
        
        Fest::factory()->create([
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDay()
        ]);
        
        $activeFests = Fest::active()->get();
        
        $this->assertCount(1, $activeFests);
    }

    /** @test */
    public function it_scopes_upcoming_fests()
    {
        Fest::factory()->create([
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3)
        ]);
        
        Fest::factory()->create([
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDays(2)
        ]);
        
        Fest::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);
        
        $upcomingFests = Fest::upcoming()->get();
        
        $this->assertCount(2, $upcomingFests);
    }
}