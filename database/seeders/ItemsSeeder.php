<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Account;
use App\Models\Supplier;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get accounts for associations
        $cogsAccount = Account::where('account_name', 'like', '%Cost of Goods%')->first();
        $incomeAccount = Account::where('account_name', 'like', '%Sales%')->first();
        $assetAccount = Account::where('account_name', 'like', '%Inventory%')->first();
        
        // Get a supplier for preferred vendor
        $supplier = Supplier::first();

        // Service Items
        $services = [
            [
                'item_name' => 'Consulting Services',
                'item_number' => 'SVC-001',
                'item_type' => 'Service',
                'purchase_description' => 'Professional consulting services',
                'cost' => 0,
                'sales_description' => 'Expert consulting and advisory services',
                'sales_price' => 150.00,
                'is_used_in_assemblies' => false,
                'is_performed_by_subcontractor' => false,
                'income_account_id' => $incomeAccount?->id,
                'is_active' => true,
                'is_inactive' => false,
            ],
            [
                'item_name' => 'Technical Support',
                'item_number' => 'SVC-002',
                'item_type' => 'Service',
                'purchase_description' => 'Technical support and maintenance',
                'cost' => 0,
                'sales_description' => '24/7 technical support services',
                'sales_price' => 75.00,
                'is_used_in_assemblies' => true,
                'is_performed_by_subcontractor' => true,
                'income_account_id' => $incomeAccount?->id,
                'is_active' => true,
                'is_inactive' => false,
            ],
            [
                'item_name' => 'Installation Service',
                'item_number' => 'SVC-003',
                'item_type' => 'Service',
                'purchase_description' => 'Product installation and setup',
                'cost' => 0,
                'sales_description' => 'Professional installation and configuration',
                'sales_price' => 200.00,
                'is_used_in_assemblies' => false,
                'is_performed_by_subcontractor' => false,
                'income_account_id' => $incomeAccount?->id,
                'is_active' => true,
                'is_inactive' => false,
            ],
        ];

        // Inventory Parts
        $inventoryParts = [
            [
                'item_name' => 'Laptop Computer',
                'item_number' => 'LAP-001',
                'item_type' => 'Inventory Part',
                'manufacturer_part_number' => 'DLL-15-3000',
                'unit_of_measure' => 'Each',
                'enable_unit_of_measure' => true,
                'purchase_description' => 'Dell Laptop 15-inch, 8GB RAM, 256GB SSD',
                'cost' => 800.00,
                'cogs_account_id' => $cogsAccount?->id,
                'preferred_vendor_id' => $supplier?->id,
                'sales_description' => 'High-performance laptop for business use',
                'sales_price' => 1200.00,
                'income_account_id' => $incomeAccount?->id,
                'asset_account_id' => $assetAccount?->id,
                'reorder_point' => 5.00,
                'max_quantity' => 50.00,
                'on_hand' => 12.00,
                'total_value' => 9600.00,
                'as_of_date' => now()->toDateString(),
                'is_active' => true,
                'is_inactive' => false,
            ],
            [
                'item_name' => 'Wireless Mouse',
                'item_number' => 'MOU-001',
                'item_type' => 'Inventory Part',
                'manufacturer_part_number' => 'LOG-MX-3',
                'unit_of_measure' => 'Each',
                'enable_unit_of_measure' => true,
                'purchase_description' => 'Logitech MX Master 3 Wireless Mouse',
                'cost' => 45.00,
                'cogs_account_id' => $cogsAccount?->id,
                'preferred_vendor_id' => $supplier?->id,
                'sales_description' => 'Ergonomic wireless mouse with precision tracking',
                'sales_price' => 75.00,
                'income_account_id' => $incomeAccount?->id,
                'asset_account_id' => $assetAccount?->id,
                'reorder_point' => 20.00,
                'max_quantity' => 100.00,
                'on_hand' => 35.00,
                'total_value' => 1575.00,
                'as_of_date' => now()->toDateString(),
                'is_active' => true,
                'is_inactive' => false,
            ],
            [
                'item_name' => 'USB-C Cable',
                'item_number' => 'CAB-001',
                'item_type' => 'Inventory Part',
                'manufacturer_part_number' => 'USB-C-6FT',
                'unit_of_measure' => 'Each',
                'enable_unit_of_measure' => true,
                'purchase_description' => '6-foot USB-C to USB-C cable',
                'cost' => 8.50,
                'cogs_account_id' => $cogsAccount?->id,
                'preferred_vendor_id' => $supplier?->id,
                'sales_description' => 'High-speed USB-C cable for data transfer and charging',
                'sales_price' => 15.00,
                'income_account_id' => $incomeAccount?->id,
                'asset_account_id' => $assetAccount?->id,
                'reorder_point' => 50.00,
                'max_quantity' => 200.00,
                'on_hand' => 85.00,
                'total_value' => 722.50,
                'as_of_date' => now()->toDateString(),
                'is_active' => true,
                'is_inactive' => false,
            ],
        ];

        // Non-Inventory Parts
        $nonInventoryParts = [
            [
                'item_name' => 'Shipping Fee',
                'item_number' => 'SHIP-001',
                'item_type' => 'Other Charge',
                'purchase_description' => 'Shipping and handling charges',
                'cost' => 0,
                'sales_description' => 'Standard shipping and handling',
                'sales_price' => 12.99,
                'income_account_id' => $incomeAccount?->id,
                'is_active' => true,
                'is_inactive' => false,
            ],
            [
                'item_name' => 'Volume Discount',
                'item_number' => 'DISC-001',
                'item_type' => 'Discount',
                'purchase_description' => 'Volume purchase discount',
                'cost' => 0,
                'sales_description' => '10% discount for orders over $1000',
                'sales_price' => -0.10, // Negative for discount
                'income_account_id' => $incomeAccount?->id,
                'is_active' => true,
                'is_inactive' => false,
            ],
        ];

        // Create all items
        $allItems = array_merge($services, $inventoryParts, $nonInventoryParts);
        
        foreach ($allItems as $itemData) {
            // Calculate markup and margin
            $itemData['markup_percentage'] = $itemData['cost'] > 0 
                ? (($itemData['sales_price'] - $itemData['cost']) / $itemData['cost']) * 100 
                : 0;
            
            $itemData['margin_percentage'] = $itemData['sales_price'] > 0 
                ? (($itemData['sales_price'] - $itemData['cost']) / $itemData['sales_price']) * 100 
                : 0;

            Item::create($itemData);
        }

        // Create some assembly items
        $laptop = Item::where('item_number', 'LAP-001')->first();
        $mouse = Item::where('item_number', 'MOU-001')->first();
        $cable = Item::where('item_number', 'CAB-001')->first();

        if ($laptop && $mouse && $cable) {
            // Create a laptop bundle assembly
            $laptopBundle = Item::create([
                'item_name' => 'Laptop Bundle',
                'item_number' => 'BUNDLE-001',
                'item_type' => 'Inventory Assembly',
                'purchase_description' => 'Complete laptop setup bundle',
                'cost' => 0, // Will be calculated from components
                'cogs_account_id' => $cogsAccount?->id,
                'sales_description' => 'Complete laptop setup with mouse and cable',
                'sales_price' => 1400.00,
                'income_account_id' => $incomeAccount?->id,
                'asset_account_id' => $assetAccount?->id,
                'reorder_point' => 3.00,
                'max_quantity' => 25.00,
                'on_hand' => 0.00,
                'total_value' => 0.00,
                'as_of_date' => now()->toDateString(),
                'purchase_from_vendor' => false,
                'build_point_min' => 2.00,
                'is_active' => true,
                'is_inactive' => false,
            ]);

            // Add components to the assembly
            if ($laptopBundle) {
                $laptopBundle->assemblyComponents()->create([
                    'component_item_id' => $laptop->id,
                    'quantity' => 1.0000,
                    'unit_cost' => $laptop->cost,
                    'total_cost' => $laptop->cost,
                    'notes' => 'Main laptop unit'
                ]);

                $laptopBundle->assemblyComponents()->create([
                    'component_item_id' => $mouse->id,
                    'quantity' => 1.0000,
                    'unit_cost' => $mouse->cost,
                    'total_cost' => $mouse->cost,
                    'notes' => 'Wireless mouse included'
                ]);

                $laptopBundle->assemblyComponents()->create([
                    'component_item_id' => $cable->id,
                    'quantity' => 2.0000,
                    'unit_cost' => $cable->cost,
                    'total_cost' => $cable->cost * 2,
                    'notes' => 'Two USB-C cables included'
                ]);

                // Calculate total cost from components
                $totalComponentCost = $laptopBundle->assemblyComponents()->sum('total_cost');
                $laptopBundle->update([
                    'cost' => $totalComponentCost,
                    'markup_percentage' => $totalComponentCost > 0 
                        ? (($laptopBundle->sales_price - $totalComponentCost) / $totalComponentCost) * 100 
                        : 0,
                    'margin_percentage' => $laptopBundle->sales_price > 0 
                        ? (($laptopBundle->sales_price - $totalComponentCost) / $laptopBundle->sales_price) * 100 
                        : 0,
                ]);
            }
        }

        $this->command->info('Items seeded successfully!');
    }
}