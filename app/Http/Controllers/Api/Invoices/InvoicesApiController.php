<?php

namespace App\Http\Controllers\Api\Invoices;

use App\Http\Controllers\Api\ApiController;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicesApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Invoice::with(['customer', 'lineItems']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('invoice_no', 'like', '%' . $request->search . '%')
                      ->orWhereHas('customer', function ($customerQuery) use ($request) {
                          $customerQuery->where('name', 'like', '%' . $request->search . '%')
                                       ->orWhere('email', 'like', '%' . $request->search . '%');
                      });
                });
            }

            // Status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Date range filter
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['invoice_no', 'date', 'total_amount', 'status', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $invoices = $query->paginate(15);

            $data = [
                'invoices' => $invoices,
                'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
            ];

            return $this->success($data, 'Invoices retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve invoices: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'date' => 'required|date',
                'ship_date' => 'nullable|date',
                'po_number' => 'nullable|string|max:255',
                'terms' => 'nullable|string|max:255',
                'rep' => 'nullable|string|max:255',
                'via' => 'nullable|string|max:255',
                'fob' => 'nullable|string|max:255',
                'customer_message' => 'nullable|string|max:255',
                'memo' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'shipping_address' => 'nullable|string',
                'template' => 'nullable|string|max:255',
                'is_online_payment_enabled' => 'boolean',
                'line_items' => 'required|array|min:1',
                'line_items.*.item_id' => 'nullable|exists:items,id',
                'line_items.*.description' => 'required|string|max:255',
                'line_items.*.quantity' => 'required|numeric|min:0.01',
                'line_items.*.unit_price' => 'required|numeric|min:0',
                'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);

            DB::beginTransaction();
            
            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'date' => $validated['date'],
                'ship_date' => $validated['ship_date'],
                'po_number' => $validated['po_number'],
                'terms' => $validated['terms'],
                'rep' => $validated['rep'],
                'via' => $validated['via'],
                'fob' => $validated['fob'],
                'customer_message' => $validated['customer_message'],
                'memo' => $validated['memo'],
                'billing_address' => $validated['billing_address'],
                'shipping_address' => $validated['shipping_address'],
                'template' => $validated['template'] ?? 'default',
                'is_online_payment_enabled' => $validated['is_online_payment_enabled'] ?? false,
            ]);

            // Create line items
            foreach ($validated['line_items'] as $lineItemData) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            $invoice->load(['customer', 'lineItems.item']);

            return $this->success($invoice, 'Invoice created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        try {
            $invoice->load(['customer', 'lineItems.item', 'payments']);
            return $this->success($invoice, 'Invoice retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'date' => 'required|date',
                'ship_date' => 'nullable|date',
                'po_number' => 'nullable|string|max:255',
                'terms' => 'nullable|string|max:255',
                'rep' => 'nullable|string|max:255',
                'via' => 'nullable|string|max:255',
                'fob' => 'nullable|string|max:255',
                'customer_message' => 'nullable|string|max:255',
                'memo' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'shipping_address' => 'nullable|string',
                'template' => 'nullable|string|max:255',
                'is_online_payment_enabled' => 'boolean',
                'line_items' => 'required|array|min:1',
                'line_items.*.item_id' => 'nullable|exists:items,id',
                'line_items.*.description' => 'required|string|max:255',
                'line_items.*.quantity' => 'required|numeric|min:0.01',
                'line_items.*.unit_price' => 'required|numeric|min:0',
                'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);

            DB::beginTransaction();
            
            // Update invoice
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'date' => $validated['date'],
                'ship_date' => $validated['ship_date'],
                'po_number' => $validated['po_number'],
                'terms' => $validated['terms'],
                'rep' => $validated['rep'],
                'via' => $validated['via'],
                'fob' => $validated['fob'],
                'customer_message' => $validated['customer_message'],
                'memo' => $validated['memo'],
                'billing_address' => $validated['billing_address'],
                'shipping_address' => $validated['shipping_address'],
                'template' => $validated['template'] ?? 'default',
                'is_online_payment_enabled' => $validated['is_online_payment_enabled'] ?? false,
            ]);

            // Delete existing line items
            $invoice->lineItems()->delete();

            // Create new line items
            foreach ($validated['line_items'] as $lineItemData) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            $invoice->load(['customer', 'lineItems.item']);

            return $this->success($invoice, 'Invoice updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            // Check if invoice has payments
            if ($invoice->payments()->count() > 0) {
                return $this->error('Cannot delete invoice with existing payments.', null, 403);
            }

            $invoice->delete();

            return $this->success(null, 'Invoice deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice)
    {
        try {
            $invoice->update([
                'status' => 'paid',
                'payments_applied' => $invoice->total_amount,
                'balance_due' => 0,
            ]);

            return $this->success($invoice, 'Invoice marked as paid');
        } catch (\Exception $e) {
            return $this->serverError('Failed to mark invoice as paid: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        try {
            $invoice->load(['customer', 'lineItems.item']);
            return $this->success($invoice, 'Invoice print data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve invoice print data: ' . $e->getMessage());
        }
    }

    /**
     * Email invoice
     */
    public function email(Invoice $invoice)
    {
        try {
            // TODO: Implement email functionality
            return $this->success(['message' => 'Invoice email functionality will be implemented soon'], 'Email functionality not implemented');
        } catch (\Exception $e) {
            return $this->serverError('Failed to email invoice: ' . $e->getMessage());
        }
    }
}
