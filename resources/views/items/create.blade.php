@extends('layouts.modern')

@section('title', 'Create Item')
@section('breadcrumb', 'Create Item')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Item</h1>
                <p class="text-sm text-gray-500">Add a new item to your inventory</p>
            </div>
            <a href="{{ route('items.web.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Item Form -->
    <div class="bg-white rounded-lg shadow-sm border">
        <form action="{{ route('items.web.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Form Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button type="button" onclick="switchTab('basic')" id="basic-tab" class="tab-button active">
                        Basic Info
                    </button>
                    <button type="button" onclick="switchTab('purchase')" id="purchase-tab" class="tab-button">
                        Purchase
                    </button>
                    <button type="button" onclick="switchTab('sales')" id="sales-tab" class="tab-button">
                        Sales
                    </button>
                    <button type="button" onclick="switchTab('inventory')" id="inventory-tab" class="tab-button">
                        Inventory
                    </button>
                </nav>
            </div>

            <!-- Basic Information Tab -->
            <div id="basic-content" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Item Name -->
                    <div class="md:col-span-2">
                        <label for="item_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Item Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="item_name" 
                               id="item_name"
                               value="{{ old('item_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('item_name') border-red-500 @enderror"
                               placeholder="Enter item name"
                               required>
                        @error('item_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Item Number -->
                    <div>
                        <label for="item_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Item Number
                        </label>
                        <input type="text" 
                               name="item_number" 
                               id="item_number"
                               value="{{ old('item_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('item_number') border-red-500 @enderror"
                               placeholder="SKU or part number">
                        @error('item_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Item Type -->
                    <div>
                        <label for="item_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Item Type <span class="text-red-500">*</span>
                        </label>
                        <select name="item_type" 
                                id="item_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('item_type') border-red-500 @enderror"
                                required>
                            <option value="">Select item type</option>
                            @foreach($itemTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('item_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('item_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Parent Item -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent Item
                        </label>
                        <select name="parent_id" 
                                id="parent_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('parent_id') border-red-500 @enderror">
                            <option value="">No parent (top level)</option>
                            @foreach($parentItems as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->item_name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Manufacturer Part Number -->
                    <div>
                        <label for="manufacturer_part_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Manufacturer Part Number
                        </label>
                        <input type="text" 
                               name="manufacturer_part_number" 
                               id="manufacturer_part_number"
                               value="{{ old('manufacturer_part_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('manufacturer_part_number') border-red-500 @enderror"
                               placeholder="Manufacturer part number">
                        @error('manufacturer_part_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unit of Measure -->
                    <div>
                        <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 mb-1">
                            Unit of Measure
                        </label>
                        <input type="text" 
                               name="unit_of_measure" 
                               id="unit_of_measure"
                               value="{{ old('unit_of_measure') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('unit_of_measure') border-red-500 @enderror"
                               placeholder="e.g., Each, Box, Kg">
                        @error('unit_of_measure')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enable Unit of Measure -->
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_unit_of_measure" 
                                   id="enable_unit_of_measure"
                                   value="1"
                                   {{ old('enable_unit_of_measure') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="enable_unit_of_measure" class="ml-2 text-sm text-gray-700">
                                Enable unit of measure
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Information Tab -->
            <div id="purchase-content" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Purchase Description -->
                    <div class="md:col-span-2">
                        <label for="purchase_description" class="block text-sm font-medium text-gray-700 mb-1">
                            Purchase Description
                        </label>
                        <textarea name="purchase_description" 
                                  id="purchase_description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('purchase_description') border-red-500 @enderror"
                                  placeholder="Description for purchase orders">{{ old('purchase_description') }}</textarea>
                        @error('purchase_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cost -->
                    <div>
                        <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">
                            Cost
                        </label>
                        <input type="number" 
                               name="cost" 
                               id="cost"
                               step="0.01"
                               min="0"
                               value="{{ old('cost') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('cost') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('cost')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cost Method -->
                    <div>
                        <label for="cost_method" class="block text-sm font-medium text-gray-700 mb-1">
                            Cost Method
                        </label>
                        <select name="cost_method" 
                                id="cost_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('cost_method') border-red-500 @enderror">
                            <option value="">Select cost method</option>
                            <option value="FIFO" {{ old('cost_method') === 'FIFO' ? 'selected' : '' }}>FIFO</option>
                            <option value="LIFO" {{ old('cost_method') === 'LIFO' ? 'selected' : '' }}>LIFO</option>
                            <option value="Average" {{ old('cost_method') === 'Average' ? 'selected' : '' }}>Average</option>
                        </select>
                        @error('cost_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- COGS Account -->
                    <div>
                        <label for="cogs_account_id" class="block text-sm font-medium text-gray-700 mb-1">
                            COGS Account
                        </label>
                        <select name="cogs_account_id" 
                                id="cogs_account_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('cogs_account_id') border-red-500 @enderror">
                            <option value="">Select COGS account</option>
                            @foreach($cogsAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('cogs_account_id') == $account->id ? 'selected' : '' }}>{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        @error('cogs_account_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preferred Vendor -->
                    <div>
                        <label for="preferred_vendor_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Preferred Vendor
                        </label>
                        <select name="preferred_vendor_id" 
                                id="preferred_vendor_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('preferred_vendor_id') border-red-500 @enderror">
                            <option value="">Select preferred vendor</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('preferred_vendor_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('preferred_vendor_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sales Information Tab -->
            <div id="sales-content" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Sales Description -->
                    <div class="md:col-span-2">
                        <label for="sales_description" class="block text-sm font-medium text-gray-700 mb-1">
                            Sales Description
                        </label>
                        <textarea name="sales_description" 
                                  id="sales_description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('sales_description') border-red-500 @enderror"
                                  placeholder="Description for sales orders">{{ old('sales_description') }}</textarea>
                        @error('sales_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sales Price -->
                    <div>
                        <label for="sales_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Sales Price
                        </label>
                        <input type="number" 
                               name="sales_price" 
                               id="sales_price"
                               step="0.01"
                               min="0"
                               value="{{ old('sales_price') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('sales_price') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('sales_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Income Account -->
                    <div>
                        <label for="income_account_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Income Account
                        </label>
                        <select name="income_account_id" 
                                id="income_account_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('income_account_id') border-red-500 @enderror">
                            <option value="">Select income account</option>
                            @foreach($incomeAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('income_account_id') == $account->id ? 'selected' : '' }}>{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        @error('income_account_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Asset Account -->
                    <div>
                        <label for="asset_account_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Asset Account
                        </label>
                        <select name="asset_account_id" 
                                id="asset_account_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('asset_account_id') border-red-500 @enderror">
                            <option value="">Select asset account</option>
                            @foreach($assetAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('asset_account_id') == $account->id ? 'selected' : '' }}>{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        @error('asset_account_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Inventory Information Tab -->
            <div id="inventory-content" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Reorder Point -->
                    <div>
                        <label for="reorder_point" class="block text-sm font-medium text-gray-700 mb-1">
                            Reorder Point
                        </label>
                        <input type="number" 
                               name="reorder_point" 
                               id="reorder_point"
                               step="0.01"
                               min="0"
                               value="{{ old('reorder_point') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('reorder_point') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('reorder_point')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Max Quantity -->
                    <div>
                        <label for="max_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                            Max Quantity
                        </label>
                        <input type="number" 
                               name="max_quantity" 
                               id="max_quantity"
                               step="0.01"
                               min="0"
                               value="{{ old('max_quantity') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('max_quantity') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('max_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- On Hand -->
                    <div>
                        <label for="on_hand" class="block text-sm font-medium text-gray-700 mb-1">
                            On Hand Quantity
                        </label>
                        <input type="number" 
                               name="on_hand" 
                               id="on_hand"
                               step="0.01"
                               min="0"
                               value="{{ old('on_hand') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('on_hand') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('on_hand')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Build Point Min -->
                    <div>
                        <label for="build_point_min" class="block text-sm font-medium text-gray-700 mb-1">
                            Build Point Min
                        </label>
                        <input type="number" 
                               name="build_point_min" 
                               id="build_point_min"
                               step="0.01"
                               min="0"
                               value="{{ old('build_point_min') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('build_point_min') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('build_point_min')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="md:col-span-2">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Options</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="is_used_in_assemblies" 
                                       id="is_used_in_assemblies"
                                       value="1"
                                       {{ old('is_used_in_assemblies') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_used_in_assemblies" class="ml-2 text-sm text-gray-700">
                                    Used in assemblies
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="is_performed_by_subcontractor" 
                                       id="is_performed_by_subcontractor"
                                       value="1"
                                       {{ old('is_performed_by_subcontractor') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_performed_by_subcontractor" class="ml-2 text-sm text-gray-700">
                                    Performed by subcontractor
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="purchase_from_vendor" 
                                       id="purchase_from_vendor"
                                       value="1"
                                       {{ old('purchase_from_vendor') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="purchase_from_vendor" class="ml-2 text-sm text-gray-700">
                                    Purchase from vendor
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active"
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 text-sm text-gray-700">
                                    Item is active
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Notes
                        </label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="Additional notes about the item">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('items.web.index') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Item
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-button {
    @apply py-2 px-1 border-b-2 font-medium text-sm;
    @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300;
}

.tab-button.active {
    @apply border-blue-500 text-blue-600;
}

.tab-content {
    @apply block;
}

.tab-content.hidden {
    @apply hidden;
}
</style>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active class to selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
}
</script>
@endsection