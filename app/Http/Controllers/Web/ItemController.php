<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Account;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        // Check if data is coming as JSON (to bypass max_input_vars limit)
        if ($request->has('items_json') && !empty($request->items_json)) {
            $items = json_decode($request->items_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('JSON decode error: ' . json_last_error_msg(), ['json' => $request->items_json]);
                return redirect()->back()
                    ->withErrors(['items' => 'Invalid data format: ' . json_last_error_msg()])
                    ->withInput();
            }
        } else {
            // Fallback to regular form data (for backward compatibility)
            $items = collect($request->items ?? [])->filter(function ($item) {
                return !empty($item['item_name']) && trim($item['item_name']) !== '';
            })->values()->toArray();
        }

        // Filter out empty rows and clean up data
        $items = collect($items)->filter(function ($item) {
            return !empty($item['item_name']) && trim($item['item_name']) !== '';
        })->map(function ($item) {
            // Convert empty strings to null for optional fields
            return [
                'item_name' => trim($item['item_name']),
                'item_number' => !empty($item['item_number']) ? trim($item['item_number']) : null,
                'item_type' => $item['item_type'] ?? '',
                'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' && $item['sales_price'] !== null ? (float)$item['sales_price'] : null,
                'cost' => isset($item['cost']) && $item['cost'] !== '' && $item['cost'] !== null ? (float)$item['cost'] : null,
                'income_account_id' => !empty($item['income_account_id']) ? $item['income_account_id'] : null,
                'cogs_account_id' => !empty($item['cogs_account_id']) ? $item['cogs_account_id'] : null,
                'asset_account_id' => !empty($item['asset_account_id']) ? $item['asset_account_id'] : null,
                'preferred_vendor_id' => !empty($item['preferred_vendor_id']) ? $item['preferred_vendor_id'] : null,
                'is_active' => $item['is_active'] ?? '1',
            ];
        })->values()->toArray();

        if (empty($items)) {
            return redirect()->back()
                ->withErrors(['items' => 'Please enter at least one item with a name.'])
                ->withInput();
        }
        
        \Log::info('Bulk store items count: ' . count($items));

        // Validate items
        $validator = \Validator::make(['items' => $items], [
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_number' => 'nullable|string|max:50',
            'items.*.item_type' => 'required|in:' . implode(',', Item::getConstants()),
            'items.*.sales_price' => 'nullable|numeric|min:0',
            'items.*.cost' => 'nullable|numeric|min:0',
            'items.*.income_account_id' => 'nullable|exists:accounts,id',
            'items.*.cogs_account_id' => 'nullable|exists:accounts,id',
            'items.*.asset_account_id' => 'nullable|exists:accounts,id',
            'items.*.preferred_vendor_id' => 'nullable|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($items as $index => $row) {
            try {
                // Ensure unique item_number if provided
                if (!empty($row['item_number'])) {
                    if (Item::where('item_number', $row['item_number'])->exists()) {
                        $skipped++;
                        \Log::warning("Skipped duplicate item_number: {$row['item_number']} at index {$index}");
                        continue; // skip duplicates silently
                    }
                }

                // Handle is_active checkbox (can be "0", "1", or not set)
                $isActive = true; // default
                if (isset($row['is_active'])) {
                    $isActive = in_array($row['is_active'], ['1', 1, true, 'true'], true);
                }

                $item = Item::create([
                    'item_name' => $row['item_name'],
                    'item_number' => $row['item_number'],
                    'item_type' => $row['item_type'],
                    'sales_price' => $row['sales_price'] ?? 0,
                    'cost' => $row['cost'] ?? 0,
                    'income_account_id' => $row['income_account_id'],
                    'cogs_account_id' => $row['cogs_account_id'],
                    'asset_account_id' => $row['asset_account_id'],
                    'preferred_vendor_id' => $row['preferred_vendor_id'],
                    'is_active' => $isActive,
                    'is_inactive' => !$isActive,
                ]);

                $item->updateCalculatedFields();
                $created++;
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "Row " . ($index + 1) . " ({$row['item_name']}): " . $e->getMessage();
                \Log::error("Failed to create item at index {$index}: " . $e->getMessage(), [
                    'item' => $row,
                    'trace' => $e->getTraceAsString()
                ]);
                continue;
            }
        }
        
        \Log::info("Bulk store completed: {$created} created, {$skipped} skipped");

        $message = $created . ' item(s) created successfully.';
        if ($skipped > 0) {
            $message .= " ($skipped item(s) skipped due to duplicates or errors)";
        }

        // Handle AJAX requests (check for items_json or X-Requested-With header)
        if ($request->has('items_json') || $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'skipped' => $skipped,
                'redirect' => route('items.web.index')
            ]);
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

    /**
     * Show CSV import form
     */
    public function csvImport()
    {
        return view('items.csv-import');
    }

    /**
     * Handle CSV file import
     */
    public function csvImportStore(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Read CSV file with proper encoding handling
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->back()
                ->withErrors(['csv_file' => 'Unable to read CSV file.'])
                ->withInput();
        }
        
        $data = [];
        // Detect delimiter
        $delimiter = $this->detectDelimiter($path);
        
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $data[] = $row;
        }
        fclose($handle);
        
        if (count($data) < 2) {
            return redirect()->back()
                ->withErrors(['csv_file' => 'CSV file must contain at least a header row and one data row.'])
                ->withInput();
        }

        // Get header row
        $headers = array_map('trim', $data[0]);
        
        // Map headers to field names (case-insensitive)
        $headerMap = [
            'item name' => 'item_name',
            'name' => 'item_name',
            'item_name' => 'item_name',
            'item number' => 'item_number',
            'item_number' => 'item_number',
            'number' => 'item_number',
            'sku' => 'item_number',
            'type' => 'item_type',
            'item_type' => 'item_type',
            'item type' => 'item_type',
            'sales price' => 'sales_price',
            'sales_price' => 'sales_price',
            'price' => 'sales_price',
            'cost' => 'cost',
            'income account' => 'income_account',
            'income_account' => 'income_account',
            'income account id' => 'income_account_id',
            'income_account_id' => 'income_account_id',
            'cogs account' => 'cogs_account',
            'cogs_account' => 'cogs_account',
            'cogs account id' => 'cogs_account_id',
            'cogs_account_id' => 'cogs_account_id',
            'asset account' => 'asset_account',
            'asset_account' => 'asset_account',
            'asset account id' => 'asset_account_id',
            'asset_account_id' => 'asset_account_id',
            'vendor' => 'preferred_vendor',
            'preferred_vendor' => 'preferred_vendor',
            'preferred vendor' => 'preferred_vendor',
            'preferred_vendor_id' => 'preferred_vendor_id',
            'preferred vendor id' => 'preferred_vendor_id',
            'supplier' => 'preferred_vendor',
            'active' => 'is_active',
            'is_active' => 'is_active',
            'is active' => 'is_active',
        ];

        // Normalize headers
        $normalizedHeaders = [];
        foreach ($headers as $index => $header) {
            $lowerHeader = strtolower(trim($header));
            $normalizedHeaders[$index] = $headerMap[$lowerHeader] ?? null;
        }

        // Get accounts and suppliers for matching
        $incomeAccounts = Account::where('account_type', 'Income')->get()->keyBy('account_name');
        $cogsAccounts = Account::where('account_type', 'Expense')->get()->keyBy('account_name');
        $assetAccounts = Account::where('account_type', 'Asset')->get()->keyBy('account_name');
        $suppliers = Supplier::where('is_active', true)->get()->keyBy('name');
        
        $itemTypes = [
            'service' => Item::SERVICE,
            'inventory part' => Item::INVENTORY_PART,
            'inventory assembly' => Item::INVENTORY_ASSEMBLY,
            'non-inventory part' => Item::NON_INVENTORY_PART,
            'non inventory part' => Item::NON_INVENTORY_PART,
            'other charge' => Item::OTHER_CHARGE,
            'discount' => Item::DISCOUNT,
            'group' => Item::GROUP,
            'payment' => Item::PAYMENT,
        ];

        $created = 0;
        $skipped = 0;
        $errors = [];

        // Process data rows (skip header)
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $itemData = [];
            
            // Map CSV columns to item data
            foreach ($normalizedHeaders as $colIndex => $fieldName) {
                if ($fieldName && isset($row[$colIndex])) {
                    $value = trim($row[$colIndex]);
                    if ($value !== '') {
                        $itemData[$fieldName] = $value;
                    }
                }
            }

            // Skip if no item name
            if (empty($itemData['item_name'])) {
                $skipped++;
                continue;
            }

            // Map item type
            if (isset($itemData['item_type'])) {
                $typeLower = strtolower(trim($itemData['item_type']));
                $itemData['item_type'] = $itemTypes[$typeLower] ?? $itemData['item_type'];
            }

            // Map account names to IDs
            if (isset($itemData['income_account']) && !isset($itemData['income_account_id'])) {
                $account = $incomeAccounts->get($itemData['income_account']);
                if ($account) {
                    $itemData['income_account_id'] = $account->id;
                }
                unset($itemData['income_account']);
            }

            if (isset($itemData['cogs_account']) && !isset($itemData['cogs_account_id'])) {
                $account = $cogsAccounts->get($itemData['cogs_account']);
                if ($account) {
                    $itemData['cogs_account_id'] = $account->id;
                }
                unset($itemData['cogs_account']);
            }

            if (isset($itemData['asset_account']) && !isset($itemData['asset_account_id'])) {
                $account = $assetAccounts->get($itemData['asset_account']);
                if ($account) {
                    $itemData['asset_account_id'] = $account->id;
                }
                unset($itemData['asset_account']);
            }

            // Map vendor name to ID
            if (isset($itemData['preferred_vendor']) && !isset($itemData['preferred_vendor_id'])) {
                $vendor = $suppliers->get($itemData['preferred_vendor']);
                if ($vendor) {
                    $itemData['preferred_vendor_id'] = $vendor->id;
                }
                unset($itemData['preferred_vendor']);
            }

            // Handle is_active
            $isActive = true;
            if (isset($itemData['is_active'])) {
                $activeValue = strtolower(trim($itemData['is_active']));
                $isActive = in_array($activeValue, ['1', 'yes', 'true', 'active', 'y']);
                unset($itemData['is_active']);
            }

            // Validate item data
            $validator = Validator::make($itemData, [
                'item_name' => 'required|string|max:255',
                'item_number' => 'nullable|string|max:50',
                'item_type' => 'required|in:' . implode(',', Item::getConstants()),
                'sales_price' => 'nullable|numeric|min:0',
                'cost' => 'nullable|numeric|min:0',
                'income_account_id' => 'nullable|exists:accounts,id',
                'cogs_account_id' => 'nullable|exists:accounts,id',
                'asset_account_id' => 'nullable|exists:accounts,id',
                'preferred_vendor_id' => 'nullable|exists:suppliers,id',
            ]);

            if ($validator->fails()) {
                $skipped++;
                $errors[] = "Row " . ($i + 1) . " ({$itemData['item_name']}): " . implode(', ', $validator->errors()->all());
                continue;
            }

            // Check for duplicate item_number
            $updateExisting = $request->has('update_existing') && $request->update_existing == '1';
            $existingItem = null;
            
            if (!empty($itemData['item_number'])) {
                $existingItem = Item::where('item_number', $itemData['item_number'])->first();
            }
            
            if ($existingItem && !$updateExisting) {
                $skipped++;
                $errors[] = "Row " . ($i + 1) . " ({$itemData['item_name']}): Duplicate item number";
                continue;
            }

            try {
                if ($existingItem && $updateExisting) {
                    // Update existing item
                    $existingItem->update([
                        'item_name' => $itemData['item_name'],
                        'item_type' => $itemData['item_type'],
                        'sales_price' => $itemData['sales_price'] ?? 0,
                        'cost' => $itemData['cost'] ?? 0,
                        'income_account_id' => $itemData['income_account_id'] ?? null,
                        'cogs_account_id' => $itemData['cogs_account_id'] ?? null,
                        'asset_account_id' => $itemData['asset_account_id'] ?? null,
                        'preferred_vendor_id' => $itemData['preferred_vendor_id'] ?? null,
                        'is_active' => $isActive,
                        'is_inactive' => !$isActive,
                    ]);
                    $existingItem->updateCalculatedFields();
                    $created++;
                } else {
                    // Create new item
                    $item = Item::create([
                        'item_name' => $itemData['item_name'],
                        'item_number' => $itemData['item_number'] ?? null,
                        'item_type' => $itemData['item_type'],
                        'sales_price' => $itemData['sales_price'] ?? 0,
                        'cost' => $itemData['cost'] ?? 0,
                        'income_account_id' => $itemData['income_account_id'] ?? null,
                        'cogs_account_id' => $itemData['cogs_account_id'] ?? null,
                        'asset_account_id' => $itemData['asset_account_id'] ?? null,
                        'preferred_vendor_id' => $itemData['preferred_vendor_id'] ?? null,
                        'is_active' => $isActive,
                        'is_inactive' => !$isActive,
                    ]);

                    $item->updateCalculatedFields();
                    $created++;
                }
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 1) . " ({$itemData['item_name']}): " . $e->getMessage();
                Log::error("CSV import error at row {$i}: " . $e->getMessage());
            }
        }

        $message = $created . ' item(s) imported successfully.';
        if ($skipped > 0) {
            $message .= " ($skipped item(s) skipped)";
        }

        $redirect = redirect()->route('items.web.index')->with('success', $message);
        
        if (!empty($errors) && count($errors) <= 20) {
            $redirect->with('import_errors', $errors);
        }

        return $redirect;
    }

    /**
     * Detect CSV delimiter
     */
    private function detectDelimiter($filePath)
    {
        $delimiters = [',', ';', "\t", '|'];
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);
        
        $delimiterCounts = [];
        foreach ($delimiters as $delimiter) {
            $delimiterCounts[$delimiter] = substr_count($firstLine, $delimiter);
        }
        
        return array_search(max($delimiterCounts), $delimiterCounts) ?: ',';
    }
}
