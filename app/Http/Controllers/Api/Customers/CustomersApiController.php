<?php

namespace App\Http\Controllers\Api\Customers;

use App\Http\Controllers\Api\ApiController;
use App\Models\Customer;
use Illuminate\Http\Request;
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

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%');
                });
            }

            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['name', 'email', 'phone', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $customers = $query->paginate(15);

            $data = [
                'customers' => $customers,
                'filters' => $request->only(['search', 'sort_by', 'sort_direction'])
            ];

            return $this->success($data, 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve customers: ' . $e->getMessage());
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
                'email' => 'required|email|unique:customers,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
            ]);

            $customer = Customer::create($validated);

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
                'email' => ['required', 'email', Rule::unique('customers')->ignore($customer->id)],
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
            ]);

            $customer->update($validated);

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
            // This can be implemented if we add a status field to customers
            return $this->success(['message' => 'Status toggle not implemented yet'], 'Status toggle not implemented');
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle status: ' . $e->getMessage());
        }
    }
}
