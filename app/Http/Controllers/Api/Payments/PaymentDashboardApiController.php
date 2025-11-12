<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\Payment;
use App\Models\PaymentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentDashboardApiController extends ApiController
{
    /**
     * Display payment dashboard data.
     */
    public function index(Request $request)
    {
        try {
            $payments = Payment::with(['paymentCategory'])
                ->orderBy('payment_date', 'desc')
                ->paginate(20)
                ->withQueryString();

            $categories = PaymentCategory::where('is_active', true)->orderBy('name')->get();

            $recentPayments = Payment::with(['paymentCategory'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $pendingApprovals = Payment::with(['paymentCategory'])
                ->where('approval_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $unreconciledPayments = Payment::with(['paymentCategory'])
                ->where('reconciled', false)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->get();

            $summary = [
                'total_payments' => Payment::count(),
                'total_amount' => (float) (Payment::sum('amount') ?? 0),
                'completed_payments' => Payment::where('status', 'completed')->count(),
                'completed_amount' => (float) (Payment::where('status', 'completed')->sum('amount') ?? 0),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'reconciled_payments' => Payment::where('reconciled', true)->count(),
                'pending_approvals' => Payment::where('approval_status', 'pending')->count(),
                'payments_by_category' => collect(),
                'payments_by_method' => collect(),
            ];

            return $this->success([
                'payments' => $payments,
                'summary' => $summary,
                'categories' => $categories,
                'recent_payments' => $recentPayments,
                'pending_approvals' => $pendingApprovals,
                'unreconciled_payments' => $unreconciledPayments,
                'filters' => $request->only(['category_id', 'status', 'payment_method', 'date_from', 'date_to', 'reconciled']),
            ], 'Payment dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve payment dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Return summary statistics for a date range and optional category.
     */
    public function summary(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        try {
            $baseQuery = Payment::whereBetween('payment_date', [$dateFrom, $dateTo]);

            if ($request->filled('category_id')) {
                $baseQuery->where('payment_category_id', $request->category_id);
            }

            $totalPayments = (clone $baseQuery)->count();
            $totalAmount = (float) (clone $baseQuery)->sum('amount');

            $completedQuery = (clone $baseQuery)->where('status', 'completed');
            $completedPayments = $completedQuery->count();
            $completedAmount = (float) $completedQuery->sum('amount');

            $pendingPayments = (clone $baseQuery)->where('status', 'pending')->count();
            $reconciledPayments = (clone $baseQuery)->where('reconciled', true)->count();
            $pendingApprovals = (clone $baseQuery)->where('approval_status', 'pending')->count();

            $paymentsByCategory = Payment::select('payment_categories.name as category_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->join('payment_categories', 'payments.payment_category_id', '=', 'payment_categories.id')
                ->whereBetween('payment_date', [$dateFrom, $dateTo])
                ->groupBy('payment_categories.id', 'payment_categories.name')
                ->orderBy('total', 'desc')
                ->get();

            $paymentsByMethod = Payment::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->whereBetween('payment_date', [$dateFrom, $dateTo])
                ->groupBy('payment_method')
                ->orderBy('total', 'desc')
                ->get();

            return $this->success([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_payments' => $totalPayments,
                'total_amount' => $totalAmount,
                'completed_payments' => $completedPayments,
                'completed_amount' => $completedAmount,
                'pending_payments' => $pendingPayments,
                'reconciled_payments' => $reconciledPayments,
                'pending_approvals' => $pendingApprovals,
                'payments_by_category' => $paymentsByCategory,
                'payments_by_method' => $paymentsByMethod,
            ], 'Payment summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve payment summary: ' . $e->getMessage());
        }
    }

    /**
     * Unreconciled payments and bank accounts.
     */
    public function reconciliation()
    {
        try {
            $unreconciledPayments = Payment::with(['paymentCategory', 'purchaseOrder.supplier', 'invoice.customer', 'bankAccount'])
                ->where('reconciled', false)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->get();

            $bankAccounts = Account::where(function ($query) {
                $query->where('type', 'Asset')
                      ->where(function ($q) {
                          $q->where('account_name', 'like', '%bank%')
                            ->orWhere('account_name', 'like', '%checking%')
                            ->orWhere('account_name', 'like', '%savings%')
                            ->orWhere('account_name', 'like', '%cash%');
                      });
            })->get();

            return $this->success([
                'unreconciled_payments' => $unreconciledPayments,
                'bank_accounts' => $bankAccounts,
            ], 'Reconciliation data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve reconciliation data: ' . $e->getMessage());
        }
    }

    /**
     * Expense payments listing.
     */
    public function expenses()
    {
        try {
            $expensePayments = Payment::with(['paymentCategory', 'expenseAccount'])
                ->whereHas('paymentCategory', function ($query) {
                    $query->where('code', 'EXPENSE');
                })
                ->orderBy('payment_date', 'desc')
                ->paginate(20)
                ->withQueryString();

            $expenseAccounts = Account::where('type', 'Expense')
                ->orderBy('account_name')
                ->get();

            $categories = PaymentCategory::where('is_active', true)->orderBy('name')->get();

            return $this->success([
                'payments' => $expensePayments,
                'expense_accounts' => $expenseAccounts,
                'categories' => $categories,
            ], 'Expense payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve expense payments: ' . $e->getMessage());
        }
    }

    /**
     * Payment reports for a given range and category filter.
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());
        $categoryId = $request->get('category_id');

        try {
            $query = Payment::with(['paymentCategory', 'purchaseOrder.supplier', 'invoice.customer'])
                ->whereBetween('payment_date', [$dateFrom, $dateTo]);

            if ($categoryId) {
                $query->where('payment_category_id', $categoryId);
            }

            $payments = $query->orderBy('payment_date', 'desc')->get();
            $categories = PaymentCategory::where('is_active', true)->orderBy('name')->get();

            $reportData = $this->generateReportData($payments);

            return $this->success([
                'payments' => $payments,
                'categories' => $categories,
                'report' => $reportData,
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'category_id' => $categoryId,
                ],
            ], 'Payment reports retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve payment reports: ' . $e->getMessage());
        }
    }

    /**
     * Aggregate report data.
     */
    private function generateReportData($payments): array
    {
        return [
            'total_amount' => (float) $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'by_category' => $payments->groupBy('paymentCategory.name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => (float) $group->sum('amount'),
                ];
            }),
            'by_method' => $payments->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => (float) $group->sum('amount'),
                ];
            }),
            'by_status' => $payments->groupBy('status')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => (float) $group->sum('amount'),
                ];
            }),
            'daily_totals' => $payments->groupBy(function ($payment) {
                return optional($payment->payment_date)->format('Y-m-d');
            })->map(function ($group) {
                return (float) $group->sum('amount');
            }),
        ];
    }
}

