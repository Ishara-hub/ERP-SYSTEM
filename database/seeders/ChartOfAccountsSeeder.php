<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing accounts (except system accounts)
        Account::where('is_system', false)->delete();

        // Create main account categories
        $accounts = [
            // ASSETS
            [
                'account_code' => '1000',
                'account_name' => 'Assets',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 1,
                'description' => 'All company assets'
            ],
            [
                'account_code' => '1100',
                'account_name' => 'Current Assets',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 2,
                'description' => 'Assets expected to be converted to cash within one year'
            ],
            [
                'account_code' => '1110',
                'account_name' => 'Cash and Cash Equivalents',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 3,
                'description' => 'Cash, checking, savings, and money market accounts'
            ],
            [
                'account_code' => '1120',
                'account_name' => 'Accounts Receivable',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 4,
                'description' => 'Amounts owed by customers for goods or services'
            ],
            [
                'account_code' => '1130',
                'account_name' => 'Inventory',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 5,
                'description' => 'Goods held for sale'
            ],
            [
                'account_code' => '1140',
                'account_name' => 'Prepaid Expenses',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 6,
                'description' => 'Expenses paid in advance'
            ],
            [
                'account_code' => '1200',
                'account_name' => 'Fixed Assets',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 7,
                'description' => 'Long-term assets used in business operations'
            ],
            [
                'account_code' => '1210',
                'account_name' => 'Equipment',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 8,
                'description' => 'Office equipment, machinery, etc.'
            ],
            [
                'account_code' => '1220',
                'account_name' => 'Accumulated Depreciation - Equipment',
                'account_type' => Account::ASSET,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 9,
                'description' => 'Accumulated depreciation on equipment'
            ],

            // LIABILITIES
            [
                'account_code' => '2000',
                'account_name' => 'Liabilities',
                'account_type' => Account::LIABILITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 10,
                'description' => 'All company liabilities'
            ],
            [
                'account_code' => '2100',
                'account_name' => 'Current Liabilities',
                'account_type' => Account::LIABILITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 11,
                'description' => 'Liabilities due within one year'
            ],
            [
                'account_code' => '2110',
                'account_name' => 'Accounts Payable',
                'account_type' => Account::LIABILITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 12,
                'description' => 'Amounts owed to suppliers'
            ],
            [
                'account_code' => '2120',
                'account_name' => 'Accrued Expenses',
                'account_type' => Account::LIABILITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 13,
                'description' => 'Expenses incurred but not yet paid'
            ],
            [
                'account_code' => '2130',
                'account_name' => 'Short-term Debt',
                'account_type' => Account::LIABILITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 14,
                'description' => 'Debt due within one year'
            ],
            [
                'account_code' => '2200',
                'account_name' => 'Long-term Liabilities',
                'account_type' => Account::LIABILITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 15,
                'description' => 'Liabilities due after one year'
            ],

            // EQUITY
            [
                'account_code' => '3000',
                'account_name' => 'Equity',
                'account_type' => Account::EQUITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 16,
                'description' => 'Owner\'s equity in the business'
            ],
            [
                'account_code' => '3100',
                'account_name' => 'Owner\'s Capital',
                'account_type' => Account::EQUITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 17,
                'description' => 'Initial investment by owner'
            ],
            [
                'account_code' => '3200',
                'account_name' => 'Retained Earnings',
                'account_type' => Account::EQUITY,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 18,
                'description' => 'Accumulated profits retained in the business'
            ],

            // INCOME
            [
                'account_code' => '4000',
                'account_name' => 'Income',
                'account_type' => Account::INCOME,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 19,
                'description' => 'All revenue and income accounts'
            ],
            [
                'account_code' => '4100',
                'account_name' => 'Sales Revenue',
                'account_type' => Account::INCOME,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 20,
                'description' => 'Revenue from sales of goods or services'
            ],
            [
                'account_code' => '4200',
                'account_name' => 'Service Revenue',
                'account_type' => Account::INCOME,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 21,
                'description' => 'Revenue from services provided'
            ],
            [
                'account_code' => '4300',
                'account_name' => 'Other Income',
                'account_type' => Account::INCOME,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 22,
                'description' => 'Other sources of income'
            ],

            // EXPENSES
            [
                'account_code' => '5000',
                'account_name' => 'Expenses',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 23,
                'description' => 'All business expenses'
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Cost of Goods Sold',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 24,
                'description' => 'Direct costs of producing goods or services'
            ],
            [
                'account_code' => '5200',
                'account_name' => 'Operating Expenses',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 25,
                'description' => 'General operating expenses'
            ],
            [
                'account_code' => '5210',
                'account_name' => 'Salaries and Wages',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 26,
                'description' => 'Employee compensation'
            ],
            [
                'account_code' => '5220',
                'account_name' => 'Rent Expense',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 27,
                'description' => 'Office and facility rent'
            ],
            [
                'account_code' => '5230',
                'account_name' => 'Utilities',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 28,
                'description' => 'Electricity, water, internet, etc.'
            ],
            [
                'account_code' => '5240',
                'account_name' => 'Office Supplies',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 29,
                'description' => 'Office supplies and materials'
            ],
            [
                'account_code' => '5250',
                'account_name' => 'Marketing and Advertising',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 30,
                'description' => 'Marketing and advertising expenses'
            ],
            [
                'account_code' => '5260',
                'account_name' => 'Professional Services',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 31,
                'description' => 'Legal, accounting, consulting fees'
            ],
            [
                'account_code' => '5300',
                'account_name' => 'Other Expenses',
                'account_type' => Account::EXPENSE,
                'parent_id' => null,
                'is_system' => true,
                'sort_order' => 32,
                'description' => 'Other miscellaneous expenses'
            ],
        ];

        foreach ($accounts as $accountData) {
            Account::create($accountData);
        }
    }
}