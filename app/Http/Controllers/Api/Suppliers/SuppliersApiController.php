<?php

namespace App\Http\Controllers\Api\Suppliers;

use App\Http\Controllers\Api\ApiController;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuppliersApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Supplier::withCount('purchaseOrders');

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('company_name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('supplier_code', 'like', '%' . $request->search . '%');
                });
            }

            // Status filter
            if ($request->has('status') && $request->status && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['name', 'company_name', 'email', 'supplier_code', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $suppliers = $query->paginate(15);

            $data = [
                'suppliers' => $suppliers,
                'filters' => $request->only(['search', 'status', 'sort_by', 'sort_direction'])
            ];

            return $this->success($data, 'Suppliers retrieved successfully');
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
                'email' => 'required|email|unique:suppliers,email',
                'phone' => 'required|string|max:255',
                'address' => 'required|string',
                'website' => 'nullable|url|max:255',
                'tax_id' => 'nullable|string|max:255',
                'payment_terms' => 'nullable|string|max:255',
                'credit_limit' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $supplier = Supplier::create($validated);

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
                'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
                'phone' => 'required|string|max:255',
                'address' => 'required|string',
                'website' => 'nullable|url|max:255',
                'tax_id' => 'nullable|string|max:255',
                'payment_terms' => 'nullable|string|max:255',
                'credit_limit' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $supplier->update($validated);

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
            
            return $this->success($supplier, "Supplier {$status} successfully");
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle status: ' . $e->getMessage());
        }
    }
}
