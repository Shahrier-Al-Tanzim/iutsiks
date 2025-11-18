# Design Document

## Overview

The Islamic Society Website will be built as a Laravel-based web application that extends the existing blog and event functionality into a comprehensive society management platform. The system will follow Laravel's MVC architecture with additional service layers for complex business logic, particularly around registration and payment workflows.

The application runs in a **Laravel Sail Docker environment**, providing consistent development setup with containerized MySQL database and PHP/Laravel services. All database operations, migrations, and artisan commands should be executed through Sail (`./vendor/bin/sail` or `sail` if aliased).

The application will maintain the existing authentication system (Laravel Breeze) while extending it with role-based access control. The design emphasizes modularity, allowing features to be developed incrementally while maintaining system integrity.

## Architecture

### System Architecture

The application follows a layered architecture pattern:

```
┌─────────────────────────────────────────┐
│              Presentation Layer          │
│  (Blade Templates + Tailwind CSS)       │
├─────────────────────────────────────────┤
│              Controller Layer            │
│     (HTTP Controllers + API Routes)     │
├─────────────────────────────────────────┤
│              Service Layer               │
│  (Business Logic + Registration Logic)  │
├─────────────────────────────────────────┤
│              Repository Layer            │
│        (Eloquent Models + Relations)    │
├─────────────────────────────────────────┤
│              Data Layer                  │
│         (MySQL Database + Storage)      │
└─────────────────────────────────────────┘
```

### Key Architectural Decisions

1. **Docker-Based Development**: Use Laravel Sail for consistent containerized development environment
2. **Extend Existing Models**: Build upon current Blog, Event, User models rather than replacing them
3. **Service Layer Pattern**: Implement services for complex operations like registration and payment processing
4. **Policy-Based Authorization**: Use Laravel policies for role-based access control
5. **Component-Based UI**: Leverage Blade components for reusable UI elements
6. **Event-Driven Architecture**: Use Laravel events for notifications and audit logging

### Development Environment

The application uses **Laravel Sail** for development, which provides:
- Containerized MySQL 8.0 database
- PHP 8.2+ with Laravel 12.0
- Redis for caching and sessions
- Mailpit for email testing
- Node.js for asset compilation

All development commands should be prefixed with `./vendor/bin/sail` or use the `sail` alias:
```bash
# Database migrations
sail artisan migrate

# Run tests
sail artisan test

# Access container shell
sail shell

# View logs
sail logs
```

## Components and Interfaces

### Core Models and Relationships

```php
// Extended User Model
User {
    - id, name, email, password
    - role (enum: member, event_admin, content_admin, super_admin)
    - phone, student_id (new fields)
    
    // Relationships
    - hasMany(Blog) as authoredBlogs
    - hasMany(Event) as authoredEvents  
    - hasMany(Registration) as registrations
    - hasMany(TeamMembership) as teamMemberships
}

// New Fest Model (Parent Events)
Fest {
    - id, title, description, start_date, end_date
    - banner_image, status, created_by
    
    // Relationships
    - hasMany(Event) as events
    - belongsTo(User) as creator
    - hasMany(GalleryImage) as gallery
}

// Extended Event Model
Event {
    - id, fest_id, title, description, event_date, event_time
    - type (enum: quiz, lecture, donation, competition, workshop)
    - registration_type (enum: individual, team, both, on_spot)
    - location, max_participants, fee_amount
    - registration_deadline, status, author_id, image
    
    // Relationships
    - belongsTo(Fest) as fest
    - belongsTo(User) as author
    - hasMany(Registration) as registrations
    - hasMany(GalleryImage) as gallery
}

// New Registration Model
Registration {
    - id, event_id, user_id (team leader), registration_type
    - team_name, team_members_json, individual_name
    - payment_required, payment_amount, payment_status
    - payment_method, transaction_id, payment_date
    - admin_notes, status, registered_at
    
    // Relationships
    - belongsTo(Event) as event
    - belongsTo(User) as user
}

// New PrayerTime Model
PrayerTime {
    - id, date, fajr, dhuhr, asr, maghrib, isha
    - location, updated_by, notes
    
    // Relationships
    - belongsTo(User) as updatedBy
}

// New GalleryImage Model
GalleryImage {
    - id, imageable_type, imageable_id (polymorphic)
    - image_path, caption, alt_text, uploaded_by
    
    // Relationships
    - morphTo() as imageable (Event, Fest, or null for general)
    - belongsTo(User) as uploader
}
```

