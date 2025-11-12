<?php

namespace App\Http\Controllers\Api\Items;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\Item;
use App\Models\ItemComponent;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemsApiController extends ApiController
{
    /**
     * List items with filters and summary data.
     */
    public function index(Request $request)
    {
        try {
            $query = Item::with([
                'cogsAccount',
                'incomeAccount',
                'assetAccount',
                'preferredVendor',
                'parent',
            ])->withCount('children');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhere('item_number', 'like', "%{$search}%")
                        ->orWhere('manufacturer_part_number', 'like', "%{$search}%")
                        ->orWhere('purchase_description', 'like', "%{$search}%")
                        ->orWhere('sales_description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('item_type') && $request->item_type !== 'all') {
                $query->where('item_type', $request->item_type);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true)->where('is_inactive', false);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_inactive', true);
                }
            }

            if ($request->filled('parent_id')) {
                if ($request->parent_id === 'top_level') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            $sortBy = $request->get('sort_by', 'item_name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $allowedSorts = ['item_name', 'item_number', 'item_type', 'created_at'];
            if (!in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'item_name';
            }
            $query->orderBy($sortBy, $sortDirection);

            $items = $query->paginate((int) $request->get('per_page', 20))->withQueryString();

            $data = [
                'items' => $items,
                'filters' => $request->only(['search', 'item_type', 'status', 'parent_id', 'sort_by', 'sort_direction']),
                'item_types' => $this->getItemTypeOptions(),
                'parent_items' => $this->getParentItemsList(),
                'stats' => [
                    'total' => Item::count(),
                    'active' => Item::where('is_active', true)->where('is_inactive', false)->count(),
                    'inactive' => Item::where('is_inactive', true)->count(),
                    'inventory' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])->count(),
                    'services' => Item::where('item_type', Item::SERVICE)->count(),
                ],
            ];

            return $this->success($data, 'Items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve items: ' . $e->getMessage());
        }
    }

    /**
     * Form options for creating or editing items.
     */
    public function options(Request $request)
    {
        try {
            $excludeId = $request->integer('exclude_id');

            $data = [
                'item_types' => $this->getItemTypeOptions(),
                'cogs_accounts' => Account::where('account_type', 'Expense')->orderBy('account_name')->get(['id', 'account_name']),
                'income_accounts' => Account::where('account_type', 'Income')->orderBy('account_name')->get(['id', 'account_name']),
                'asset_accounts' => Account::where('account_type', 'Asset')->orderBy('account_name')->get(['id', 'account_name']),
                'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'parent_items' => $this->getParentItemsList($excludeId),
            ];

            return $this->success($data, 'Item options retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve item options: ' . $e->getMessage());
        }
    }

    /**
     * Store a new item.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateItemRequest($request);

            $item = DB::transaction(function () use ($validated) {
                $payload = $this->prepareItemPayload($validated);
                $item = Item::create($payload);
                $item->updateCalculatedFields();

                return $item->fresh([
                    'cogsAccount',
                    'incomeAccount',
                    'assetAccount',
                    'preferredVendor',
                    'parent',
                ]);
            });

            return $this->success($item, 'Item created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create item: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific item.
     */
    public function show(Item $item)
    {
        try {
            $item->load([
                'cogsAccount',
                'incomeAccount',
                'assetAccount',
                'preferredVendor',
                'parent',
                'children',
                'assemblyComponents.componentItem',
            ]);

            return $this->success($item, 'Item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve item: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing item.
     */
    public function update(Request $request, Item $item)
    {
        try {
            $validated = $this->validateItemRequest($request, $item);

            $item = DB::transaction(function () use ($validated, $item) {
                $payload = $this->prepareItemPayload($validated, $item);
                $item->update($payload);
                $item->updateCalculatedFields();

                return $item->fresh([
                    'cogsAccount',
                    'incomeAccount',
                    'assetAccount',
                    'preferredVendor',
                    'parent',
                    'children',
                ]);
            });

            return $this->success($item, 'Item updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update item: ' . $e->getMessage());
        }
    }

    /**
     * Delete an item.
     */
    public function destroy(Item $item)
    {
        try {
            if ($item->hasChildren()) {
                return $this->error('Cannot delete item with sub-items. Please delete sub-items first.', null, 403);
            }

            if ($item->componentOf()->count() > 0) {
                return $this->error('Cannot delete item that is used in assemblies. Please remove from assemblies first.', null, 403);
            }

            $item->delete();

            return $this->success(null, 'Item deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete item: ' . $e->getMessage());
        }
    }

    /**
     * Toggle item status.
     */
    public function toggleStatus(Item $item)
    {
        try {
            $isInactive = !$item->is_inactive;
            $item->update([
                'is_inactive' => $isInactive,
                'is_active' => !$isInactive,
            ]);

            $status = $item->is_active ? 'activated' : 'deactivated';
            return $this->success($item->refresh(), "Item {$status} successfully.");
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle status: ' . $e->getMessage());
        }
    }

    /**
     * Bulk store items.
     */
    public function bulkStore(Request $request)
    {
        $itemsInput = $request->input('items');
        if (!$itemsInput && $request->filled('items_json')) {
            $itemsInput = json_decode($request->input('items_json'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->validationError(['items_json' => ['Invalid JSON payload: ' . json_last_error_msg()]]);
            }
        }

        $items = collect($itemsInput ?: [])->filter(function ($item) {
            return !empty($item['item_name']) && trim($item['item_name']) !== '';
        })->map(function ($item) {
            return [
                'item_name' => trim($item['item_name']),
                'item_number' => !empty($item['item_number']) ? trim($item['item_number']) : null,
                'item_type' => $item['item_type'] ?? '',
                'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? (float) $item['sales_price'] : null,
                'cost' => isset($item['cost']) && $item['cost'] !== '' ? (float) $item['cost'] : null,
                'income_account_id' => $item['income_account_id'] ?? null,
                'cogs_account_id' => $item['cogs_account_id'] ?? null,
                'asset_account_id' => $item['asset_account_id'] ?? null,
                'preferred_vendor_id' => $item['preferred_vendor_id'] ?? null,
                'is_active' => $item['is_active'] ?? true,
            ];
        })->values()->toArray();

        if (empty($items)) {
            return $this->validationError(['items' => ['Please provide at least one item with a name.']]);
        }

        $validator = Validator::make(['items' => $items], [
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
            return $this->validationError($validator->errors());
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($items as $index => $row) {
                try {
                    if (!empty($row['item_number']) && Item::where('item_number', $row['item_number'])->exists()) {
                        $skipped++;
                        continue;
                    }

                    $payload = $this->prepareItemPayload($row);
                    $item = Item::create($payload);
                    $item->updateCalculatedFields();
                    $created++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 1) . " ({$row['item_name']}): " . $e->getMessage();
                    Log::warning('Bulk item creation failed', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Bulk import failed: ' . $e->getMessage());
        }

        return $this->success([
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ], "{$created} item(s) created successfully" . ($skipped ? " ({$skipped} skipped)" : ''));
    }

    /**
     * Retrieve child items for hierarchical selection.
     */
    public function getChildItems(Request $request)
    {
        try {
            $parentId = $request->get('parent_id');

            if ($parentId === 'top_level') {
                $items = Item::whereNull('parent_id')->orderBy('item_name')->get();
            } elseif ($parentId) {
                $items = Item::where('parent_id', $parentId)->orderBy('item_name')->get();
            } else {
                $items = Item::orderBy('item_name')->get();
            }

            return $this->success($items, 'Child items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve child items: ' . $e->getMessage());
        }
    }

    /**
     * Import items via CSV upload.
     */
    public function csvImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'update_existing' => 'sometimes|boolean',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return $this->error('Unable to read CSV file.', null, 422);
        }

        $delimiter = $this->detectDelimiter($path);
        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (count($rows) < 2) {
            return $this->validationError(['csv_file' => ['CSV file must contain a header row and at least one data row.']]);
        }

        [$headers, $dataRows] = [array_map('trim', array_shift($rows)), $rows];
        $normalizedHeaders = $this->mapCsvHeaders($headers);

        $incomeAccounts = Account::where('account_type', 'Income')->get()->keyBy('account_name');
        $cogsAccounts = Account::where('account_type', 'Expense')->get()->keyBy('account_name');
        $assetAccounts = Account::where('account_type', 'Asset')->get()->keyBy('account_name');
        $suppliers = Supplier::where('is_active', true)->get()->keyBy('name');

        $created = 0;
        $skipped = 0;
        $errors = [];
        $updateExisting = $request->boolean('update_existing');

        DB::beginTransaction();
        try {
            foreach ($dataRows as $index => $row) {
                if (empty(array_filter($row, fn ($value) => trim((string) $value) !== ''))) {
                    continue;
                }

                $itemData = $this->mapCsvRow($row, $normalizedHeaders, $incomeAccounts, $cogsAccounts, $assetAccounts, $suppliers);
                if (empty($itemData['item_name'])) {
                    $skipped++;
                    continue;
                }

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
                    $errors[] = "Row " . ($index + 2) . " ({$itemData['item_name']}): " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $existingItem = null;
                if (!empty($itemData['item_number'])) {
                    $existingItem = Item::where('item_number', $itemData['item_number'])->first();
                }

                try {
                    if ($existingItem && !$updateExisting) {
                        $skipped++;
                        $errors[] = "Row " . ($index + 2) . " ({$itemData['item_name']}): Duplicate item number";
                        continue;
                    }

                    if ($existingItem && $updateExisting) {
                        $existingItem->update($this->prepareItemPayload($itemData, $existingItem));
                        $existingItem->updateCalculatedFields();
                        $created++;
                    } else {
                        $item = Item::create($this->prepareItemPayload($itemData));
                        $item->updateCalculatedFields();
                        $created++;
                    }
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . " ({$itemData['item_name']}): " . $e->getMessage();
                    Log::error('CSV import error', ['row' => $itemData, 'error' => $e->getMessage()]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to import CSV: ' . $e->getMessage());
        }

        return $this->success([
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ], "{$created} item(s) imported successfully" . ($skipped ? " ({$skipped} skipped)" : ''));
    }

    /**
     * Add component to inventory assembly.
     */
    public function addComponent(Request $request, Item $item)
    {
        try {
            if ($item->item_type !== Item::INVENTORY_ASSEMBLY) {
                return $this->error('Components can only be added to Inventory Assembly items.', null, 403);
            }

            $validated = $request->validate([
                'component_item_id' => 'required|exists:items,id',
                'quantity' => 'required|numeric|min:0.0001',
                'unit_cost' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            if ((int) $validated['component_item_id'] === $item->id) {
                return $this->error('Item cannot be its own component.', null, 403);
            }

            $existing = ItemComponent::where('assembly_item_id', $item->id)
                ->where('component_item_id', $validated['component_item_id'])
                ->first();

            if ($existing) {
                return $this->error('This component already exists in the assembly.', null, 403);
            }

            $component = ItemComponent::create([
                'assembly_item_id' => $item->id,
                'component_item_id' => $validated['component_item_id'],
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'total_cost' => $validated['quantity'] * $validated['unit_cost'],
                'notes' => $validated['notes'] ?? null,
            ]);

            return $this->success($component, 'Component added successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to add component: ' . $e->getMessage());
        }
    }

    /**
     * Update assembly component.
     */
    public function updateComponent(Request $request, ItemComponent $component)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|numeric|min:0.0001',
                'unit_cost' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $component->update([
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'total_cost' => $validated['quantity'] * $validated['unit_cost'],
                'notes' => $validated['notes'] ?? null,
            ]);

            return $this->success($component->fresh(), 'Component updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update component: ' . $e->getMessage());
        }
    }

    /**
     * Remove assembly component.
     */
    public function removeComponent(ItemComponent $component)
    {
        try {
            $component->delete();
            return $this->success(null, 'Component removed successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to remove component: ' . $e->getMessage());
        }
    }

    /**
     * Validation shared between store/update.
     */
    private function validateItemRequest(Request $request, Item $item = null): array
    {
        $itemTypes = implode(',', Item::getConstants());

        return $request->validate([
            'item_name' => 'required|string|max:255',
            'item_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('items', 'item_number')->ignore(optional($item)->id),
            ],
            'item_type' => "required|in:{$itemTypes}",
            'parent_id' => [
                'nullable',
                'exists:items,id',
                function ($attribute, $value, $fail) use ($item) {
                    if (!$item || !$value) {
                        return;
                    }
                    if ((int) $value === $item->id) {
                        $fail('Item cannot be its own parent.');
                    }
                    if ($item->children()->where('id', $value)->exists()) {
                        $fail('Item cannot be parent of its own child.');
                    }
                    if ($this->wouldCreateCircularRelationship($item->id, (int) $value)) {
                        $fail('This parent would create a circular relationship.');
                    }
                },
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
            'custom_fields' => 'nullable|array',
        ]);
    }

    /**
     * Prepare payload for persistence.
     */
    private function prepareItemPayload(array $data, Item $item = null): array
    {
        $booleanFields = [
            'enable_unit_of_measure',
            'is_used_in_assemblies',
            'is_performed_by_subcontractor',
            'purchase_from_vendor',
            'is_active',
            'is_inactive',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        $isActive = $data['is_active'] ?? ($item ? $item->is_active : true);
        $isInactive = $data['is_inactive'] ?? !$isActive;

        $data['is_active'] = $isActive;
        $data['is_inactive'] = $isInactive;

        if (isset($data['item_type']) && in_array($data['item_type'], [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY], true)) {
            $cost = $data['cost'] ?? 0;
            $onHand = $data['on_hand'] ?? 0;
            $data['total_value'] = $onHand * $cost;
            $data['as_of_date'] = $data['as_of_date'] ?? now()->toDateString();
        }

        return $data;
    }

    /**
     * Map CSV headers to internal fields.
     */
    private function mapCsvHeaders(array $headers): array
    {
        $map = [
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

        $normalized = [];
        foreach ($headers as $index => $header) {
            $key = strtolower(trim($header));
            $normalized[$index] = $map[$key] ?? null;
        }

        return $normalized;
    }

    /**
     * Map a CSV row to item data.
     */
    private function mapCsvRow(array $row, array $headers, $incomeAccounts, $cogsAccounts, $assetAccounts, $suppliers): array
    {
        $itemData = [];
        foreach ($headers as $idx => $field) {
            if ($field && isset($row[$idx])) {
                $value = trim($row[$idx]);
                if ($value !== '') {
                    $itemData[$field] = $value;
                }
            }
        }

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

        if (isset($itemData['item_type'])) {
            $typeKey = strtolower($itemData['item_type']);
            $itemData['item_type'] = $itemTypes[$typeKey] ?? $itemData['item_type'];
        }

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

        if (isset($itemData['preferred_vendor']) && !isset($itemData['preferred_vendor_id'])) {
            $vendor = $suppliers->get($itemData['preferred_vendor']);
            if ($vendor) {
                $itemData['preferred_vendor_id'] = $vendor->id;
            }
            unset($itemData['preferred_vendor']);
        }

        if (isset($itemData['is_active'])) {
            $value = strtolower($itemData['is_active']);
            $itemData['is_active'] = in_array($value, ['1', 'yes', 'true', 'active', 'y'], true);
            $itemData['is_inactive'] = !$itemData['is_active'];
        }

        if (isset($itemData['sales_price'])) {
            $itemData['sales_price'] = (float) $itemData['sales_price'];
        }

        if (isset($itemData['cost'])) {
            $itemData['cost'] = (float) $itemData['cost'];
        }

        return $itemData;
    }

    /**
     * Detect CSV delimiter.
     */
    private function detectDelimiter(string $filePath): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        arsort($counts);
        return key($counts) ?: ',';
    }

    /**
     * Return human-readable item type map.
     */
    private function getItemTypeOptions(): array
    {
        return [
            Item::SERVICE => 'Service',
            Item::INVENTORY_PART => 'Inventory Part',
            Item::INVENTORY_ASSEMBLY => 'Inventory Assembly',
            Item::NON_INVENTORY_PART => 'Non-Inventory Part',
            Item::OTHER_CHARGE => 'Other Charge',
            Item::DISCOUNT => 'Discount',
            Item::GROUP => 'Group',
            Item::PAYMENT => 'Payment',
        ];
    }

    /**
     * Parent items for dropdowns.
     */
    private function getParentItemsList(int $excludeId = null)
    {
        $query = Item::whereNull('parent_id')->orderBy('item_name');

        if ($excludeId) {
            $item = Item::with('children')->find($excludeId);
            if ($item) {
                $query->where('id', '!=', $excludeId)
                    ->whereNotIn('id', $item->children->pluck('id')->all());
            }
        }

        return $query->get(['id', 'item_name', 'item_type']);
    }

    /**
     * Prevent circular relationships.
     */
    private function wouldCreateCircularRelationship(int $itemId, int $parentId): bool
    {
        $current = Item::find($parentId);
        while ($current) {
            if ($current->id === $itemId) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }
}

