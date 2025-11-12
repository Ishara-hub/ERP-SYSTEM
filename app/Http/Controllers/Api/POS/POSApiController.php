<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSApiController extends ApiController
{
    /**
     * POS dashboard data.
     */
    public function dashboard()
    {
        try {
            $customers = Customer::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'company']);

            $items = Item::active()
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'sales_price', 'sales_description', 'unit_of_measure']);

            $accountsReceivable = Account::where('account_type', 'Accounts Receivable')
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get(['id', 'account_name', 'account_code']);

            return $this->success([
                'customers' => $customers,
                'items' => $items,
                'accounts_receivable' => $accountsReceivable,
            ], 'POS dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve POS dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Customer-specific pricing history.
     */
    public function customerPricing(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'item_id' => 'required|exists:items,id',
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);
            $item = Item::findOrFail($request->item_id);

            $lastInvoiceItem = InvoiceLineItem::whereHas('invoice', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
                ->where('item_id', $item->id)
                ->orderBy('created_at', 'desc')
                ->first();

            return $this->success([
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
                    'invoice_id' => $lastInvoiceItem->invoice_id,
                    'invoice_no' => $lastInvoiceItem->invoice->invoice_no,
                    'date' => optional($lastInvoiceItem->invoice->date)->format('M d, Y'),
                    'quantity' => $lastInvoiceItem->quantity,
                ] : null,
            ], 'Customer pricing retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve customer pricing: ' . $e->getMessage());
        }
    }

    /**
     * Recent invoices for customer.
     */
    public function customerInvoices(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        try {
            $invoices = Invoice::where('customer_id', $request->customer_id)
                ->with(['lineItems.item'])
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'date' => optional($invoice->date)->format('M d, Y'),
                        'total_amount' => $invoice->total_amount,
                        'status' => $invoice->status,
                        'balance_due' => $invoice->balance_due,
                        'items_count' => $invoice->lineItems->count(),
                        'items' => $invoice->lineItems->map(function ($lineItem) {
                            return [
                                'item_name' => optional($lineItem->item)->item_name ?? $lineItem->description,
                                'quantity' => $lineItem->quantity,
                                'unit_price' => $lineItem->unit_price,
                                'amount' => $lineItem->amount,
                            ];
                        }),
                    ];
                });

            return $this->success($invoices, 'Customer invoices retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve customer invoices: ' . $e->getMessage());
        }
    }

    /**
     * Create invoice directly from POS.
     */
    public function createInvoice(Request $request)
    {
        $itemsKey = $request->has('items') ? 'items' : 'line_items';

        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                $itemsKey => 'required|array|min:1',
                $itemsKey . '.*.item_id' => 'required|exists:items,id',
                $itemsKey . '.*.quantity' => 'required|numeric|min:0.01',
                $itemsKey . '.*.unit_price' => 'required|numeric|min:0',
                $itemsKey . '.*.description' => 'nullable|string',
                'invoice_no' => 'nullable|string|max:255',
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        DB::beginTransaction();
        try {
            $items = $request->input($itemsKey, []);
            $subtotal = collect($items)->reduce(function ($carry, $item) {
                return $carry + ($item['quantity'] * $item['unit_price']);
            }, 0);

            $taxRate = $request->tax_rate ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $discountAmount = 0;
            $shippingAmount = 0;
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            $invoiceNo = $request->invoice_no ?? $this->generateInvoiceNumber();

            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'invoice_no' => $invoiceNo,
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
                'is_online_payment_enabled' => $request->boolean('is_online_payment_enabled', false),
                'status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'payments_applied' => 0,
                'balance_due' => $totalAmount,
            ]);

            foreach ($items as $itemData) {
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

            if (method_exists($invoice, 'calculateTotals')) {
                $invoice->calculateTotals();
                $invoice->refresh();
            }

            DB::commit();

            return $this->success([
                'invoice' => $invoice->load(['customer', 'lineItems']),
            ], 'Invoice created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Generate next invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();

        if ($lastInvoice && $lastInvoice->invoice_no && preg_match('/-(\d+)$/', $lastInvoice->invoice_no, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return 'INV-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}

