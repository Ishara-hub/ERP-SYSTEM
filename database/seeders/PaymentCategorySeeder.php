<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentCategory;

class PaymentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = PaymentCategory::getDefaultCategories();
        
        foreach ($categories as $categoryData) {
            PaymentCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );
        }
    }
}