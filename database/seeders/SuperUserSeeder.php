<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin role exists
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run PermissionSeeder first.');
            return;
        }

        // Check if super user already exists
        $existingUser = User::where('email', 'admin@erp.com')->first();
        
        if ($existingUser) {
            $this->command->info('Super user already exists. Updating...');
            
            // Update existing user
            $existingUser->update([
                'name' => 'Super Admin',
                'email' => 'admin@erp.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            
            // Assign admin role if not already assigned
            if (!$existingUser->hasRole('admin')) {
                $existingUser->assignRole('admin');
            }
            
            $this->command->info('Super user updated successfully!');
        } else {
            // Create new super user
            $superUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@erp.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            
            // Assign admin role
            $superUser->assignRole('admin');
            
            $this->command->info('Super user created successfully!');
        }
        
        // Display login credentials
        $this->command->line('');
        $this->command->line('==========================================');
        $this->command->line('SUPER USER CREDENTIALS');
        $this->command->line('==========================================');
        $this->command->line('Email: admin@erp.com');
        $this->command->line('Password: admin123');
        $this->command->line('Role: admin (All Permissions)');
        $this->command->line('==========================================');
        $this->command->line('');
        
        // Display permissions
        $permissions = $adminRole->permissions->pluck('name')->toArray();
        $this->command->info('Admin Role has ' . count($permissions) . ' permissions:');
        
        // Group permissions by module
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $module = explode('.', $permission)[0];
            if (!isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = [];
            }
            $groupedPermissions[$module][] = $permission;
        }
        
        foreach ($groupedPermissions as $module => $modulePermissions) {
            $this->command->line("  {$module}: " . count($modulePermissions) . " permissions");
        }
    }
}


