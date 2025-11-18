<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'registration_type',
        'team_name',
        'team_members_json',
        'individual_name',
        'payment_required',
        'payment_amount',
        'payment_status',
        'payment_method',
        'transaction_id',
        'payment_date',
        'admin_notes',
        'status',
        'registered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'team_members_json' => 'array',
            'payment_required' => 'boolean',
            'payment_amount' => 'decimal:2',
            'payment_date' => 'date',
            'registered_at' => 'datetime',
        ];
    }

    /**
     * Get the event this registration belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who made this registration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get team members as User models.
     */
    public function getTeamMembersAttribute()
    {
        if (!$this->team_members_json || $this->registration_type !== 'team') {
            return collect();
        }

        $memberIds = collect($this->team_members_json)->pluck('user_id')->filter();
        return User::whereIn('id', $memberIds)->get();
    }

    /**
     * Set team members from User models or array of user data.
     */
    public function setTeamMembers($members)
    {
        if ($members instanceof \Illuminate\Support\Collection) {
            $members = $members->toArray();
        }

        $teamMembersData = collect($members)->map(function ($member) {
            if ($member instanceof User) {
                return [
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'student_id' => $member->student_id,
                ];
            }
            
            return $member;
        })->toArray();

        $this->team_members_json = $teamMembersData;
    }

    /**
     * Get the count of team members.
     */
    public function getTeamMemberCount(): int
    {
        if ($this->registration_type !== 'team' || !$this->team_members_json) {
            return 0;
        }

        $teamMembers = $this->team_members_json;
        if (is_string($teamMembers)) {
            $teamMembers = json_decode($teamMembers, true);
        }

        return is_array($teamMembers) ? count($teamMembers) : 0;
    }

    /**
     * Check if registration is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if registration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if registration is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if registration is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if payment is verified.
     */
    public function isPaymentVerified(): bool
    {
        return $this->payment_status === 'verified';
    }

    /**
     * Check if payment is pending.
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if payment is rejected.
     */
    public function isPaymentRejected(): bool
    {
        return $this->payment_status === 'rejected';
    }

    /**
     * Check if registration requires payment verification.
     */
    public function needsPaymentVerification(): bool
    {
        return $this->payment_required && $this->payment_status === 'pending';
    }

    /**
     * Check if registration can be approved.
     */
    public function canBeApproved(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->payment_required && $this->payment_status !== 'verified') {
            return false;
        }

        return true;
    }

    /**
     * Get the participant name for display.
     */
    public function getParticipantName(): string
    {
        if ($this->registration_type === 'team') {
            return $this->team_name ?? 'Team Registration';
        }

        return $this->individual_name ?? $this->user->name;
    }

    /**
     * Get payment status badge class for UI.
     */
    public function getPaymentStatusBadgeClass(): string
    {
        return match($this->payment_status) {
            'verified' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get registration status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Scope to get approved registrations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get rejected registrations.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get registrations with verified payment.
     */
    public function scopePaymentVerified($query)
    {
        return $query->where('payment_status', 'verified');
    }

    /**
     * Scope to get registrations needing payment verification.
     */
    public function scopeNeedsPaymentVerification($query)
    {
        return $query->where('payment_required', true)
                    ->where('payment_status', 'pending');
    }

    /**
     * Scope to get team registrations.
     */
    public function scopeTeam($query)
    {
        return $query->where('registration_type', 'team');
    }

    /**
     * Scope to get individual registrations.
     */
    public function scopeIndividual($query)
    {
        return $query->where('registration_type', 'individual');
    }
}