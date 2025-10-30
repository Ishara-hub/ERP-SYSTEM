<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentDashboardController extends Controller
{
    /**
     * Display the payment dashboard.
     */
    public function index(Request $request)
    {
        try {
            // Get basic data first
            $payments = Payment::with(['paymentCategory'])
                ->orderBy('payment_date', 'desc')
                ->paginate(20);

            // Get payment categories
            $categories = PaymentCategory::where('is_active', true)->orderBy('name')->get();
            
            // Get recent payments
            $recentPayments = Payment::with(['paymentCategory'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get pending approvals
            $pendingApprovals = Payment::with(['paymentCategory'])
                ->where('approval_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get unreconciled payments
            $unreconciledPayments = Payment::with(['paymentCategory'])
                ->where('reconciled', false)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->get();

            // Simple summary
            $summary = [
                'total_payments' => Payment::count(),
                'total_amount' => Payment::sum('amount') ?? 0,
                'completed_payments' => Payment::where('status', 'completed')->count(),
                'completed_amount' => Payment::where('status', 'completed')->sum('amount') ?? 0,
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'reconciled_payments' => Payment::where('reconciled', true)->count(),
                'pending_approvals' => Payment::where('approval_status', 'pending')->count(),
                'payments_by_category' => collect(),
                'payments_by_method' => collect(),
            ];

            return view('payments.dashboard', [
                'payments' => $payments,
                'summary' => $summary,
                'categories' => $categories,
                'recentPayments' => $recentPayments,
                'pendingApprovals' => $pendingApprovals,
                'unreconciledPayments' => $unreconciledPayments,
                'filters' => $request->only(['category_id', 'status', 'payment_method', 'date_from', 'date_to', 'reconciled'])
            ]);
        } catch (\Exception $e) {
            // Return a simple error view or redirect
            return view('payments.dashboard', [
                'payments' => collect(),
                'summary' => [
                    'total_payments' => 0,
                    'total_amount' => 0,
                    'completed_payments' => 0,
                    'completed_amount' => 0,
                    'pending_payments' => 0,
                    'reconciled_payments' => 0,
                    'pending_approvals' => 0,
                    'payments_by_category' => collect(),
                    'payments_by_method' => collect(),
                ],
                'categories' => collect(),
                'recentPayments' => collect(),
                'pendingApprovals' => collect(),
                'unreconciledPayments' => collect(),
                'filters' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get payment summary statistics.
     */
    private function getPaymentSummary(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $query = Payment::whereBetween('payment_date', [$dateFrom, $dateTo]);

        // Apply category filter if specified
        if ($request->get('category_id')) {
            $query->where('payment_category_id', $request->get('category_id'));
        }

        $totalPayments = $query->count();
        $totalAmount = $query->sum('amount');
        $completedPayments = $query->where('status', 'completed')->count();
        $completedAmount = $query->where('status', 'completed')->sum('amount');
        $pendingPayments = $query->where('status', 'pending')->count();
        $reconciledPayments = $query->where('reconciled', true)->count();
        $pendingApprovals = $query->where('approval_status', 'pending')->count();

        // Get payments by category
        $paymentsByCategory = Payment::select('payment_categories.name as category_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->join('payment_categories', 'payments.payment_category_id', '=', 'payment_categories.id')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->groupBy('payment_categories.id', 'payment_categories.name')
            ->orderBy('total', 'desc')
            ->get();

        // Get payments by method
        $paymentsByMethod = Payment::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->groupBy('payment_method')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'completed_payments' => $completedPayments,
            'completed_amount' => $completedAmount,
            'pending_payments' => $pendingPayments,
            'reconciled_payments' => $reconciledPayments,
            'pending_approvals' => $pendingApprovals,
            'payments_by_category' => $paymentsByCategory,
            'payments_by_method' => $paymentsByMethod,
        ];
    }

    /**
     * Show payment reconciliation.
     */
    public function reconciliation()
    {
        $unreconciledPayments = Payment::with(['paymentCategory', 'purchaseOrder.supplier', 'invoice.customer', 'bankAccount'])
            ->where('reconciled', false)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->get();

        $bankAccounts = Account::where('type', 'Asset')
            ->where('account_name', 'like', '%bank%')
            ->orWhere('account_name', 'like', '%checking%')
            ->orWhere('account_name', 'like', '%savings%')
            ->get();

        return view('payments.reconciliation', [
            'unreconciledPayments' => $unreconciledPayments,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Show expense payments.
     */
    public function expenses()
    {
        $expensePayments = Payment::with(['paymentCategory', 'expenseAccount'])
            ->whereHas('paymentCategory', function($query) {
                $query->where('code', 'EXPENSE');
            })
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        $expenseAccounts = Account::where('type', 'Expense')
            ->orderBy('account_name')
            ->get();

        $categories = PaymentCategory::where('is_active', true)->orderBy('name')->get();

        return view('payments.expenses', [
            'expensePayments' => $expensePayments,
            'expenseAccounts' => $expenseAccounts,
            'categories' => $categories,
        ]);
    }

    /**
     * Show payment reports.
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());
        $categoryId = $request->get('category_id');

        $query = Payment::with(['paymentCategory', 'purchaseOrder.supplier', 'invoice.customer'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        if ($categoryId) {
            $query->where('payment_category_id', $categoryId);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();
        $categories = PaymentCategory::where('is_active', true)->orderBy('name')->get();

        // Generate report data
        $reportData = $this->generateReportData($payments);

        return view('payments.reports', [
            'payments' => $payments,
            'categories' => $categories,
            'reportData' => $reportData,
            'filters' => $request->only(['date_from', 'date_to', 'category_id'])
        ]);
    }

    /**
     * Generate report data.
     */
    private function generateReportData($payments)
    {
        $data = [
            'total_amount' => $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'by_category' => $payments->groupBy('paymentCategory.name')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            }),
            'by_method' => $payments->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            }),
            'by_status' => $payments->groupBy('status')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            }),
            'daily_totals' => $payments->groupBy(function($payment) {
                return $payment->payment_date->format('Y-m-d');
            })->map(function($group) {
                return $group->sum('amount');
            }),
        ];

        return $data;
    }
}
