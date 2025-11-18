<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fest_id',
        'title',
        'description',
        'event_date',
        'event_time',
        'type',
        'registration_type',
        'location',
        'max_participants',
        'fee_amount',
        'registration_deadline',
        'status',
        'author_id',
        'image'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'event_time' => 'datetime',
            'registration_deadline' => 'datetime',
            'fee_amount' => 'decimal:2',
            'max_participants' => 'integer',
        ];
    }

    /**
     * Get the user who authored this event.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the fest this event belongs to.
     */
    public function fest()
    {
        return $this->belongsTo(Fest::class);
    }

    /**
     * Get the registrations for this event.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the gallery images for this event.
     */
    public function gallery()
    {
        return $this->morphMany(GalleryImage::class, 'imageable');
    }

    /**
     * Get approved registrations for this event.
     */
    public function approvedRegistrations()
    {
        return $this->hasMany(Registration::class)->where('status', 'approved');
    }

    /**
     * Get pending registrations for this event.
     */
    public function pendingRegistrations()
    {
        return $this->hasMany(Registration::class)->where('status', 'pending');
    }

    /**
     * Check if registration is open for this event.
     */
    public function isRegistrationOpen(): bool
    {
        if ($this->registration_type === 'on_spot') {
            return false;
        }

        if ($this->registration_deadline && $this->registration_deadline < now()) {
            return false;
        }

        if ($this->max_participants && $this->getRegisteredCount() >= $this->max_participants) {
            return false;
        }

        return $this->status === 'published';
    }

    /**
     * Get the count of registered participants.
     */
    public function getRegisteredCount(): int
    {
        return $this->approvedRegistrations()->count();
    }

    /**
     * Get available spots for registration.
     */
    public function getAvailableSpots(): ?int
    {
        if (!$this->max_participants) {
            return null;
        }

        return max(0, $this->max_participants - $this->getRegisteredCount());
    }

    /**
     * Check if event is full.
     */
    public function isFull(): bool
    {
        if (!$this->max_participants) {
            return false;
        }

        return $this->getRegisteredCount() >= $this->max_participants;
    }

    /**
     * Check if event requires payment.
     */
    public function requiresPayment(): bool
    {
        return $this->fee_amount > 0;
    }

    /**
     * Check if event allows individual registration.
     */
    public function allowsIndividualRegistration(): bool
    {
        return in_array($this->registration_type, ['individual', 'both']);
    }

    /**
     * Check if event allows team registration.
     */
    public function allowsTeamRegistration(): bool
    {
        return in_array($this->registration_type, ['team', 'both']);
    }

    /**
     * Check if event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->event_date > now()->toDateString();
    }

    /**
     * Check if event is today.
     */
    public function isToday(): bool
    {
        return $this->event_date->isToday();
    }

    /**
     * Check if event is completed.
     */
    public function isCompleted(): bool
    {
        return $this->event_date < now()->toDateString() || $this->status === 'completed';
    }

    /**
     * Scope to get published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>', now()->toDateString());
    }

    /**
     * Scope to get events by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get events with open registration.
     */
    public function scopeWithOpenRegistration($query)
    {
        return $query->where('status', 'published')
                    ->where('registration_type', '!=', 'on_spot')
                    ->where(function ($q) {
                        $q->whereNull('registration_deadline')
                          ->orWhere('registration_deadline', '>', now());
                    });
    }
}

