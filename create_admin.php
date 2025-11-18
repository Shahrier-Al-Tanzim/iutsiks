<?php
/**
 * Simple script to create admin users for SIKS
 * Run this script when database is available: php create_admin.php
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Creating SIKS Admin Users...\n\n";

    // Create Super Admin
    $superAdmin = User::updateOrCreate(
        ['email' => 'admin@siks.edu'],
        [
            'name' => 'Super Admin',
            'email' => 'admin@siks.edu',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'phone' => '+8801700000000',
            'student_id' => 'ADMIN001',
            'email_verified_at' => now(),
        ]
    );
    echo "✓ Super Admin created: admin@siks.edu / admin123\n";

    // Create Event Admin
    $eventAdmin = User::updateOrCreate(
        ['email' => 'events@siks.edu'],
        [
            'name' => 'Event Admin',
            'email' => 'events@siks.edu',
            'password' => Hash::make('events123'),
            'role' => 'event_admin',
            'phone' => '+8801700000001',
            'student_id' => 'ADMIN002',
            'email_verified_at' => now(),
        ]
    );
    echo "✓ Event Admin created: events@siks.edu / events123\n";

    // Create Content Admin
    $contentAdmin = User::updateOrCreate(
        ['email' => 'content@siks.edu'],
        [
            'name' => 'Content Admin',
            'email' => 'content@siks.edu',
            'password' => Hash::make('content123'),
            'role' => 'content_admin',
            'phone' => '+8801700000002',
            'student_id' => 'ADMIN003',
            'email_verified_at' => now(),
        ]
    );
    echo "✓ Content Admin created: content@siks.edu / content123\n";

    // Create Prayer Admin
    $prayerAdmin = User::updateOrCreate(
        ['email' => 'prayer@siks.edu'],
        [
            'name' => 'Prayer Admin',
            'email' => 'prayer@siks.edu',
            'password' => Hash::make('prayer123'),
            'role' => 'content_admin',
            'phone' => '+8801700000003',
            'student_id' => 'ADMIN004',
            'email_verified_at' => now(),
        ]
    );
    echo "✓ Prayer Admin created: prayer@siks.edu / prayer123\n";

    echo "\n🎉 All admin users created successfully!\n\n";
    
    echo "Admin Access Information:\n";
    echo "========================\n";
    echo "Super Admin Dashboard: /admin/dashboard\n";
    echo "  - Email: admin@siks.edu\n";
    echo "  - Password: admin123\n";
    echo "  - Access: Full system control\n\n";
    
    echo "Event Management: /admin/registrations\n";
    echo "  - Email: events@siks.edu\n";
    echo "  - Password: events123\n";
    echo "  - Access: Events and registrations\n\n";
    
    echo "Content Management: /admin/prayer-times\n";
    echo "  - Email: content@siks.edu\n";
    echo "  - Password: content123\n";
    echo "  - Access: Blogs and prayer times\n\n";
    
    echo "Prayer Management: /admin/prayer-times\n";
    echo "  - Email: prayer@siks.edu\n";
    echo "  - Password: prayer123\n";
    echo "  - Access: Prayer times management\n\n";
    
    echo "⚠️  IMPORTANT: Change these default passwords in production!\n";

} catch (Exception $e) {
    echo "❌ Error creating admin users: " . $e->getMessage() . "\n";
    echo "Make sure your database is configured and accessible.\n";
}