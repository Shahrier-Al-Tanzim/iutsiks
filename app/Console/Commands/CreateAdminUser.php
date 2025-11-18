<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siks:create-admin {--all : Create all default admin users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin users for SIKS system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->createAllAdmins();
        } else {
            $this->createSingleAdmin();
        }
    }

    /**
     * Create all default admin users
     */
    private function createAllAdmins()
    {
        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@siks.edu',
                'password' => 'admin123',
                'role' => 'super_admin',
                'phone' => '+8801700000000',
                'student_id' => 'ADMIN001',
            ],
            [
                'name' => 'Event Admin',
                'email' => 'events@siks.edu',
                'password' => 'events123',
                'role' => 'event_admin',
                'phone' => '+8801700000001',
                'student_id' => 'ADMIN002',
            ],
            [
                'name' => 'Content Admin',
                'email' => 'content@siks.edu',
                'password' => 'content123',
                'role' => 'content_admin',
                'phone' => '+8801700000002',
                'student_id' => 'ADMIN003',
            ],
            [
                'name' => 'Prayer Admin',
                'email' => 'prayer@siks.edu',
                'password' => 'prayer123',
                'role' => 'content_admin',
                'phone' => '+8801700000003',
                'student_id' => 'ADMIN004',
            ],
        ];

        $this->info('Creating default admin users...');
        
        foreach ($admins as $adminData) {
            try {
                $user = User::updateOrCreate(
                    ['email' => $adminData['email']],
                    [
                        'name' => $adminData['name'],
                        'email' => $adminData['email'],
                        'password' => Hash::make($adminData['password']),
                        'role' => $adminData['role'],
                        'phone' => $adminData['phone'],
                        'student_id' => $adminData['student_id'],
                        'email_verified_at' => now(),
                    ]
                );
                
                $this->info("✓ Created {$adminData['role']}: {$adminData['email']} / {$adminData['password']}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to create {$adminData['email']}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('Admin users created successfully!');
        $this->info('You can now access admin areas with these credentials:');
        $this->table(
            ['Role', 'Email', 'Password', 'Access'],
            [
                ['Super Admin', 'admin@siks.edu', 'admin123', 'Full system access'],
                ['Event Admin', 'events@siks.edu', 'events123', 'Events & registrations'],
                ['Content Admin', 'content@siks.edu', 'content123', 'Blogs & prayer times'],
                ['Prayer Admin', 'prayer@siks.edu', 'prayer123', 'Prayer times management'],
            ]
        );
    }

    /**
     * Create a single admin user interactively
     */
    private function createSingleAdmin()
    {
        $this->info('Creating a new admin user...');

        $name = $this->ask('Enter admin name');
        $email = $this->ask('Enter admin email');
        $password = $this->secret('Enter admin password');
        $role = $this->choice('Select admin role', [
            'super_admin' => 'Super Admin (Full access)',
            'event_admin' => 'Event Admin (Events & registrations)',
            'content_admin' => 'Content Admin (Blogs & prayer times)',
        ], 'super_admin');

        $phone = $this->ask('Enter phone number (optional)', null);
        $studentId = $this->ask('Enter student ID (optional)', null);

        try {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => $role,
                    'phone' => $phone,
                    'student_id' => $studentId,
                    'email_verified_at' => now(),
                ]
            );

            $this->info("✓ Admin user created successfully!");
            $this->info("Email: {$email}");
            $this->info("Role: {$role}");
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to create admin user: " . $e->getMessage());
        }
    }
}
