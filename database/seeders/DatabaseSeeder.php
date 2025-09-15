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
        // Create departments and designations first
        $this->call([
            DepartmentSeeder::class,
            DesignationSeeder::class,
            ERPSampleDataSeeder::class,
            PermissionSeeder::class,
            ChartOfAccountsSeeder::class,
            ItemsSeeder::class,
        ]);

        // Create a test user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@erp.com',
        ]);
    }
}
