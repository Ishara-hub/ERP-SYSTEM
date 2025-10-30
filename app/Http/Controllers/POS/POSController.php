<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class POSController extends Controller
{
    public function index()
    {
        $customers = Customer::where('is_active', true)->get();
        $items = Item::active()->get();
        $subAccounts = \App\Models\Account::whereNotNull('parent_id')
            ->where('is_active', true)
            ->with('parent')
            ->orderBy('account_name')
            ->get();
        
        return view('pos.dashboard', compact('customers', 'items', 'subAccounts'));
    }

    public function getCustomerPricing(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        $itemId = $request->input('item_id');

        if (!$customerId || !$itemId) {
            return response()->json(['error' => 'Customer and item are required'], 400);
        }

        $customer = Customer::findOrFail($customerId);
        $item = Item::findOrFail($itemId);

        // Get the last price this customer paid for this item
        $lastInvoiceItem = InvoiceLineItem::whereHas('invoice', function($query) use ($customerId) {
            $query->where('customer_id', $customerId);
        })
        ->where('item_id', $itemId)
        ->orderBy('created_at', 'desc')
        ->first();

        $pricing = [
            'item' => [
                'id' => $item->id,
                'name' => $item->item_name,
                'current_price' => $item->sales_price,
                'description' => $item->sales_description,
                'unit_of_measure' => $item->unit_of_measure,
            ],
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'company' => $customer->company,
            ],
            'last_price' => $lastInvoiceItem ? $lastInvoiceItem->unit_price : null,
            'last_invoice' => $lastInvoiceItem ? [
                'invoice_no' => $lastInvoiceItem->invoice->invoice_no,
                'date' => $lastInvoiceItem->invoice->date->format('M d, Y'),
                'quantity' => $lastInvoiceItem->quantity,
            ] : null,
        ];

        return response()->json($pricing);
    }

    public function getCustomerInvoices(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');

        if (!$customerId) {
            return response()->json(['error' => 'Customer is required'], 400);
        }

        $invoices = Invoice::where('customer_id', $customerId)
            ->with(['lineItems.item'])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'date' => $invoice->date->format('M d, Y'),
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'balance_due' => $invoice->balance_due,
                    'items_count' => $invoice->lineItems->count(),
                    'items' => $invoice->lineItems->map(function ($lineItem) {
                        return [
                            'item_name' => $lineItem->item ? $lineItem->item->item_name : $lineItem->description,
                            'quantity' => $lineItem->quantity,
                            'unit_price' => $lineItem->unit_price,
                            'amount' => $lineItem->amount,
                        ];
                    }),
                ];
            });

        return response()->json($invoices);
    }

    public function createInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:255',
            'terms' => 'nullable|string|max:255',
            'rep' => 'nullable|string|max:255',
            'template' => 'nullable|string|max:255',
            'ship_date' => 'nullable|date',
            'via' => 'nullable|string|max:255',
            'fob' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'customer_message' => 'nullable|string',
            'memo' => 'nullable|string|max:255',
            'is_online_payment_enabled' => 'nullable|boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        // Calculate totals
        $subtotal = 0;
        $taxRate = $request->tax_rate ?? 0;
        
        foreach ($request->items as $itemData) {
            $amount = $itemData['quantity'] * $itemData['unit_price'];
            $subtotal += $amount;
        }

        $taxAmount = $subtotal * ($taxRate / 100);
        $discountAmount = 0; // Can be implemented later
        $shippingAmount = 0; // Can be implemented later
        $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_no' => $request->invoice_no ?? Invoice::generateInvoiceNumber(),
            'date' => $request->date ?? now()->toDateString(),
            'due_date' => $request->due_date ?? now()->addDays(30)->toDateString(),
            'po_number' => $request->po_number,
            'terms' => $request->terms,
            'rep' => $request->rep,
            'template' => $request->template ?? 'default',
            'ship_date' => $request->ship_date,
            'via' => $request->via,
            'fob' => $request->fob,
            'billing_address' => $request->billing_address,
            'shipping_address' => $request->shipping_address,
            'customer_message' => $request->customer_message,
            'memo' => $request->memo,
            'is_online_payment_enabled' => $request->is_online_payment_enabled ?? false,
            'status' => 'unpaid',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'payments_applied' => 0,
            'balance_due' => $totalAmount,
        ]);

        // Create line items
        foreach ($request->items as $itemData) {
            $item = Item::findOrFail($itemData['item_id']);
            $amount = $itemData['quantity'] * $itemData['unit_price'];
            $lineTaxAmount = $amount * ($taxRate / 100);

            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'description' => $itemData['description'] ?? $item->sales_description ?? $item->item_name,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'amount' => $amount,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTaxAmount,
            ]);
        }

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'total_amount' => $invoice->total_amount,
                'subtotal' => $invoice->subtotal,
                'tax_amount' => $invoice->tax_amount,
                'balance_due' => $invoice->balance_due,
            ]
        ]);
    }
}