### Service Classes

```php
// RegistrationService
class RegistrationService {
    + registerIndividual(Event $event, User $user, array $data): Registration
    + registerTeam(Event $event, User $leader, array $teamData): Registration
    + processPayment(Registration $registration, array $paymentData): bool
    + approveRegistration(Registration $registration, User $admin): bool
    + rejectRegistration(Registration $registration, User $admin, string $reason): bool
    + checkAvailability(Event $event): array
    + exportRegistrations(Event $event): string
}

// PrayerTimeService  
class PrayerTimeService {
    + getTodaysPrayerTimes(): ?PrayerTime
    + updatePrayerTimes(array $times, User $admin): PrayerTime
    + getUpcomingPrayer(): ?array
    + getPrayerTimesForDate(Carbon $date): ?PrayerTime
}

// GalleryService
class GalleryService {
    + uploadImages(array $files, $imageable, User $uploader): Collection
    + getEventGallery(Event $event): Collection
    + getFestGallery(Fest $fest): Collection
    + deleteImage(GalleryImage $image, User $user): bool
}
```

### Controller Structure

```php
// Admin Controllers (Protected by role middleware)
AdminController {
    + dashboard(): View
    + userManagement(): View
    + systemSettings(): View
}

FestController {
    + index(), create(), store(), show(), edit(), update(), destroy()
    + addEvent(Fest $fest): View
}

RegistrationController {
    + show(Event $event): View
    + registerIndividual(Event $event): RedirectResponse  
    + registerTeam(Event $event): RedirectResponse
    + myRegistrations(): View
}

AdminRegistrationController {
    + index(): View (all registrations)
    + show(Registration $registration): View
    + approve(Registration $registration): RedirectResponse
    + reject(Registration $registration): RedirectResponse
    + export(Event $event): Response
}

PrayerTimeController {
    + index(): View (public display)
    + edit(): View (admin only)
    + update(): RedirectResponse
}

GalleryController {
    + index(): View
    + show($type, $id): View (event/fest specific)
    + upload(): RedirectResponse (admin)
    + destroy(GalleryImage $image): RedirectResponse
}
```

## Data Models

### Database Schema Extensions

```sql
-- Extend users table
ALTER TABLE users ADD COLUMN role ENUM('member', 'event_admin', 'content_admin', 'super_admin') DEFAULT 'member';
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN student_id VARCHAR(50) NULL;

-- Create fests table
CREATE TABLE fests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    banner_image VARCHAR(255) NULL,
    status ENUM('draft', 'published', 'completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Extend events table
ALTER TABLE events ADD COLUMN fest_id BIGINT UNSIGNED NULL;
ALTER TABLE events ADD COLUMN type ENUM('quiz', 'lecture', 'donation', 'competition', 'workshop') DEFAULT 'lecture';
ALTER TABLE events ADD COLUMN registration_type ENUM('individual', 'team', 'both', 'on_spot') DEFAULT 'individual';
ALTER TABLE events ADD COLUMN location VARCHAR(255) NULL;
ALTER TABLE events ADD COLUMN max_participants INT NULL;
ALTER TABLE events ADD COLUMN fee_amount DECIMAL(8,2) DEFAULT 0;
ALTER TABLE events ADD COLUMN registration_deadline DATETIME NULL;
ALTER TABLE events ADD COLUMN status ENUM('draft', 'published', 'completed') DEFAULT 'draft';
ALTER TABLE events ADD FOREIGN KEY (fest_id) REFERENCES fests(id) ON DELETE CASCADE;

-- Create registrations table
CREATE TABLE registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    registration_type ENUM('individual', 'team') NOT NULL,
    team_name VARCHAR(255) NULL,
    team_members_json JSON NULL,
    individual_name VARCHAR(255) NULL,
    payment_required BOOLEAN DEFAULT FALSE,
    payment_amount DECIMAL(8,2) DEFAULT 0,
    payment_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    payment_method VARCHAR(100) NULL,
    transaction_id VARCHAR(255) NULL,
    payment_date DATE NULL,
    admin_notes TEXT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_event (event_id, user_id)
);

-- Create prayer_times table
CREATE TABLE prayer_times (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    fajr TIME NOT NULL,
    dhuhr TIME NOT NULL,
    asr TIME NOT NULL,
    maghrib TIME NOT NULL,
    isha TIME NOT NULL,
    location VARCHAR(255) DEFAULT 'IOT Masjid',
    updated_by BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Create gallery_images table
CREATE TABLE gallery_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    imageable_type VARCHAR(255) NULL,
    imageable_id BIGINT UNSIGNED NULL,
    image_path VARCHAR(255) NOT NULL,
    caption TEXT NULL,
    alt_text VARCHAR(255) NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX imageable_index (imageable_type, imageable_id)
);
```

