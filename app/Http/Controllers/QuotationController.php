<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations = Quotation::with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $items = Item::orderBy('item_name')->get();
        
        return view('quotations.create', compact('customers', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after:quotation_date',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.description' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'line_items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'customer_id' => $request->customer_id,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'status' => 'draft',
                'payment_terms' => $request->payment_terms,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'created_by' => auth()->user()->name ?? 'System',
            ]);

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->line_items as $lineItem) {
                $amount = $lineItem['quantity'] * $lineItem['unit_price'];
                $taxAmount = $amount * ($lineItem['tax_rate'] ?? 0) / 100;
                $discountAmount = $amount * ($lineItem['discount_rate'] ?? 0) / 100;

                $quotation->lineItems()->create([
                    'item_id' => $lineItem['item_id'],
                    'description' => $lineItem['description'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'amount' => $amount,
                    'tax_rate' => $lineItem['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => $lineItem['discount_rate'] ?? 0,
                    'discount_amount' => $discountAmount,
                ]);

                $subtotal += $amount;
                $totalTax += $taxAmount;
                $totalDiscount += $discountAmount;
            }

            $totalAmount = $subtotal + $totalTax - $totalDiscount;

            $quotation->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'Quotation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create quotation: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'lineItems.item']);
        
        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quotation $quotation)
    {
        $customers = Customer::orderBy('name')->get();
        $items = Item::orderBy('item_name')->get();
        $quotation->load('lineItems.item');
        
        return view('quotations.edit', compact('quotation', 'customers', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after:quotation_date',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.description' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'line_items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $quotation->update([
                'customer_id' => $request->customer_id,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'status' => $request->status,
                'payment_terms' => $request->payment_terms,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
            ]);

            // Delete existing line items
            $quotation->lineItems()->delete();

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->line_items as $lineItem) {
                $amount = $lineItem['quantity'] * $lineItem['unit_price'];
                $taxAmount = $amount * ($lineItem['tax_rate'] ?? 0) / 100;
                $discountAmount = $amount * ($lineItem['discount_rate'] ?? 0) / 100;

                $quotation->lineItems()->create([
                    'item_id' => $lineItem['item_id'],
                    'description' => $lineItem['description'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'amount' => $amount,
                    'tax_rate' => $lineItem['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => $lineItem['discount_rate'] ?? 0,
                    'discount_amount' => $discountAmount,
                ]);

                $subtotal += $amount;
                $totalTax += $taxAmount;
                $totalDiscount += $discountAmount;
            }

            $totalAmount = $subtotal + $totalTax - $totalDiscount;

            $quotation->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'Quotation updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update quotation: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }

    /**
     * Print the quotation as PDF
     */
    public function print(Quotation $quotation)
    {
        $quotation->load(['customer', 'lineItems.item']);
        
        $pdf = Pdf::loadView('quotations.print', compact('quotation'));
        
        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }

    /**
     * Send quotation (update status to sent)
     */
    public function send(Quotation $quotation)
    {
        $quotation->update(['status' => 'sent']);
        
        return back()->with('success', 'Quotation sent successfully.');
    }

    /**
     * Accept quotation (update status to accepted)
     */
    public function accept(Quotation $quotation)
    {
        $quotation->update(['status' => 'accepted']);
        
        return back()->with('success', 'Quotation accepted successfully.');
    }

    /**
     * Reject quotation (update status to rejected)
     */
    public function reject(Quotation $quotation)
    {
        $quotation->update(['status' => 'rejected']);
        
        return back()->with('success', 'Quotation rejected successfully.');
    }
}
