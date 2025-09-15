<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'description' => 'Manages employee relations and recruitment'],
            ['name' => 'Finance', 'description' => 'Handles accounting and financial operations'],
            ['name' => 'Sales', 'description' => 'Manages customer relationships and sales'],
            ['name' => 'Marketing', 'description' => 'Handles promotional activities and branding'],
            ['name' => 'IT', 'description' => 'Manages technology infrastructure and support'],
            ['name' => 'Operations', 'description' => 'Handles day-to-day business operations'],
            ['name' => 'Customer Service', 'description' => 'Provides customer support and assistance'],
            ['name' => 'Procurement', 'description' => 'Manages supplier relationships and purchasing'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
