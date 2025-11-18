<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;

class VerifyAdminAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siks:verify-admin {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify admin access and permissions for SIKS system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if (!$email) {
            $this->listAllAdmins();
        } else {
            $this->verifyUserAccess($email);
        }
    }

    /**
     * List all admin users
     */
    private function listAllAdmins()
    {
        $this->info('SIKS Admin Users:');
        $this->info('================');

        $admins = User::whereIn('role', ['super_admin', 'event_admin', 'content_admin'])->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found!');
            $this->info('Run: php artisan siks:create-admin --all');
            return;
        }

        $tableData = [];
        foreach ($admins as $admin) {
            $tableData[] = [
                $admin->name,
                $admin->email,
                $admin->role,
                $admin->created_at->format('Y-m-d H:i'),
                $admin->email_verified_at ? '✓' : '✗'
            ];
        }

        $this->table(
            ['Name', 'Email', 'Role', 'Created', 'Verified'],
            $tableData
        );

        $this->newLine();
        $this->info('Admin URLs:');
        $this->info('- Super Admin Dashboard: /admin/dashboard');
        $this->info('- User Management: /admin/users');
        $this->info('- Registration Management: /admin/registrations');
        $this->info('- Prayer Times Management: /admin/prayer-times');
        $this->info('- System Analytics: /admin/analytics');
    }

    /**
     * Verify specific user access
     */
    private function verifyUserAccess(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return;
        }

        $this->info("Verifying access for: {$user->name} ({$user->email})");
        $this->info('=================================================');

        // Basic info
        $this->table(
            ['Property', 'Value'],
            [
                ['Role', $user->role],
                ['Email Verified', $user->email_verified_at ? '✓ Yes' : '✗ No'],
                ['Created', $user->created_at->format('Y-m-d H:i:s')],
                ['Last Updated', $user->updated_at->format('Y-m-d H:i:s')],
            ]
        );

        // Role checks
        $this->newLine();
        $this->info('Role Permissions:');
        $roleChecks = [
            ['Is Admin', $user->isAdmin() ? '✓' : '✗'],
            ['Is Super Admin', $user->isSuperAdmin() ? '✓' : '✗'],
            ['Is Event Admin', $user->isEventAdmin() ? '✓' : '✗'],
            ['Is Content Admin', $user->isContentAdmin() ? '✓' : '✗'],
        ];
        $this->table(['Permission', 'Status'], $roleChecks);

        // Capability checks
        $this->newLine();
        $this->info('Capabilities:');
        $capabilities = [
            ['Manage Users', $user->canManageUsers() ? '✓' : '✗'],
            ['Manage Events', $user->canManageEvents() ? '✓' : '✗'],
            ['Manage Content', $user->canManageContent() ? '✓' : '✗'],
            ['Manage Prayer Times', $user->canManagePrayerTimes() ? '✓' : '✗'],
            ['Manage Gallery', $user->canManageGallery() ? '✓' : '✗'],
            ['Manage Registrations', $user->canManageRegistrations() ? '✓' : '✗'],
        ];
        $this->table(['Capability', 'Status'], $capabilities);

        // Access URLs
        $this->newLine();
        $this->info('Accessible Admin URLs:');
        $urls = [];
        
        if ($user->isSuperAdmin()) {
            $urls = [
                '/admin/dashboard - Super Admin Dashboard',
                '/admin/users - User Management',
                '/admin/analytics - System Analytics',
                '/admin/activity-logs - Activity Logs',
                '/admin/settings - System Settings',
            ];
        }
        
        if ($user->canManageEvents()) {
            $urls[] = '/admin/registrations - Registration Management';
        }
        
        if ($user->canManagePrayerTimes()) {
            $urls[] = '/admin/prayer-times - Prayer Times Management';
        }

        if (empty($urls)) {
            $this->warn('No admin URLs accessible for this user.');
        } else {
            foreach ($urls as $url) {
                $this->info("- {$url}");
            }
        }

        // Recommendations
        $this->newLine();
        if (!$user->isAdmin()) {
            $this->warn('This user does not have admin privileges.');
            $this->info('To grant admin access, run:');
            $this->info("php artisan tinker --execute=\"User::where('email', '{$email}')->first()->update(['role' => 'super_admin']);\"");
        } else {
            $this->info('✓ User has admin access and can access admin areas.');
        }
    }
}
