<?php

namespace App\Http\Controllers\Api\Suppliers;

use App\Http\Controllers\Api\ApiController;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SuppliersApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Supplier::withCount('purchaseOrders');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('company')) {
                if ($request->company === 'has_company') {
                    $query->whereNotNull('company_name');
                } elseif ($request->company === 'no_company') {
                    $query->whereNull('company_name');
                }
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSorts = ['name', 'company_name', 'email', 'supplier_code', 'created_at'];
            if (!in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'created_at';
            }
            $query->orderBy($sortBy, $sortDirection);

            $suppliers = $query->paginate((int) $request->get('per_page', 15))->withQueryString();

            return $this->success([
                'suppliers' => $suppliers,
                'filters' => $request->only(['search', 'status', 'company', 'sort_by', 'sort_direction']),
                'stats' => [
                    'total' => Supplier::count(),
                    'active' => Supplier::where('is_active', true)->count(),
                    'inactive' => Supplier::where('is_active', false)->count(),
                ],
            ], 'Suppliers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'website' => 'nullable|url|max:255',
                'tax_id' => 'nullable|string|max:50',
                'payment_terms' => 'nullable|string|max:100',
                'credit_limit' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'notes' => 'nullable|string',
                'supplier_code' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            $supplier = Supplier::create([
                'name' => $validated['name'],
                'company_name' => $validated['company_name'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'website' => $validated['website'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'credit_limit' => $validated['credit_limit'] ?? null,
                'currency' => $validated['currency'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'supplier_code' => $validated['supplier_code'] ?? null,
                'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
            ]);

            return $this->success($supplier, 'Supplier created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        try {
            $supplier->load(['purchaseOrders' => function ($query) {
                $query->with('items')->orderBy('created_at', 'desc')->limit(10);
            }]);

            // Calculate supplier statistics
            $stats = [
                'total_orders' => $supplier->purchaseOrders()->count(),
                'total_value' => $supplier->purchaseOrders()->sum('total_amount'),
                'pending_orders' => $supplier->purchaseOrders()->whereIn('status', ['draft', 'sent', 'confirmed'])->count(),
                'received_orders' => $supplier->purchaseOrders()->where('status', 'received')->count(),
            ];

            $data = [
                'supplier' => $supplier,
                'stats' => $stats
            ];

            return $this->success($data, 'Supplier retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve supplier: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'website' => 'nullable|url|max:255',
                'tax_id' => 'nullable|string|max:50',
                'payment_terms' => 'nullable|string|max:100',
                'credit_limit' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'notes' => 'nullable|string',
                'supplier_code' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            $supplier->update([
                'name' => $validated['name'],
                'company_name' => $validated['company_name'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'website' => $validated['website'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'credit_limit' => $validated['credit_limit'] ?? null,
                'currency' => $validated['currency'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'supplier_code' => $validated['supplier_code'] ?? null,
                'is_active' => array_key_exists('is_active', $validated)
                    ? (bool) $validated['is_active']
                    : $supplier->is_active,
            ]);

            return $this->success($supplier, 'Supplier updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update supplier: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            // Check if supplier has purchase orders
            if ($supplier->purchaseOrders()->count() > 0) {
                return $this->error('Cannot delete supplier with existing purchase orders.', null, 403);
            }

            $supplier->delete();

            return $this->success(null, 'Supplier deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete supplier: ' . $e->getMessage());
        }
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(Supplier $supplier)
    {
        try {
            $supplier->update(['is_active' => !$supplier->is_active]);

            $status = $supplier->is_active ? 'activated' : 'deactivated';
            
            return $this->success($supplier->refresh(), "Supplier {$status} successfully");
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle status: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple suppliers.
     */
    public function bulkStore(Request $request)
    {
        $suppliers = collect($request->input('suppliers', []))
            ->filter(fn ($supplier) => !empty($supplier['name']) && trim($supplier['name']) !== '')
            ->map(function ($supplier) {
                return [
                    'name' => trim($supplier['name']),
                    'company_name' => !empty($supplier['company_name']) ? trim($supplier['company_name']) : null,
                    'contact_person' => !empty($supplier['contact_person']) ? trim($supplier['contact_person']) : null,
                    'email' => !empty($supplier['email']) ? trim($supplier['email']) : null,
                    'phone' => !empty($supplier['phone']) ? trim($supplier['phone']) : null,
                    'address' => !empty($supplier['address']) ? trim($supplier['address']) : null,
                    'website' => !empty($supplier['website']) ? trim($supplier['website']) : null,
                    'tax_id' => !empty($supplier['tax_id']) ? trim($supplier['tax_id']) : null,
                    'payment_terms' => !empty($supplier['payment_terms']) ? trim($supplier['payment_terms']) : null,
                    'credit_limit' => isset($supplier['credit_limit']) ? (float) $supplier['credit_limit'] : null,
                    'currency' => !empty($supplier['currency']) ? trim($supplier['currency']) : null,
                    'notes' => !empty($supplier['notes']) ? trim($supplier['notes']) : null,
                    'supplier_code' => !empty($supplier['supplier_code']) ? trim($supplier['supplier_code']) : null,
                    'is_active' => isset($supplier['is_active'])
                        ? filter_var($supplier['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true
                        : true,
                ];
            })
            ->values()
            ->toArray();

        if (empty($suppliers)) {
            return $this->validationError(['suppliers' => ['Please enter at least one supplier with a name.']]);
        }

        $validator = Validator::make(['suppliers' => $suppliers], [
            'suppliers' => 'required|array|min:1',
            'suppliers.*.name' => 'required|string|max:255',
            'suppliers.*.company_name' => 'nullable|string|max:255',
            'suppliers.*.contact_person' => 'nullable|string|max:255',
            'suppliers.*.email' => 'nullable|email',
            'suppliers.*.phone' => 'nullable|string|max:20',
            'suppliers.*.address' => 'nullable|string',
            'suppliers.*.website' => 'nullable|url|max:255',
            'suppliers.*.tax_id' => 'nullable|string|max:50',
            'suppliers.*.payment_terms' => 'nullable|string|max:100',
            'suppliers.*.credit_limit' => 'nullable|numeric|min:0',
            'suppliers.*.currency' => 'nullable|string|max:3',
            'suppliers.*.notes' => 'nullable|string',
            'suppliers.*.supplier_code' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($suppliers as $index => $row) {
            try {
                Supplier::create($row);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "Row " . ($index + 1) . " ({$row['name']}): " . $e->getMessage();
            }
        }

        return $this->success([
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ], "{$created} supplier(s) created successfully" . ($skipped ? " ({$skipped} skipped)" : ''));
    }
}
