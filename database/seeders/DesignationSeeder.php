<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            ['name' => 'CEO', 'description' => 'Chief Executive Officer'],
            ['name' => 'CTO', 'description' => 'Chief Technology Officer'],
            ['name' => 'CFO', 'description' => 'Chief Financial Officer'],
            ['name' => 'Manager', 'description' => 'Department Manager'],
            ['name' => 'Senior Developer', 'description' => 'Senior Software Developer'],
            ['name' => 'Developer', 'description' => 'Software Developer'],
            ['name' => 'Accountant', 'description' => 'Financial Accountant'],
            ['name' => 'Sales Executive', 'description' => 'Sales Representative'],
            ['name' => 'HR Specialist', 'description' => 'Human Resources Specialist'],
            ['name' => 'Customer Support', 'description' => 'Customer Support Representative'],
            ['name' => 'Intern', 'description' => 'Intern/Trainee'],
        ];

        foreach ($designations as $designation) {
            Designation::create($designation);
        }
    }
}
