<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request): View
    {
        try {
            $query = Supplier::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('supplier_code', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $status = $request->get('status');
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Company filter
            if ($request->filled('company')) {
                $company = $request->get('company');
                if ($company === 'has_company') {
                    $query->whereNotNull('company_name');
                } elseif ($company === 'no_company') {
                    $query->whereNull('company_name');
                }
            }

            $suppliers = $query->orderBy('name')->paginate(20);

            return view('suppliers.index', compact('suppliers'));
        } catch (\Exception $e) {
            \Log::error('Error in SupplierController@index: ' . $e->getMessage());
            return view('suppliers.index', ['suppliers' => collect()]);
        }
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($request->all());

        return redirect()->route('suppliers.web.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier
     */
    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchaseOrders']);
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.web.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->route('suppliers.web.index')
                ->with('error', 'Cannot delete supplier with existing purchase orders.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.web.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['is_active' => !$supplier->is_active]);

        $status = $supplier->is_active ? 'activated' : 'deactivated';
        return redirect()->route('suppliers.web.index')
            ->with('success', "Supplier {$status} successfully.");
    }
}


