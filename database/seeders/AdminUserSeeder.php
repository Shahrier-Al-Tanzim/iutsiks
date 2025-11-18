<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::updateOrCreate(
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

        // Create Event Admin
        User::updateOrCreate(
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

        // Create Content Admin
        User::updateOrCreate(
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

        // Create Prayer Admin (content admin with prayer management focus)
        User::updateOrCreate(
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

        $this->command->info('Admin users created successfully!');
        $this->command->info('Super Admin: admin@siks.edu / admin123');
        $this->command->info('Event Admin: events@siks.edu / events123');
        $this->command->info('Content Admin: content@siks.edu / content123');
        $this->command->info('Prayer Admin: prayer@siks.edu / prayer123');
    }
}