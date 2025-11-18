<?php

namespace Database\Factories;

use App\Models\GalleryImage;
use App\Models\User;
use App\Models\Event;
use App\Models\Fest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GalleryImage>
 */
class GalleryImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GalleryImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imageable_type' => null,
            'imageable_id' => null,
            'image_path' => 'gallery/general/' . $this->faker->uuid() . '.jpg',
            'thumbnail_path' => null,
            'caption' => $this->faker->optional()->sentence(),
            'alt_text' => $this->faker->optional()->sentence(),
            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the image belongs to an event.
     */
    public function forEvent(?Event $event = null): static
    {
        return $this->state(function (array $attributes) use ($event) {
            $eventId = $event ? $event->id : Event::factory()->create()->id;
            
            return [
                'imageable_type' => Event::class,
                'imageable_id' => $eventId,
                'image_path' => "gallery/events/{$eventId}/" . $this->faker->uuid() . '.jpg',
                'alt_text' => 'Image from event',
            ];
        });
    }

    /**
     * Indicate that the image belongs to a fest.
     */
    public function forFest(?Fest $fest = null): static
    {
        return $this->state(function (array $attributes) use ($fest) {
            $festId = $fest ? $fest->id : Fest::factory()->create()->id;
            
            return [
                'imageable_type' => Fest::class,
                'imageable_id' => $festId,
                'image_path' => "gallery/fests/{$festId}/" . $this->faker->uuid() . '.jpg',
                'alt_text' => 'Image from fest',
            ];
        });
    }

    /**
     * Indicate that the image is in the general gallery.
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'imageable_type' => null,
            'imageable_id' => null,
            'image_path' => 'gallery/general/' . $this->faker->uuid() . '.jpg',
            'alt_text' => 'General gallery image',
        ]);
    }

    /**
     * Set a specific caption.
     */
    public function withCaption(string $caption): static
    {
        return $this->state(fn (array $attributes) => [
            'caption' => $caption,
        ]);
    }

    /**
     * Set a specific alt text.
     */
    public function withAltText(string $altText): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => $altText,
        ]);
    }

    /**
     * Set a specific uploader.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Include a thumbnail path.
     */
    public function withThumbnail(): static
    {
        return $this->state(function (array $attributes) {
            $imagePath = $attributes['image_path'] ?? 'gallery/general/' . $this->faker->uuid() . '.jpg';
            $pathInfo = pathinfo($imagePath);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/thumb_' . $pathInfo['basename'];
            
            return [
                'thumbnail_path' => $thumbnailPath,
            ];
        });
    }

    /**
     * Set a specific image path.
     */
    public function withImagePath(string $path): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => $path,
        ]);
    }
}