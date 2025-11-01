<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Account;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Show bulk create form
     */
    public function bulkCreate()
    {
        $itemTypes = [
            Item::SERVICE => 'Service',
            Item::INVENTORY_PART => 'Inventory Part',
            Item::INVENTORY_ASSEMBLY => 'Inventory Assembly',
            Item::NON_INVENTORY_PART => 'Non-Inventory Part',
            Item::OTHER_CHARGE => 'Other Charge',
            Item::DISCOUNT => 'Discount',
            Item::GROUP => 'Group',
            Item::PAYMENT => 'Payment',
        ];

        $cogsAccounts = Account::where('account_type', 'Expense')->orderBy('account_name')->get();
        $incomeAccounts = Account::where('account_type', 'Income')->orderBy('account_name')->get();
        $assetAccounts = Account::where('account_type', 'Asset')->orderBy('account_name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('items.bulk-create', compact('itemTypes', 'cogsAccounts', 'incomeAccounts', 'assetAccounts', 'suppliers'));
    }

    /**
     * Store multiple items at once
     */
    public function bulkStore(Request $request)
    {
        // Filter out empty rows before validation
        $items = collect($request->items)->filter(function ($item) {
            return !empty($item['item_name']) && trim($item['item_name']) !== '';
        })->values()->toArray();

        if (empty($items)) {
            return redirect()->back()
                ->withErrors(['items' => 'Please enter at least one item with a name.'])
                ->withInput();
        }

        // Custom validation with filtered items
        $request->merge(['items' => $items]);
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_number' => 'nullable|string|max:50|distinct',
            'items.*.item_type' => 'required|in:' . implode(',', Item::getConstants()),
            'items.*.sales_price' => 'nullable|numeric|min:0',
            'items.*.cost' => 'nullable|numeric|min:0',
            'items.*.income_account_id' => 'nullable|exists:accounts,id',
            'items.*.cogs_account_id' => 'nullable|exists:accounts,id',
            'items.*.asset_account_id' => 'nullable|exists:accounts,id',
            'items.*.preferred_vendor_id' => 'nullable|exists:suppliers,id',
        ]);

        $created = 0;
        $skipped = 0;
        
        foreach ($items as $row) {
            // Ensure unique item_number if provided
            if (!empty($row['item_number'])) {
                if (Item::where('item_number', $row['item_number'])->exists()) {
                    $skipped++;
                    continue; // skip duplicates silently
                }
            }

            // Handle is_active checkbox (can be "0", "1", or not set)
            $isActive = true; // default
            if (isset($row['is_active'])) {
                $isActive = in_array($row['is_active'], ['1', 1, true, 'true'], true);
            }

            try {
                $item = Item::create([
                    'item_name' => $row['item_name'],
                    'item_number' => $row['item_number'] ?? null,
                    'item_type' => $row['item_type'],
                    'sales_price' => $row['sales_price'] ?? 0,
                    'cost' => $row['cost'] ?? 0,
                    'income_account_id' => $row['income_account_id'] ?? null,
                    'cogs_account_id' => $row['cogs_account_id'] ?? null,
                    'asset_account_id' => $row['asset_account_id'] ?? null,
                    'preferred_vendor_id' => $row['preferred_vendor_id'] ?? null,
                    'is_active' => $isActive,
                    'is_inactive' => !$isActive,
                ]);

                $item->updateCalculatedFields();
                $created++;
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }
        }

        $message = $created . ' item(s) created successfully.';
        if ($skipped > 0) {
            $message .= " ($skipped item(s) skipped due to duplicates or errors)";
        }

        return redirect()->route('items.web.index')->with('success', $message);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Item::with(['parent', 'cogsAccount', 'incomeAccount', 'assetAccount', 'preferredVendor']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                      ->orWhere('item_number', 'like', "%{$search}%")
                      ->orWhere('manufacturer_part_number', 'like', "%{$search}%")
                      ->orWhere('purchase_description', 'like', "%{$search}%")
                      ->orWhere('sales_description', 'like', "%{$search}%");
                });
            }

            // Item type filter
            if ($request->filled('item_type')) {
                $query->where('item_type', $request->item_type);
            }

            // Status filter
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true)->where('is_inactive', false);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_inactive', true);
                }
            }

            // Parent filter (for hierarchical view)
            if ($request->filled('parent_id')) {
                if ($request->parent_id === 'top_level') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            $items = $query->orderBy('item_name')->paginate(20);

            // Get item types for filter
            $itemTypes = [
                Item::SERVICE => 'Service',
                Item::INVENTORY_PART => 'Inventory Part',
                Item::INVENTORY_ASSEMBLY => 'Inventory Assembly',
                Item::NON_INVENTORY_PART => 'Non-Inventory Part',
                Item::OTHER_CHARGE => 'Other Charge',
                Item::DISCOUNT => 'Discount',
                Item::GROUP => 'Group',
                Item::PAYMENT => 'Payment',
            ];

            // Get parent items for hierarchical filter
            $parentItems = Item::whereNull('parent_id')->orderBy('item_name')->get();

            return view('items.index', compact('items', 'itemTypes', 'parentItems'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $itemTypes = [
                Item::SERVICE => 'Service',
                Item::INVENTORY_PART => 'Inventory Part',
                Item::INVENTORY_ASSEMBLY => 'Inventory Assembly',
                Item::NON_INVENTORY_PART => 'Non-Inventory Part',
                Item::OTHER_CHARGE => 'Other Charge',
                Item::DISCOUNT => 'Discount',
                Item::GROUP => 'Group',
                Item::PAYMENT => 'Payment',
            ];

            // Get accounts for dropdowns
            $cogsAccounts = Account::where('account_type', 'Expense')->orderBy('account_name')->get();
            $incomeAccounts = Account::where('account_type', 'Income')->orderBy('account_name')->get();
            $assetAccounts = Account::where('account_type', 'Asset')->orderBy('account_name')->get();

            // Get suppliers for preferred vendor
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

            // Get parent items for hierarchical selection
            $parentItems = Item::whereNull('parent_id')->orderBy('item_name')->get();

            return view('items.create', compact('itemTypes', 'cogsAccounts', 'incomeAccounts', 'assetAccounts', 'suppliers', 'parentItems'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'item_name' => 'required|string|max:255',
                'item_number' => 'nullable|string|max:50|unique:items,item_number',
                'item_type' => 'required|in:' . implode(',', Item::getConstants()),
                'parent_id' => 'nullable|exists:items,id',
                'manufacturer_part_number' => 'nullable|string|max:100',
                'unit_of_measure' => 'nullable|string|max:20',
                'enable_unit_of_measure' => 'boolean',
                'purchase_description' => 'nullable|string|max:1000',
                'cost' => 'nullable|numeric|min:0',
                'cost_method' => 'nullable|string|max:50',
                'cogs_account_id' => 'nullable|exists:accounts,id',
                'preferred_vendor_id' => 'nullable|exists:suppliers,id',
                'sales_description' => 'nullable|string|max:1000',
                'sales_price' => 'nullable|numeric|min:0',
                'income_account_id' => 'nullable|exists:accounts,id',
                'asset_account_id' => 'nullable|exists:accounts,id',
                'reorder_point' => 'nullable|numeric|min:0',
                'max_quantity' => 'nullable|numeric|min:0',
                'on_hand' => 'nullable|numeric|min:0',
                'is_used_in_assemblies' => 'boolean',
                'is_performed_by_subcontractor' => 'boolean',
                'purchase_from_vendor' => 'boolean',
                'build_point_min' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
                'is_inactive' => 'boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            $item = Item::create($request->all());

            // Update calculated fields
            $item->updateCalculatedFields();

            return redirect()->route('items.web.index')
                ->with('success', 'Item created successfully.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        try {
            $item->load(['parent', 'children', 'cogsAccount', 'incomeAccount', 'assetAccount', 'preferredVendor', 'assemblyComponents.componentItem']);

            return view('items.show', compact('item'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        try {
            $itemTypes = [
                Item::SERVICE => 'Service',
                Item::INVENTORY_PART => 'Inventory Part',
                Item::INVENTORY_ASSEMBLY => 'Inventory Assembly',
                Item::NON_INVENTORY_PART => 'Non-Inventory Part',
                Item::OTHER_CHARGE => 'Other Charge',
                Item::DISCOUNT => 'Discount',
                Item::GROUP => 'Group',
                Item::PAYMENT => 'Payment',
            ];

            // Get accounts for dropdowns
            $cogsAccounts = Account::where('account_type', 'Expense')->orderBy('account_name')->get();
            $incomeAccounts = Account::where('account_type', 'Income')->orderBy('account_name')->get();
            $assetAccounts = Account::where('account_type', 'Asset')->orderBy('account_name')->get();

            // Get suppliers for preferred vendor
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

            // Get parent items for hierarchical selection (excluding current item and its children)
            $parentItems = Item::whereNull('parent_id')
                ->where('id', '!=', $item->id)
                ->whereNotIn('id', $item->children->pluck('id'))
                ->orderBy('item_name')
                ->get();

            return view('items.edit', compact('item', 'itemTypes', 'cogsAccounts', 'incomeAccounts', 'assetAccounts', 'suppliers', 'parentItems'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        try {
            $request->validate([
                'item_name' => 'required|string|max:255',
                'item_number' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('items', 'item_number')->ignore($item->id)
                ],
                'item_type' => 'required|in:' . implode(',', Item::getConstants()),
                'parent_id' => [
                    'nullable',
                    'exists:items,id',
                    function ($attribute, $value, $fail) use ($item) {
                        if ($value && $value == $item->id) {
                            $fail('Item cannot be its own parent.');
                        }
                        if ($value && $item->children->contains('id', $value)) {
                            $fail('Item cannot be parent of its own child.');
                        }
                    }
                ],
                'manufacturer_part_number' => 'nullable|string|max:100',
                'unit_of_measure' => 'nullable|string|max:20',
                'enable_unit_of_measure' => 'boolean',
                'purchase_description' => 'nullable|string|max:1000',
                'cost' => 'nullable|numeric|min:0',
                'cost_method' => 'nullable|string|max:50',
                'cogs_account_id' => 'nullable|exists:accounts,id',
                'preferred_vendor_id' => 'nullable|exists:suppliers,id',
                'sales_description' => 'nullable|string|max:1000',
                'sales_price' => 'nullable|numeric|min:0',
                'income_account_id' => 'nullable|exists:accounts,id',
                'asset_account_id' => 'nullable|exists:accounts,id',
                'reorder_point' => 'nullable|numeric|min:0',
                'max_quantity' => 'nullable|numeric|min:0',
                'on_hand' => 'nullable|numeric|min:0',
                'is_used_in_assemblies' => 'boolean',
                'is_performed_by_subcontractor' => 'boolean',
                'purchase_from_vendor' => 'boolean',
                'build_point_min' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
                'is_inactive' => 'boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            $item->update($request->all());

            // Update calculated fields
            $item->updateCalculatedFields();

            return redirect()->route('items.web.index')
                ->with('success', 'Item updated successfully.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        try {
            // Prevent deletion of items with children
            if ($item->hasChildren()) {
                return redirect()->route('items.web.index')
                    ->with('error', 'Cannot delete item with sub-items. Please delete sub-items first.');
            }

            // Prevent deletion of items used in assemblies
            if ($item->componentOf()->count() > 0) {
                return redirect()->route('items.web.index')
                    ->with('error', 'Cannot delete item that is used in assemblies. Please remove from assemblies first.');
            }

            $item->delete();

            return redirect()->route('items.web.index')
                ->with('success', 'Item deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('items.web.index')
                ->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    /**
     * Toggle item status
     */
    public function toggleStatus(Item $item)
    {
        try {
            if ($item->is_active) {
                $item->update(['is_active' => false, 'is_inactive' => true]);
                $status = 'deactivated';
            } else {
                $item->update(['is_active' => true, 'is_inactive' => false]);
                $status = 'activated';
            }

            return back()->with('success', "Item {$status} successfully.");
        } catch (Exception $e) {
            return back()->with('error', 'Failed to toggle item status: ' . $e->getMessage());
        }
    }

    /**
     * Get child items for hierarchical selection
     */
    public function getChildItems(Request $request)
    {
        try {
            $parentId = $request->get('parent_id');
            
            if ($parentId === 'top_level') {
                $items = Item::whereNull('parent_id')->orderBy('item_name')->get();
            } else {
                $items = Item::where('parent_id', $parentId)->orderBy('item_name')->get();
            }

            return response()->json($items);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
