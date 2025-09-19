<?php

namespace App\Http\Controllers\Api\Items;

use App\Http\Controllers\Api\ApiController;
use App\Models\Item;
use App\Models\ItemComponent;
use App\Models\Account;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemsApiController extends ApiController
{
    public function index(Request $request)
    {
        try {
            $query = Item::with(['cogsAccount', 'incomeAccount', 'assetAccount', 'preferredVendor', 'parent'])
                ->withCount('children');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                      ->orWhere('item_number', 'like', "%{$search}%")
                      ->orWhere('manufacturer_part_number', 'like', "%{$search}%");
                });
            }

            // Filter by type
            if ($request->filled('item_type') && $request->item_type !== 'all') {
                $query->where('item_type', $request->item_type);
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true)->where('is_inactive', false);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_inactive', true);
                }
            }

            // Filter by parent
            if ($request->filled('parent_id')) {
                if ($request->parent_id === 'null') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // Sort
            $sortBy = $request->get('sort_by', 'item_name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            $items = $query->paginate(20)->withQueryString();

            // Get filter options
            $itemTypes = Item::select('item_type')
                ->distinct()
                ->orderBy('item_type')
                ->pluck('item_type');

            $parentItems = Item::select('id', 'item_name', 'item_type')
                ->where('is_active', true)
                ->where('is_inactive', false)
                ->orderBy('item_name')
                ->get();

            $data = [
                'items' => $items,
                'filters' => $request->only(['search', 'item_type', 'status', 'parent_id', 'sort_by', 'sort_direction']),
                'itemTypes' => $itemTypes,
                'parentItems' => $parentItems,
                'stats' => [
                    'total' => Item::count(),
                    'active' => Item::where('is_active', true)->where('is_inactive', false)->count(),
                    'inactive' => Item::where('is_inactive', true)->count(),
                    'services' => Item::where('item_type', 'Service')->count(),
                    'inventory' => Item::whereIn('item_type', ['Inventory Part', 'Inventory Assembly'])->count(),
                ]
            ];

            return $this->success($data, 'Items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve items: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_name' => 'required|string|max:255',
                'item_number' => 'nullable|string|max:255|unique:items,item_number',
                'item_type' => 'required|in:Service,Inventory Part,Inventory Assembly,Non-Inventory Part,Other Charge,Discount,Group,Payment',
                'parent_id' => 'nullable|exists:items,id',
                'manufacturer_part_number' => 'nullable|string|max:255',
                'unit_of_measure' => 'nullable|string|max:50',
                'enable_unit_of_measure' => 'boolean',
                'purchase_description' => 'nullable|string',
                'cost' => 'required|numeric|min:0',
                'cost_method' => 'required|in:global_preference,manual',
                'cogs_account_id' => 'nullable|exists:accounts,id',
                'preferred_vendor_id' => 'nullable|exists:suppliers,id',
                'sales_description' => 'nullable|string',
                'sales_price' => 'required|numeric|min:0',
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
                'notes' => 'nullable|string',
                'custom_fields' => 'nullable|array',
            ]);

            // Calculate markup and margin
            $validated['markup_percentage'] = $validated['cost'] > 0 
                ? (($validated['sales_price'] - $validated['cost']) / $validated['cost']) * 100 
                : 0;
            
            $validated['margin_percentage'] = $validated['sales_price'] > 0 
                ? (($validated['sales_price'] - $validated['cost']) / $validated['sales_price']) * 100 
                : 0;

            // Calculate total value for inventory items
            if (in_array($validated['item_type'], ['Inventory Part', 'Inventory Assembly'])) {
                $validated['total_value'] = ($validated['on_hand'] ?? 0) * $validated['cost'];
                $validated['as_of_date'] = now()->toDateString();
            }

            $item = Item::create($validated);

            return $this->success($item, 'Item created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create item: ' . $e->getMessage());
        }
    }

    public function show(Item $item)
    {
        try {
            $item->load([
                'cogsAccount', 'incomeAccount', 'assetAccount', 'preferredVendor', 
                'parent', 'children', 'assemblyComponents.componentItem'
            ]);

            return $this->success($item, 'Item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve item: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Item $item)
    {
        try {
            $validated = $request->validate([
                'item_name' => 'required|string|max:255',
                'item_number' => 'nullable|string|max:255|unique:items,item_number,' . $item->id,
                'item_type' => 'required|in:Service,Inventory Part,Inventory Assembly,Non-Inventory Part,Other Charge,Discount,Group,Payment',
                'parent_id' => 'nullable|exists:items,id',
                'manufacturer_part_number' => 'nullable|string|max:255',
                'unit_of_measure' => 'nullable|string|max:50',
                'enable_unit_of_measure' => 'boolean',
                'purchase_description' => 'nullable|string',
                'cost' => 'required|numeric|min:0',
                'cost_method' => 'required|in:global_preference,manual',
                'cogs_account_id' => 'nullable|exists:accounts,id',
                'preferred_vendor_id' => 'nullable|exists:suppliers,id',
                'sales_description' => 'nullable|string',
                'sales_price' => 'required|numeric|min:0',
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
                'notes' => 'nullable|string',
                'custom_fields' => 'nullable|array',
            ]);

            // Calculate markup and margin
            $validated['markup_percentage'] = $validated['cost'] > 0 
                ? (($validated['sales_price'] - $validated['cost']) / $validated['cost']) * 100 
                : 0;
            
            $validated['margin_percentage'] = $validated['sales_price'] > 0 
                ? (($validated['sales_price'] - $validated['cost']) / $validated['sales_price']) * 100 
                : 0;

            // Calculate total value for inventory items
            if (in_array($validated['item_type'], ['Inventory Part', 'Inventory Assembly'])) {
                $validated['total_value'] = ($validated['on_hand'] ?? 0) * $validated['cost'];
                if (!$item->as_of_date) {
                    $validated['as_of_date'] = now()->toDateString();
                }
            }

            $item->update($validated);

            return $this->success($item, 'Item updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update item: ' . $e->getMessage());
        }
    }

    public function destroy(Item $item)
    {
        try {
            // Check if item has children
            if ($item->hasChildren()) {
                return $this->error('Cannot delete item that has sub-items. Please delete sub-items first.', null, 403);
            }

            // Check if item is used in assemblies
            if ($item->componentOf()->count() > 0) {
                return $this->error('Cannot delete item that is used in assemblies. Please remove from assemblies first.', null, 403);
            }

            $item->delete();

            return $this->success(null, 'Item deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete item: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Item $item)
    {
        try {
            $item->update([
                'is_inactive' => !$item->is_inactive,
                'is_active' => $item->is_inactive ? false : true
            ]);

            $status = $item->is_inactive ? 'inactivated' : 'activated';
            
            return $this->success($item, "Item {$status} successfully");
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle status: ' . $e->getMessage());
        }
    }

    public function addComponent(Request $request, Item $item)
    {
        try {
            if ($item->item_type !== 'Inventory Assembly') {
                return $this->error('Components can only be added to Inventory Assembly items.', null, 403);
            }

            $validated = $request->validate([
                'component_item_id' => 'required|exists:items,id',
                'quantity' => 'required|numeric|min:0.0001',
                'unit_cost' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            // Prevent adding self as component
            if ($validated['component_item_id'] == $item->id) {
                return $this->error('Cannot add item as its own component.', null, 403);
            }

            // Check if component already exists
            $existingComponent = ItemComponent::where('assembly_item_id', $item->id)
                ->where('component_item_id', $validated['component_item_id'])
                ->first();

            if ($existingComponent) {
                return $this->error('This component already exists in the assembly.', null, 403);
            }

            $validated['assembly_item_id'] = $item->id;
            $validated['total_cost'] = $validated['quantity'] * $validated['unit_cost'];

            $component = ItemComponent::create($validated);

            return $this->success($component, 'Component added successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to add component: ' . $e->getMessage());
        }
    }

    public function updateComponent(Request $request, ItemComponent $component)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|numeric|min:0.0001',
                'unit_cost' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            $validated['total_cost'] = $validated['quantity'] * $validated['unit_cost'];

            $component->update($validated);

            return $this->success($component, 'Component updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update component: ' . $e->getMessage());
        }
    }

    public function removeComponent(ItemComponent $component)
    {
        try {
            $component->delete();

            return $this->success(null, 'Component removed successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to remove component: ' . $e->getMessage());
        }
    }
}
