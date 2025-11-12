<?php

namespace App\Http\Controllers\Api\Customers;

use App\Http\Controllers\Api\ApiController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomersApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSorts = ['name', 'email', 'phone', 'created_at'];
            if (!in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'created_at';
            }
            $query->orderBy($sortBy, $sortDirection);

            $customers = $query->paginate((int) $request->get('per_page', 15))->withQueryString();

            return $this->success([
                'customers' => $customers,
                'filters' => $request->only(['search', 'status', 'sort_by', 'sort_direction']),
                'stats' => [
                    'total' => Customer::count(),
                    'active' => Customer::where('is_active', true)->count(),
                    'inactive' => Customer::where('is_active', false)->count(),
                ],
            ], 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve customers: ' . $e->getMessage());
        }
    }

    /**
     * Search customers for autocomplete
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return $this->success(['customers' => []], 'Search query too short');
            }

            $customers = Customer::where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%')
                  ->orWhere('phone', 'like', '%' . $query . '%')
                  ->orWhere('id', 'like', '%' . $query . '%');
            })
            ->select('id', 'name', 'email', 'phone', 'address', 'company')
            ->limit(10)
            ->get();

            return $this->success(['customers' => $customers], 'Search completed successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to search customers: ' . $e->getMessage());
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
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'company' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
            ]);

            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'company' => $validated['company'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
            ]);

            return $this->success($customer, 'Customer created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        try {
            $customer->load(['invoices', 'salesOrders', 'interactions']);
            return $this->success($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve customer: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['nullable', 'email'],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'company' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
            ]);

            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'company' => $validated['company'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => array_key_exists('is_active', $validated)
                    ? (bool) $validated['is_active']
                    : $customer->is_active,
            ]);

            return $this->success($customer, 'Customer updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has any related records
            if ($customer->invoices()->count() > 0 || $customer->salesOrders()->count() > 0) {
                return $this->error('Cannot delete customer with existing invoices or sales orders.', null, 403);
            }

            $customer->delete();

            return $this->success(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete customer: ' . $e->getMessage());
        }
    }

    /**
     * Toggle customer status (if we add status field later)
     */
    public function toggleStatus(Customer $customer)
    {
        try {
            $customer->update(['is_active' => !$customer->is_active]);

            $status = $customer->is_active ? 'activated' : 'deactivated';
            return $this->success($customer->refresh(), "Customer {$status} successfully");
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle status: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple customers at once.
     */
    public function bulkStore(Request $request)
    {
        $customers = collect($request->input('customers', []))
            ->filter(fn ($customer) => !empty($customer['name']) && trim($customer['name']) !== '')
            ->map(function ($customer) {
                return [
                    'name' => trim($customer['name']),
                    'email' => !empty($customer['email']) ? trim($customer['email']) : null,
                    'phone' => !empty($customer['phone']) ? trim($customer['phone']) : null,
                    'address' => !empty($customer['address']) ? trim($customer['address']) : null,
                    'company' => !empty($customer['company']) ? trim($customer['company']) : null,
                    'contact_person' => !empty($customer['contact_person']) ? trim($customer['contact_person']) : null,
                    'notes' => !empty($customer['notes']) ? trim($customer['notes']) : null,
                    'is_active' => isset($customer['is_active']) ? filter_var($customer['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
                ];
            })
            ->values()
            ->toArray();

        if (empty($customers)) {
            return $this->validationError(['customers' => ['Please enter at least one customer with a name.']]);
        }

        $validator = Validator::make(['customers' => $customers], [
            'customers' => 'required|array|min:1',
            'customers.*.name' => 'required|string|max:255',
            'customers.*.email' => 'nullable|email',
            'customers.*.phone' => 'nullable|string|max:20',
            'customers.*.address' => 'nullable|string|max:500',
            'customers.*.company' => 'nullable|string|max:255',
            'customers.*.contact_person' => 'nullable|string|max:255',
            'customers.*.notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $created = 0;
        $skipped = 0;

        foreach ($customers as $row) {
            try {
                Customer::create($row);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }
        }

        return $this->success([
            'created' => $created,
            'skipped' => $skipped,
        ], "{$created} customer(s) created successfully" . ($skipped ? " ({$skipped} skipped due to errors)" : ''));
    }
}
