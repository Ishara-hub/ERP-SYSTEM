<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each ERP module
        $permissions = [
            // User Management
            'users.view' => 'View users',
            'users.create' => 'Create users',
            'users.edit' => 'Edit users',
            'users.delete' => 'Delete users',
            
            // Role Management
            'roles.view' => 'View roles',
            'roles.create' => 'Create roles',
            'roles.edit' => 'Edit roles',
            'roles.delete' => 'Delete roles',
            
            // Employee Management
            'employees.view' => 'View employees',
            'employees.create' => 'Create employees',
            'employees.edit' => 'Edit employees',
            'employees.delete' => 'Delete employees',
            
            // Attendance Management
            'attendance.view' => 'View attendance',
            'attendance.create' => 'Create attendance',
            'attendance.edit' => 'Edit attendance',
            'attendance.delete' => 'Delete attendance',
            
            // Leave Management
            'leaves.view' => 'View leaves',
            'leaves.create' => 'Create leaves',
            'leaves.edit' => 'Edit leaves',
            'leaves.delete' => 'Delete leaves',
            'leaves.approve' => 'Approve leaves',
            
            // Payroll Management
            'payrolls.view' => 'View payrolls',
            'payrolls.create' => 'Create payrolls',
            'payrolls.edit' => 'Edit payrolls',
            'payrolls.delete' => 'Delete payrolls',
            
            // Customer Management
            'customers.view' => 'View customers',
            'customers.create' => 'Create customers',
            'customers.edit' => 'Edit customers',
            'customers.delete' => 'Delete customers',
            
            // Product Management
            'products.view' => 'View products',
            'products.create' => 'Create products',
            'products.edit' => 'Edit products',
            'products.delete' => 'Delete products',
            
            // Inventory Management
            'inventory.view' => 'View inventory',
            'inventory.create' => 'Create inventory',
            'inventory.edit' => 'Edit inventory',
            'inventory.delete' => 'Delete inventory',
            
            // Purchase Orders
            'purchase_orders.view' => 'View purchase orders',
            'purchase_orders.create' => 'Create purchase orders',
            'purchase_orders.edit' => 'Edit purchase orders',
            'purchase_orders.delete' => 'Delete purchase orders',
            'purchase_orders.approve' => 'Approve purchase orders',
            
            // Sales Orders
            'sales_orders.view' => 'View sales orders',
            'sales_orders.create' => 'Create sales orders',
            'sales_orders.edit' => 'Edit sales orders',
            'sales_orders.delete' => 'Delete sales orders',
            'sales_orders.approve' => 'Approve sales orders',
            
            // Invoice Management
            'invoices.view' => 'View invoices',
            'invoices.create' => 'Create invoices',
            'invoices.edit' => 'Edit invoices',
            'invoices.delete' => 'Delete invoices',
            'invoices.approve' => 'Approve invoices',
            
            // Financial Management
            'accounts.view' => 'View accounts',
            'accounts.create' => 'Create accounts',
            'accounts.edit' => 'Edit accounts',
            'accounts.delete' => 'Delete accounts',
            
            'transactions.view' => 'View transactions',
            'transactions.create' => 'Create transactions',
            'transactions.edit' => 'Edit transactions',
            'transactions.delete' => 'Delete transactions',
            
            // Reports
            'reports.view' => 'View reports',
            'reports.export' => 'Export reports',
            
            // Settings
            'settings.view' => 'View settings',
            'settings.edit' => 'Edit settings',
        ];

        foreach ($permissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        $employeeRole = Role::create(['name' => 'employee', 'guard_name' => 'web']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        
        $managerRole->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'employees.view', 'employees.create', 'employees.edit',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'leaves.view', 'leaves.create', 'leaves.edit', 'leaves.approve',
            'payrolls.view', 'payrolls.create', 'payrolls.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'products.view', 'products.create', 'products.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.approve',
            'sales_orders.view', 'sales_orders.create', 'sales_orders.edit', 'sales_orders.approve',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.approve',
            'accounts.view', 'accounts.create', 'accounts.edit',
            'transactions.view', 'transactions.create', 'transactions.edit',
            'reports.view', 'reports.export',
            'settings.view'
        ]);
        
        $employeeRole->givePermissionTo([
            'attendance.view', 'attendance.create',
            'leaves.view', 'leaves.create',
            'customers.view',
            'products.view',
            'inventory.view',
            'sales_orders.view', 'sales_orders.create',
            'invoices.view', 'invoices.create'
        ]);
        
        $userRole->givePermissionTo([
            'customers.view',
            'products.view',
            'invoices.view'
        ]);
    }
}
