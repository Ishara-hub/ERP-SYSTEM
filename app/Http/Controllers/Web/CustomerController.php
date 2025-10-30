<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            $customers = $query->orderBy('name')->paginate(20);

            return view('customers.index', compact('customers'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => $request->company,
            'contact_person' => $request->contact_person,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['invoices' => function ($query) {
            $query->latest()->limit(10);
        }, 'salesOrders' => function ($query) {
            $query->latest()->limit(10);
        }, 'interactions' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($customer->id)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => $request->company,
            'contact_person' => $request->contact_person,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Prevent deletion of customers with invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with invoices. Please transfer or delete invoices first.');
        }

        // Prevent deletion of customers with sales orders
        if ($customer->salesOrders()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with sales orders. Please transfer or delete sales orders first.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Toggle customer status
     */
    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);

        $status = $customer->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Customer {$status} successfully.");
    }
}
