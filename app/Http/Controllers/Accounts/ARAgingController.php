<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ARAgingController extends Controller
{
    /**
     * Display A/R Aging Summary Report
     */
    public function index(Request $request)
    {
        $dateAsOf = $request->get('as_of', date('Y-m-d'));

        $customers = Customer::where('is_active', true)->get();
        $agingData = [];

        $totalCurrent = 0;
        $total1_30 = 0;
        $total31_60 = 0;
        $total61_90 = 0;
        $totalOver90 = 0;
        $totalBalance = 0;

        foreach ($customers as $customer) {
            $invoices = Invoice::where('customer_id', $customer->id)
                ->where('balance_due', '>', 0)
                ->where('date', '<=', $dateAsOf)
                ->get();

            if ($invoices->isEmpty()) continue;

            $current = 0;
            $aging1_30 = 0;
            $aging31_60 = 0;
            $aging61_90 = 0;
            $agingOver90 = 0;

            foreach ($invoices as $invoice) {
                $daysOverdue = max(0, \Carbon\Carbon::parse($dateAsOf)->diffInDays($invoice->date));

                if ($daysOverdue <= 0) {
                    $current += $invoice->balance_due;
                } elseif ($daysOverdue <= 30) {
                    $aging1_30 += $invoice->balance_due;
                } elseif ($daysOverdue <= 60) {
                    $aging31_60 += $invoice->balance_due;
                } elseif ($daysOverdue <= 90) {
                    $aging61_90 += $invoice->balance_due;
                } else {
                    $agingOver90 += $invoice->balance_due;
                }
            }

            $customerTotal = $current + $aging1_30 + $aging31_60 + $aging61_90 + $agingOver90;

            $agingData[] = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'current' => $current,
                '1_30' => $aging1_30,
                '31_60' => $aging31_60,
                '61_90' => $aging61_90,
                'over90' => $agingOver90,
                'total' => $customerTotal,
            ];

            $totalCurrent += $current;
            $total1_30 += $aging1_30;
            $total31_60 += $aging31_60;
            $total61_90 += $aging61_90;
            $totalOver90 += $agingOver90;
            $totalBalance += $customerTotal;
        }

        return view('accounts.reports.ar-aging', [
            'dateAsOf' => $dateAsOf,
            'agingData' => $agingData,
            'totals' => [
                'current' => $totalCurrent,
                '1_30' => $total1_30,
                '31_60' => $total31_60,
                '61_90' => $total61_90,
                'over90' => $totalOver90,
                'total' => $totalBalance,
            ]
        ]);
    }
}

