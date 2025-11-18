<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin users first
        $this->call(AdminUserSeeder::class);

        // Create test user if it doesn't exist
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'member',
        ]);
    }
}
