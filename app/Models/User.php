<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'student_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the blogs authored by this user.
     */
    public function authoredBlogs()
    {
        return $this->hasMany(Blog::class, 'author_id');
    }

    /**
     * Get the events authored by this user.
     */
    public function authoredEvents()
    {
        return $this->hasMany(Event::class, 'author_id');
    }

    /**
     * Get the fests created by this user.
     */
    public function createdFests()
    {
        return $this->hasMany(Fest::class, 'created_by');
    }

    /**
     * Get the registrations for this user.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the prayer times updated by this user.
     */
    public function updatedPrayerTimes()
    {
        return $this->hasMany(PrayerTime::class, 'updated_by');
    }

    /**
     * Get the gallery images uploaded by this user.
     */
    public function uploadedImages()
    {
        return $this->hasMany(GalleryImage::class, 'uploaded_by');
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is content admin.
     */
    public function isContentAdmin(): bool
    {
        return $this->role === 'content_admin';
    }

    /**
     * Check if user is event admin.
     */
    public function isEventAdmin(): bool
    {
        return $this->role === 'event_admin';
    }

    /**
     * Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'content_admin', 'event_admin']);
    }

    /**
     * Check if user can manage content (blogs, prayer times).
     */
    public function canManageContent(): bool
    {
        return in_array($this->role, ['super_admin', 'content_admin']);
    }

    /**
     * Check if user can manage events and registrations.
     */
    public function canManageEvents(): bool
    {
        return in_array($this->role, ['super_admin', 'event_admin']);
    }

    /**
     * Check if user can manage fests.
     */
    public function canManageFests(): bool
    {
        return in_array($this->role, ['super_admin', 'event_admin']);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }

        return in_array($this->role, $roles);
    }

    /**
     * Check if user can manage prayer times.
     */
    public function canManagePrayerTimes(): bool
    {
        return in_array($this->role, ['super_admin', 'content_admin']);
    }

    /**
     * Check if user can manage gallery.
     */
    public function canManageGallery(): bool
    {
        return in_array($this->role, ['super_admin', 'content_admin', 'event_admin']);
    }

    /**
     * Check if user can manage registrations.
     */
    public function canManageRegistrations(): bool
    {
        return in_array($this->role, ['super_admin', 'event_admin']);
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user can view admin dashboard.
     */
    public function canViewAdminDashboard(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can approve registrations.
     */
    public function canApproveRegistrations(): bool
    {
        return in_array($this->role, ['super_admin', 'event_admin']);
    }

    /**
     * Check if user can verify payments.
     */
    public function canVerifyPayments(): bool
    {
        return in_array($this->role, ['super_admin', 'event_admin']);
    }

    /**
     * Get user's last login IP address.
     */
    public function getLastLoginIp(): ?string
    {
        return $this->last_login_ip ?? null;
    }

    /**
     * Get user's last login time.
     */
    public function getLastLoginAt(): ?\Carbon\Carbon
    {
        return $this->last_login_at ? \Carbon\Carbon::parse($this->last_login_at) : null;
    }

    /**
     * Check if user account is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Lock user account.
     */
    public function lock(?string $reason = null): void
    {
        $this->update([
            'locked_at' => now(),
            'lock_reason' => $reason
        ]);
    }

    /**
     * Unlock user account.
     */
    public function unlock(): void
    {
        $this->update([
            'locked_at' => null,
            'lock_reason' => null
        ]);
    }

    /**
     * Record login attempt.
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
            'login_count' => $this->login_count + 1
        ]);
    }

    /**
     * Check if user has permission for specific action.
     */
    public function hasPermission(string $permission): bool
    {
        return match($permission) {
            'manage_users' => $this->canManageUsers(),
            'manage_events' => $this->canManageEvents(),
            'manage_fests' => $this->canManageFests(),
            'manage_content' => $this->canManageContent(),
            'manage_prayer_times' => $this->canManagePrayerTimes(),
            'manage_gallery' => $this->canManageGallery(),
            'approve_registrations' => $this->canApproveRegistrations(),
            'verify_payments' => $this->canVerifyPayments(),
            'view_admin_dashboard' => $this->canViewAdminDashboard(),
            default => false
        };
    }
}