## Error Handling

### Validation Rules

```php
// Event Registration Validation
'registration_type' => 'required|in:individual,team',
'team_name' => 'required_if:registration_type,team|max:100',
'team_members' => 'required_if:registration_type,team|array|min:2|max:10',
'team_members.*' => 'exists:users,id',
'payment_method' => 'required_if:payment_required,true|in:bkash,nagad,bank_transfer',
'transaction_id' => 'required_if:payment_required,true|string|max:100',
'payment_date' => 'required_if:payment_required,true|date|before_or_equal:today',

// Fest Creation Validation
'title' => 'required|string|max:255',
'description' => 'required|string|min:50',
'start_date' => 'required|date|after_or_equal:today',
'end_date' => 'required|date|after_or_equal:start_date',
'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

// Prayer Times Validation
'date' => 'required|date',
'fajr' => 'required|date_format:H:i',
'dhuhr' => 'required|date_format:H:i|after:fajr',
'asr' => 'required|date_format:H:i|after:dhuhr',
'maghrib' => 'required|date_format:H:i|after:asr',
'isha' => 'required|date_format:H:i|after:maghrib'
```

### Exception Handling Strategy

```php
// Custom Exceptions
class RegistrationException extends Exception {}
class PaymentVerificationException extends Exception {}
class EventCapacityException extends Exception {}

// Global Exception Handler Extensions
public function render($request, Exception $exception)
{
    if ($exception instanceof RegistrationException) {
        return redirect()->back()
            ->withErrors(['registration' => $exception->getMessage()])
            ->withInput();
    }
    
    if ($exception instanceof EventCapacityException) {
        return redirect()->route('events.show', $request->event)
            ->with('error', 'Event is full. Registration closed.');
    }
    
    return parent::render($request, $exception);
}
```

## Testing Strategy

### Unit Testing Focus Areas

1. **Registration Logic**: Test individual/team registration flows, capacity limits, duplicate prevention
2. **Payment Processing**: Test payment validation, approval/rejection workflows
3. **Role Authorization**: Test access control for different user roles
4. **Prayer Time Management**: Test time validation and display logic
5. **Gallery Operations**: Test image upload, association, and deletion

### Feature Testing Scenarios

1. **Complete Registration Flow**: User registration → Event registration → Payment → Admin approval
2. **Team Formation**: Team creation → Member invitation → Registration submission
3. **Admin Workflows**: Event creation → Registration management → Payment verification
4. **Public Access**: Anonymous browsing → Registration requirement → Login flow

### Test Data Strategy

```php
// Factory Definitions
FestFactory::definition() => [
    'title' => fake()->sentence(3),
    'description' => fake()->paragraphs(3, true),
    'start_date' => fake()->dateTimeBetween('now', '+1 month'),
    'end_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
    'created_by' => User::factory()
];

EventFactory::definition() => [
    'fest_id' => Fest::factory(),
    'title' => fake()->sentence(4),
    'type' => fake()->randomElement(['quiz', 'lecture', 'competition']),
    'registration_type' => fake()->randomElement(['individual', 'team', 'both']),
    'max_participants' => fake()->numberBetween(10, 100),
    'fee_amount' => fake()->randomElement([0, 50, 100, 200])
];
```

This design provides a solid foundation for implementing the Islamic Society Website while building upon the existing Laravel application structure. The modular approach allows for incremental development and testing of each component.