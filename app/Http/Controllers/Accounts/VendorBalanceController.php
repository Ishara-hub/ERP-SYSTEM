<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Bill;
use Illuminate\Http\Request;

class VendorBalanceController extends Controller
{
    /**
     * Display Vendor Balance Summary Report
     */
    public function index(Request $request)
    {
        $dateAsOf = $request->get('as_of', date('Y-m-d'));

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $balanceData = [];
        $totalBalance = 0;

        foreach ($suppliers as $supplier) {
            $balance = Bill::where('supplier_id', $supplier->id)
                ->where('bill_date', '<=', $dateAsOf)
                ->sum('balance_due');

            if ($balance == 0) continue;

            $balanceData[] = [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'balance' => $balance,
            ];

            $totalBalance += $balance;
        }

        return view('accounts.reports.vendor-balance', [
            'dateAsOf' => $dateAsOf,
            'balanceData' => $balanceData,
            'totalBalance' => $totalBalance,
        ]);
    }
}

