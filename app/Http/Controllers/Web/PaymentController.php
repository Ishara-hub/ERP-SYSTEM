<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of purchase order payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['purchaseOrder.supplier'])
            ->where('payment_category', 'purchase_order');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('purchaseOrder.supplier', function ($supplierQuery) use ($request) {
                      $supplierQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Payment method filter
        if ($request->has('payment_method') && $request->payment_method && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, ['payment_number', 'payment_date', 'amount', 'status', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $payments = $query->paginate(15);

        return view('payments.index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status', 'payment_method', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
        ]);
    }

    /**
     * Show the form for creating a new payment for a purchase order.
     */
    public function create(Request $request)
    {
        $purchaseOrderId = $request->get('purchase_order_id');
        $purchaseOrder = null;
        
        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with('supplier')->find($purchaseOrderId);
        }

        $purchaseOrders = PurchaseOrder::where('status', '!=', 'cancelled')
            ->with('supplier')
            ->orderBy('po_number')
            ->get(['id', 'po_number', 'total_amount', 'supplier_id']);

        return view('payments.create', [
            'purchaseOrder' => $purchaseOrder,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,cancelled',
            'bank_name' => 'nullable|string|max:255',
            'check_number' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'fee_amount' => 'nullable|numeric|min:0',
            'received_by' => 'nullable|string|max:255',
        ]);

        // Validate payment amount doesn't exceed purchase order balance
        $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
        if ($request->amount > $purchaseOrder->balance_due) {
            return back()->withErrors(['amount' => 'Payment amount cannot exceed purchase order balance of $' . number_format($purchaseOrder->balance_due, 2)]);
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'purchase_order_id' => $request->purchase_order_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => $request->status,
                'bank_name' => $request->bank_name,
                'check_number' => $request->check_number,
                'transaction_id' => $request->transaction_id,
                'fee_amount' => $request->fee_amount ?? 0,
                'received_by' => $request->received_by,
                'payment_category' => 'purchase_order',
            ]);

            DB::commit();

            return redirect()->route('payments.web.show', $payment)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['purchaseOrder.supplier', 'purchaseOrder.items']);
        
        return view('payments.show', [
            'payment' => $payment
        ]);
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        $payment->load(['purchaseOrder.supplier']);
        
        $purchaseOrders = PurchaseOrder::where('status', '!=', 'cancelled')
            ->orWhere('id', $payment->purchase_order_id)
            ->with('supplier')
            ->orderBy('po_number')
            ->get(['id', 'po_number', 'total_amount', 'supplier_id']);

        return view('payments.edit', [
            'payment' => $payment,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,cancelled',
            'bank_name' => 'nullable|string|max:255',
            'check_number' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'fee_amount' => 'nullable|numeric|min:0',
            'received_by' => 'nullable|string|max:255',
        ]);

        // Validate payment amount doesn't exceed purchase order balance (excluding current payment)
        $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
        $otherPayments = $purchaseOrder->payments()->where('id', '!=', $payment->id)->where('status', 'completed')->sum('amount');
        $availableBalance = $purchaseOrder->total_amount - $otherPayments;
        
        if ($request->amount > $availableBalance) {
            return back()->withErrors(['amount' => 'Payment amount cannot exceed available balance of $' . number_format($availableBalance, 2)]);
        }

        DB::beginTransaction();
        try {
            $payment->update($request->all());

            DB::commit();

            return redirect()->route('payments.web.show', $payment)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.web.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Show payment form for a specific purchase order.
     */
    public function createForPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier');
        
        return view('payments.create', [
            'purchaseOrder' => $purchaseOrder,
            'purchaseOrders' => collect([$purchaseOrder]),
        ]);
    }
}


