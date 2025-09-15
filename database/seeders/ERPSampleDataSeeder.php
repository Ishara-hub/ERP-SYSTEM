<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ERPSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample categories
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and components'],
            ['name' => 'Office Supplies', 'description' => 'Office equipment and supplies'],
            ['name' => 'Software', 'description' => 'Software licenses and applications'],
            ['name' => 'Furniture', 'description' => 'Office furniture and fixtures'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create sample suppliers
        $suppliers = [
            ['name' => 'Tech Solutions Inc', 'email' => 'contact@techsolutions.com', 'phone' => '+1-555-0101', 'address' => '123 Tech Street, Silicon Valley, CA'],
            ['name' => 'Office Depot', 'email' => 'orders@officedepot.com', 'phone' => '+1-555-0102', 'address' => '456 Business Ave, New York, NY'],
            ['name' => 'Software Corp', 'email' => 'sales@softwarecorp.com', 'phone' => '+1-555-0103', 'address' => '789 Software Blvd, Seattle, WA'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // Create sample products
        $products = [
            ['name' => 'Laptop Computer', 'sku' => 'LAP-001', 'category_id' => 1, 'cost_price' => 800.00, 'sale_price' => 1200.00, 'quantity' => 50, 'unit' => 'pcs'],
            ['name' => 'Office Chair', 'sku' => 'CHAIR-001', 'category_id' => 4, 'cost_price' => 150.00, 'sale_price' => 250.00, 'quantity' => 30, 'unit' => 'pcs'],
            ['name' => 'Microsoft Office', 'sku' => 'SOFT-001', 'category_id' => 3, 'cost_price' => 200.00, 'sale_price' => 300.00, 'quantity' => 100, 'unit' => 'licenses'],
            ['name' => 'Desk Lamp', 'sku' => 'LAMP-001', 'category_id' => 2, 'cost_price' => 25.00, 'sale_price' => 45.00, 'quantity' => 75, 'unit' => 'pcs'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create sample customers
        $customers = [
            ['name' => 'ABC Corporation', 'email' => 'contact@abccorp.com', 'phone' => '+1-555-0201', 'address' => '100 Corporate Plaza, Business City, BC'],
            ['name' => 'XYZ Enterprises', 'email' => 'info@xyzent.com', 'phone' => '+1-555-0202', 'address' => '200 Enterprise Ave, Commerce City, CC'],
            ['name' => 'Small Business LLC', 'email' => 'hello@smallbiz.com', 'phone' => '+1-555-0203', 'address' => '300 Main Street, Small Town, ST'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Create sample employees
        $employees = [
            [
                'employee_code' => 'EMP001',
                'name' => 'John Smith',
                'nic' => '123456789',
                'email' => 'john.smith@company.com',
                'phone' => '+1-555-0301',
                'department_id' => 1,
                'designation_id' => 1,
                'join_date' => '2023-01-15',
                'salary' => 75000.00,
                'status' => 'active'
            ],
            [
                'employee_code' => 'EMP002',
                'name' => 'Jane Doe',
                'nic' => '987654321',
                'email' => 'jane.doe@company.com',
                'phone' => '+1-555-0302',
                'department_id' => 2,
                'designation_id' => 7,
                'join_date' => '2023-02-20',
                'salary' => 65000.00,
                'status' => 'active'
            ],
            [
                'employee_code' => 'EMP003',
                'name' => 'Mike Johnson',
                'nic' => '456789123',
                'email' => 'mike.johnson@company.com',
                'phone' => '+1-555-0303',
                'department_id' => 3,
                'designation_id' => 8,
                'join_date' => '2023-03-10',
                'salary' => 55000.00,
                'status' => 'active'
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }

        // Note: Accounts are now created by ChartOfAccountsSeeder

        // Create sample invoices
        $invoices = [
            [
                'customer_id' => 1,
                'invoice_no' => 'INV-001',
                'date' => '2024-01-15',
                'total_amount' => 2400.00,
                'status' => 'paid'
            ],
            [
                'customer_id' => 2,
                'invoice_no' => 'INV-002',
                'date' => '2024-01-20',
                'total_amount' => 1500.00,
                'status' => 'unpaid'
            ],
            [
                'customer_id' => 3,
                'invoice_no' => 'INV-003',
                'date' => '2024-01-25',
                'total_amount' => 900.00,
                'status' => 'partial'
            ],
        ];

        foreach ($invoices as $invoice) {
            Invoice::create($invoice);
        }
    }
}
