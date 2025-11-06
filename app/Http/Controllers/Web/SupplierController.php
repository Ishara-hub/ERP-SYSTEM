<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

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
            'email' => 'nullable|email', // Duplicate emails are allowed
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
            'email' => 'nullable|email', // Duplicate emails are allowed
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

    /**
     * Show bulk create form
     */
    public function bulkCreate(): View
    {
        return view('suppliers.bulk-create');
    }

    /**
     * Store multiple suppliers at once
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        // Filter out empty rows before validation
        $suppliers = collect($request->suppliers)->filter(function ($supplier) {
            return !empty($supplier['name']) && trim($supplier['name']) !== '';
        })->values()->toArray();

        if (empty($suppliers)) {
            return redirect()->back()
                ->withErrors(['suppliers' => 'Please enter at least one supplier with a name.'])
                ->withInput();
        }

        // Custom validation with filtered suppliers using Validator::make
        $validator = Validator::make(['suppliers' => $suppliers], [
            'suppliers' => 'required|array|min:1',
            'suppliers.*.name' => 'required|string|max:255', // Duplicate names are allowed
            'suppliers.*.company_name' => 'nullable|string|max:255',
            'suppliers.*.contact_person' => 'nullable|string|max:255',
            'suppliers.*.email' => 'nullable|email', // Duplicate emails are allowed (just like names)
            'suppliers.*.phone' => 'nullable|string|max:20',
            'suppliers.*.address' => 'nullable|string',
            'suppliers.*.website' => 'nullable|url',
            'suppliers.*.tax_id' => 'nullable|string|max:50',
            'suppliers.*.payment_terms' => 'nullable|string|max:100',
            'suppliers.*.credit_limit' => 'nullable|numeric|min:0',
            'suppliers.*.currency' => 'nullable|string|max:3',
            'suppliers.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $created = 0;
        $skipped = 0;
        
        foreach ($suppliers as $index => $row) {
            $email = !empty($row['email']) ? trim($row['email']) : null;
            
            // Duplicate emails are allowed - no validation prevents them (just like duplicate names)

            // Handle is_active checkbox (can be "0", "1", or not set)
            $isActive = true; // default
            if (isset($row['is_active'])) {
                $isActive = in_array($row['is_active'], ['1', 1, true, 'true'], true);
            }

            try {
                // Duplicate names are allowed - no validation prevents them
                Supplier::create([
                    'name' => trim($row['name']),
                    'company_name' => !empty($row['company_name']) ? trim($row['company_name']) : null,
                    'contact_person' => !empty($row['contact_person']) ? trim($row['contact_person']) : null,
                    'email' => $email,
                    'phone' => !empty($row['phone']) ? trim($row['phone']) : null,
                    'address' => !empty($row['address']) ? trim($row['address']) : null,
                    'website' => !empty($row['website']) ? trim($row['website']) : null,
                    'tax_id' => !empty($row['tax_id']) ? trim($row['tax_id']) : null,
                    'payment_terms' => !empty($row['payment_terms']) ? trim($row['payment_terms']) : null,
                    'credit_limit' => !empty($row['credit_limit']) ? $row['credit_limit'] : null,
                    'currency' => !empty($row['currency']) ? trim($row['currency']) : 'USD',
                    'notes' => !empty($row['notes']) ? trim($row['notes']) : null,
                    'is_active' => $isActive,
                ]);

                $created++;
            } catch (\Exception $e) {
                // Handle any errors (should be rare now that duplicate emails/names are allowed)
                $skipped++;
                // Log error for debugging
                \Log::warning("Failed to create supplier at row " . ($index + 1) . ": " . $e->getMessage());
                continue;
            }
        }

        $message = $created . ' supplier(s) created successfully.';
        if ($skipped > 0) {
            $message .= " ($skipped supplier(s) skipped due to duplicates or errors)";
        }

        return redirect()->route('suppliers.web.index')->with('success', $message);
    }
}


