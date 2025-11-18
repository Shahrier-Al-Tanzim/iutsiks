<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'banner_image',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the user who created this fest.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the events associated with this fest.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the gallery images for this fest.
     */
    public function gallery()
    {
        return $this->morphMany(GalleryImage::class, 'imageable');
    }

    /**
     * Check if the fest is currently active.
     */
    public function isActive(): bool
    {
        $now = now()->toDateString();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    /**
     * Check if the fest is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date > now()->toDateString();
    }

    /**
     * Check if the fest is completed.
     */
    public function isCompleted(): bool
    {
        return $this->end_date < now()->toDateString() || $this->status === 'completed';
    }

    /**
     * Get the duration of the fest in days.
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope to get published fests.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get active fests.
     */
    public function scopeActive($query)
    {
        $now = now()->toDateString();
        return $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    /**
     * Scope to get upcoming fests.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now()->toDateString());
    }
}