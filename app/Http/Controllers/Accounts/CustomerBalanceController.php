<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CustomerBalanceController extends Controller
{
    /**
     * Display Customer Balance Summary Report
     */
    public function index(Request $request)
    {
        $dateAsOf = $request->get('as_of', date('Y-m-d'));

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $balanceData = [];
        $totalBalance = 0;

        foreach ($customers as $customer) {
            $balance = Invoice::where('customer_id', $customer->id)
                ->where('date', '<=', $dateAsOf)
                ->sum('balance_due');

            if ($balance == 0) continue;

            $balanceData[] = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'balance' => $balance,
            ];

            $totalBalance += $balance;
        }

        return view('accounts.reports.customer-balance', [
            'dateAsOf' => $dateAsOf,
            'balanceData' => $balanceData,
            'totalBalance' => $totalBalance,
        ]);
    }
}

