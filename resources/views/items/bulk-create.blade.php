@extends('layouts.modern')

@section('content')
<div class="bg-gray-50 fixed-page-container">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-4 py-6 fixed-page-wrapper">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 page-header-fixed">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bulk Create Items - Spreadsheet View</h1>
                <p class="mt-1 text-sm text-gray-600">Copy and paste from Excel/Google Sheets. Use Tab to navigate between cells. Right-click any cell and select "Copy Down to All Filled Rows" to auto-fill all rows with item names. Press <kbd class="px-1.5 py-0.5 bg-gray-200 rounded text-xs font-mono">Ctrl+D</kbd> to copy down from cell above, or <kbd class="px-1.5 py-0.5 bg-gray-200 rounded text-xs font-mono">Ctrl+Shift+D</kbd> to copy down entire column.</p>
            </div>
            <div class="flex items-center space-x-2 flex-wrap gap-2">
                <div class="flex items-center space-x-1">
                    <button type="button" id="addRows10" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                        </svg>
                        Add 10
                    </button>
                    <button type="button" id="addRows50" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Add 50
                    </button>
                    <button type="button" id="addRows100" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Add 100
                    </button>
                    <button type="button" id="addRows200" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Add 200
                    </button>
                </div>
                <div class="flex items-center space-x-1">
                    <button type="button" id="copyDownBtn" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700" title="Copy Down (Ctrl+D)">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy Down
                    </button>
                    <button type="button" id="copyDownColumnBtn" class="inline-flex items-center px-3 py-2 bg-green-700 text-white text-sm rounded-md hover:bg-green-800" title="Copy Down Column (Ctrl+Shift+D)">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Copy Column
                    </button>
                </div>
                <a href="{{ route('items.web.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('items.web.bulk-store') }}" id="bulkItemsForm">
            @csrf
            <input type="hidden" name="items_json" id="itemsJsonInput">

            <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden">
                <div class="overflow-x-auto overflow-y-auto">
                    <table class="min-w-full border-collapse" id="spreadsheetTable" style="font-family: 'Courier New', monospace;">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr>
                                <th class="w-12 px-2 py-2 text-center text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">#</th>
                                <th class="min-w-[200px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">A</span> Item Name <span class="text-red-500">*</span>
                                </th>
                                <th class="min-w-[150px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">B</span> Item Number
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">C</span> Type <span class="text-red-500">*</span>
                                </th>
                                <th class="min-w-[120px] px-2 py-2 text-right text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">D</span> Sales Price
                                </th>
                                <th class="min-w-[120px] px-2 py-2 text-right text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">E</span> Cost
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">F</span> Income Account
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">G</span> COGS Account
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">H</span> Asset Account
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">I</span> Vendor
                                </th>
                                <th class="min-w-[100px] px-2 py-2 text-center text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">J</span> Active
                                </th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody" class="bg-white">
                            @for($i = 0; $i < 200; $i++)
                            <tr class="item-row hover:bg-blue-50">
                                <td class="px-2 py-1 text-center text-xs text-gray-500 border border-gray-300 bg-gray-50 select-none">{{ $i + 1 }}</td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="items[{{ $i }}][item_name]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Item name" 
                                           data-col="0" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="items[{{ $i }}][item_number]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="SKU/Code" 
                                           data-col="1" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <select name="items[{{ $i }}][item_type]" 
                                            class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm"
                                            data-col="2" data-row="{{ $i }}"
                                            data-searchable="true">
                                        <option value="">-</option>
                                        @foreach($itemTypes as $value => $label)
                                            <option value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="items[{{ $i }}][sales_price]" type="number" step="0.01" min="0" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-right text-sm" 
                                           placeholder="0.00"
                                           data-col="3" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="items[{{ $i }}][cost]" type="number" step="0.01" min="0" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-right text-sm" 
                                           placeholder="0.00"
                                           data-col="4" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <select name="items[{{ $i }}][income_account_id]" 
                                            class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm"
                                            data-col="5" data-row="{{ $i }}"
                                            data-searchable="true">
                                        <option value="">-</option>
                                        @foreach($incomeAccounts as $acc)
                                            <option value="{{ $acc->id }}" data-label="{{ $acc->account_name }}">{{ $acc->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <select name="items[{{ $i }}][cogs_account_id]" 
                                            class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm"
                                            data-col="6" data-row="{{ $i }}"
                                            data-searchable="true">
                                        <option value="">-</option>
                                        @foreach($cogsAccounts as $acc)
                                            <option value="{{ $acc->id }}" data-label="{{ $acc->account_name }}">{{ $acc->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <select name="items[{{ $i }}][asset_account_id]" 
                                            class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm"
                                            data-col="7" data-row="{{ $i }}"
                                            data-searchable="true">
                                        <option value="">-</option>
                                        @foreach($assetAccounts as $acc)
                                            <option value="{{ $acc->id }}" data-label="{{ $acc->account_name }}">{{ $acc->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <select name="items[{{ $i }}][preferred_vendor_id]" 
                                            class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm"
                                            data-col="8" data-row="{{ $i }}"
                                            data-searchable="true">
                                        <option value="">-</option>
                                        @foreach($suppliers as $vendor)
                                            <option value="{{ $vendor->id }}" data-label="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-0 border border-gray-300 text-center">
                                    <input type="hidden" name="items[{{ $i }}][is_active]" value="0" />
                                    <input type="checkbox" name="items[{{ $i }}][is_active]" value="1" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer"
                                           checked
                                           data-col="9" data-row="{{ $i }}" />
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span id="rowCount">200</span> rows available (Maximum: 500 rows)
                </div>
                <div class="flex space-x-3">
                    <button type="button" id="clearAll" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                        Clear All
                    </button>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Items (Ctrl+S)
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Context Menu -->
<div id="contextMenu" class="context-menu">
    <div class="context-menu-item" id="copyDownMenuItem">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span>Copy Down to All Filled Rows</span>
    </div>
    <div class="context-menu-item" id="copyDownSingleMenuItem">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span>Copy Down (Single Row)</span>
    </div>
</div>

<style>
    /* Fixed page layout - no page scrolling */
    .fixed-page-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--header-height));
        overflow: hidden;
    }
    
    .fixed-page-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    
    .page-header-fixed {
        flex-shrink: 0;
    }
    
    /* Error message fixed */
    .mb-4.bg-red-50 {
        flex-shrink: 0;
    }
    
    /* Form with flex layout */
    #bulkItemsForm {
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow: hidden;
    }
    
    /* Table container takes remaining space */
    #bulkItemsForm > div.bg-white {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    #bulkItemsForm > div.bg-white > div.overflow-x-auto {
        flex: 1;
        overflow-x: auto;
        overflow-y: auto;
    }
    
    /* Footer buttons fixed */
    .mt-4.flex.justify-between {
        flex-shrink: 0;
    }
    
    #spreadsheetTable tbody tr td {
        position: relative;
    }
    
    #spreadsheetTable tbody tr td input:focus,
    #spreadsheetTable tbody tr td select:focus {
        background-color: #eff6ff !important;
        box-shadow: inset 0 0 0 2px #3b82f6;
    }
    
    #spreadsheetTable tbody tr:nth-child(even) {
        background-color: #f9fafb;
    }
    
    #spreadsheetTable tbody tr:nth-child(even):hover {
        background-color: #eff6ff;
    }
    
    kbd {
        display: inline-block;
        padding: 0.125rem 0.375rem;
        font-size: 0.75rem;
        line-height: 1;
        color: #374151;
        background-color: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
    }
    
    /* Context Menu Styles */
    .context-menu {
        position: fixed;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 1000;
        min-width: 180px;
        padding: 0.25rem 0;
        display: none;
    }
    
    .context-menu-item {
        padding: 0.5rem 1rem;
        cursor: pointer;
        font-size: 0.875rem;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .context-menu-item:hover {
        background-color: #f3f4f6;
    }
    
    .context-menu-item svg {
        width: 1rem;
        height: 1rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('spreadsheetTable');
    const body = document.getElementById('itemsBody');
    const clearAllBtn = document.getElementById('clearAll');
    const rowCountEl = document.getElementById('rowCount');
    let currentCell = null;
    let pasteBuffer = [];

    // Item type mappings for paste
    const itemTypeMap = {
        'service': 'Service',
        'inventory part': 'Inventory Part',
        'inventory assembly': 'Inventory Assembly',
        'non-inventory part': 'Non-Inventory Part',
        'non inventory part': 'Non-Inventory Part',
        'other charge': 'Other Charge',
        'discount': 'Discount',
        'group': 'Group',
        'payment': 'Payment'
    };

    // Account data for matching
    const accounts = {
        income: [
            @foreach($incomeAccounts as $acc)
            { id: {{ $acc->id }}, name: "{{ $acc->account_name }}" },
            @endforeach
        ],
        cogs: [
            @foreach($cogsAccounts as $acc)
            { id: {{ $acc->id }}, name: "{{ $acc->account_name }}" },
            @endforeach
        ],
        asset: [
            @foreach($assetAccounts as $acc)
            { id: {{ $acc->id }}, name: "{{ $acc->account_name }}" },
            @endforeach
        ]
    };

    const vendors = [
        @foreach($suppliers as $vendor)
        { id: {{ $vendor->id }}, name: "{{ $vendor->name }}" },
        @endforeach
    ];

    // Function to find matching account or vendor
    function findMatch(value, list, type = 'id') {
        if (!value) return null;
        const searchValue = String(value).toLowerCase().trim();
        const match = list.find(item => {
            const itemValue = String(item[type]).toLowerCase().trim();
            return itemValue === searchValue || itemValue.includes(searchValue) || searchValue.includes(itemValue);
        });
        return match ? match.id : null;
    }

    // Handle paste event
    function handlePaste(e) {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text');
        const pasteRows = pasteData.split(/\r\n|\n|\r/).filter(row => row.trim());
        
        if (!pasteRows.length) return;

        const currentRow = parseInt(currentCell.closest('tr').querySelector('td:first-child').textContent) - 1;
        const currentCol = parseInt(currentCell.dataset.col);

        // Calculate how many rows we need
        const existingRowCount = body.querySelectorAll('tr.item-row').length;
        const maxTargetRow = currentRow + pasteRows.length - 1;
        const neededRows = Math.max(0, maxTargetRow - existingRowCount + 1);
        
        // Pre-add all needed rows before pasting (synchronously)
        if (neededRows > 0) {
            const rowsToAdd = Math.min(neededRows, MAX_ROWS - existingRowCount);
            if (rowsToAdd > 0) {
                addRows(rowsToAdd);
            }
        }

        // Now paste all data
        pasteRows.forEach((row, rowOffset) => {
            const cols = row.split('\t');
            cols.forEach((value, colOffset) => {
                const targetRow = currentRow + rowOffset;
                const targetCol = currentCol + colOffset;
                
                if (targetCol > 9) return; // Max 10 columns (0-9)

                const rowEl = Array.from(body.querySelectorAll('tr.item-row'))[targetRow];
                
                if (!rowEl) {
                    console.warn(`Row ${targetRow} not found after adding rows`);
                    return;
                }

                const cells = rowEl.querySelectorAll('input:not([type="hidden"]), select');
                const targetCell = cells[targetCol];

                if (!targetCell) {
                    console.warn(`Cell at row ${targetRow}, col ${targetCol} not found`);
                    return;
                }

                const cleanValue = value.trim();
                applyPasteValueToCell(targetCell, cleanValue, targetCol);
            });
        });

        // Move to next cell after paste
        navigateToCell(currentRow + pasteRows.length, currentCol);
    }

    // Helper function to apply paste value to a cell
    function applyPasteValueToCell(targetCell, cleanValue, targetCol) {
        // Handle different column types
        switch(targetCol) {
                    case 0: // Item Name
                        targetCell.value = cleanValue;
                        break;
                    case 1: // Item Number
                        targetCell.value = cleanValue;
                        break;
                    case 2: // Type
                        const typeLower = cleanValue.toLowerCase();
                        const matchedType = itemTypeMap[typeLower] || cleanValue;
                        const typeOption = targetCell.querySelector(`option[data-label="${matchedType}"], option[value="${matchedType}"]`);
                        if (typeOption) {
                            targetCell.value = typeOption.value;
                        } else {
                            // Try to find partial match
                            for (const option of targetCell.options) {
                                if (option.text.toLowerCase().includes(typeLower) || typeLower.includes(option.text.toLowerCase())) {
                                    targetCell.value = option.value;
                                    break;
                                }
                            }
                        }
                        break;
                    case 3: // Sales Price
                    case 4: // Cost
                        targetCell.value = cleanValue ? parseFloat(cleanValue) || 0 : '';
                        break;
                    case 5: // Income Account
                        const incomeMatch = findMatch(cleanValue, accounts.income, 'name');
                        if (incomeMatch) targetCell.value = incomeMatch;
                        break;
                    case 6: // COGS Account
                        const cogsMatch = findMatch(cleanValue, accounts.cogs, 'name');
                        if (cogsMatch) targetCell.value = cogsMatch;
                        break;
                    case 7: // Asset Account
                        const assetMatch = findMatch(cleanValue, accounts.asset, 'name');
                        if (assetMatch) targetCell.value = assetMatch;
                        break;
                    case 8: // Vendor
                        const vendorMatch = findMatch(cleanValue, vendors, 'name');
                        if (vendorMatch) targetCell.value = vendorMatch;
                        break;
                    case 9: // Active
                        const activeValue = cleanValue.toLowerCase();
                        const isActive = ['yes', 'true', '1', '✓', 'x'].includes(activeValue) || activeValue === '';
                        targetCell.checked = isActive;
                        const hiddenInput = targetCell.closest('td').querySelector('input[type="hidden"]');
                        if (hiddenInput) {
                            hiddenInput.value = isActive ? '0' : '0';
                        }
                        break;
                }
    }

    // Tab navigation
    function navigateToCell(row, col, direction = 'right') {
        const rows = Array.from(body.querySelectorAll('tr.item-row'));
        if (row < 0 || row >= rows.length) {
            if (direction === 'down') {
                // Add rows in larger chunks when navigating down
                const existingRows = body.querySelectorAll('tr.item-row').length;
                const neededRows = row - existingRows + 1;
                if (neededRows > 0) {
                    const rowsToAdd = Math.min(Math.max(50, Math.ceil(neededRows / 50) * 50), 200);
                    addRows(rowsToAdd);
                }
                setTimeout(() => navigateToCell(row, col), 100);
                return;
            }
            return;
        }

        const targetRow = rows[row];
        const cells = targetRow.querySelectorAll('input:not([type="hidden"]), select');
        
        if (col < 0) col = 0;
        if (col >= cells.length) {
            if (direction === 'right' && row < rows.length - 1) {
                navigateToCell(row + 1, 0);
                return;
            }
            return;
        }

        currentCell = cells[col];
        currentCell.focus();
        if (currentCell.tagName === 'INPUT') currentCell.select();
    }

    // Copy down function - copies value from cell above or current cell
    function copyDown(targetCell = null, copyToAllFilledRows = false) {
        const cellToUse = targetCell || currentCell;
        if (!cellToUse) return;
        
        const row = parseInt(cellToUse.dataset.row);
        const col = parseInt(cellToUse.dataset.col);
        
        const rows = Array.from(body.querySelectorAll('tr.item-row'));
        
        // Determine source row and target start row
        let sourceRowIndex;
        let startRow;
        
        if (copyToAllFilledRows) {
            // When copying to all filled rows:
            // - If right-clicking on row 0, use row 0 as source and copy to rows 1+
            // - If right-clicking on row N (N > 0), use row N-1 as source and copy to rows N+
            if (row === 0) {
                sourceRowIndex = 0;
                startRow = 1;
            } else {
                sourceRowIndex = row - 1;
                startRow = row;
            }
        } else {
            // Single copy mode: copy from row above to current row
            if (row === 0) {
                // Can't copy down from first row in single copy mode
                return;
            }
            sourceRowIndex = row - 1;
            startRow = row;
        }
        
        const sourceRowEl = rows[sourceRowIndex];
        if (!sourceRowEl) return;
        
        const sourceCells = sourceRowEl.querySelectorAll('input:not([type="hidden"]), select');
        const sourceField = sourceCells[col];
        
        if (!sourceField) return;
        
        // Get source value
        let sourceValue;
        if (sourceField.tagName === 'INPUT') {
            if (sourceField.type === 'checkbox') {
                sourceValue = sourceField.checked;
            } else {
                sourceValue = sourceField.value;
            }
        } else if (sourceField.tagName === 'SELECT') {
            sourceValue = sourceField.value;
        }
        
        if (copyToAllFilledRows) {
            // Copy to all rows that have item name filled (from startRow down)
            for (let i = startRow; i < rows.length; i++) {
                const targetRowEl = rows[i];
                const itemNameCell = targetRowEl.querySelector('input[name*="[item_name]"]');
                
                // Only copy if item name is filled
                if (itemNameCell && itemNameCell.value.trim()) {
                    const targetCells = targetRowEl.querySelectorAll('input:not([type="hidden"]), select');
                    const targetField = targetCells[col];
                    
                    if (!targetField) continue;
                    
                    // Copy value based on field type
                    if (targetField.tagName === 'INPUT') {
                        if (targetField.type === 'checkbox') {
                            targetField.checked = sourceValue;
                            const hiddenInput = targetField.closest('td').querySelector('input[type="hidden"]');
                            if (hiddenInput) {
                                hiddenInput.value = sourceValue ? '0' : '0';
                            }
                        } else {
                            targetField.value = sourceValue;
                        }
                    } else if (targetField.tagName === 'SELECT') {
                        targetField.value = sourceValue;
                        targetField.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }
        } else {
            // Original behavior: copy only to the cell directly below
            if (row === 0) return; // Can't copy down from first row
            
            const currentRowEl = rows[row];
            if (!currentRowEl) return;
            
            const currentCells = currentRowEl.querySelectorAll('input:not([type="hidden"]), select');
            const currentField = currentCells[col];
            
            if (!currentField) return;
            
            // Copy value based on field type
            if (currentField.tagName === 'INPUT') {
                if (currentField.type === 'checkbox') {
                    currentField.checked = sourceValue;
                    const hiddenInput = currentField.closest('td').querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.value = sourceValue ? '0' : '0';
                    }
                } else {
                    currentField.value = sourceValue;
                }
            } else if (currentField.tagName === 'SELECT') {
                currentField.value = sourceValue;
                currentField.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }
    
    // Copy down for entire column (from current cell down to end or selected range)
    function copyDownColumn() {
        if (!currentCell) return;
        
        const startRow = parseInt(currentCell.dataset.row);
        const col = parseInt(currentCell.dataset.col);
        
        // Can't copy down from first row
        if (startRow === 0) {
            alert('Cannot copy down from the first row');
            return;
        }
        
        const rows = Array.from(body.querySelectorAll('tr.item-row'));
        const sourceRow = rows[startRow - 1];
        
        if (!sourceRow) return;
        
        const sourceCells = sourceRow.querySelectorAll('input:not([type="hidden"]), select');
        const sourceField = sourceCells[col];
        
        if (!sourceField) return;
        
        // Copy to all rows from current row to the end
        for (let i = startRow; i < rows.length; i++) {
            const targetRow = rows[i];
            const targetCells = targetRow.querySelectorAll('input:not([type="hidden"]), select');
            const targetField = targetCells[col];
            
            if (!targetField) continue;
            
            // Copy value based on field type
            if (targetField.tagName === 'INPUT') {
                if (targetField.type === 'checkbox') {
                    targetField.checked = sourceField.checked;
                    const hiddenInput = targetField.closest('td').querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.value = sourceField.checked ? '0' : '0';
                    }
                } else {
                    targetField.value = sourceField.value;
                }
            } else if (targetField.tagName === 'SELECT') {
                targetField.value = sourceField.value;
                targetField.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    // Keyboard navigation
    body.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
            currentCell = e.target;
            
            // Copy down shortcut (Ctrl+D or Cmd+D)
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                // If Shift is also pressed, copy down entire column
                if (e.shiftKey) {
                    copyDownColumn();
                } else {
                    copyDown();
                }
                return;
            }
            
            if (e.key === 'Tab') {
                e.preventDefault();
                const row = parseInt(currentCell.dataset.row);
                const col = parseInt(currentCell.dataset.col);
                const direction = e.shiftKey ? 'left' : 'right';
                
                if (direction === 'right') {
                    if (col < 9) {
                        navigateToCell(row, col + 1);
                    } else if (row < body.querySelectorAll('tr').length - 1) {
                        navigateToCell(row + 1, 0);
                    }
                } else {
                    if (col > 0) {
                        navigateToCell(row, col - 1);
                    } else if (row > 0) {
                        navigateToCell(row - 1, 9);
                    }
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const row = parseInt(currentCell.dataset.row);
                navigateToCell(row + 1, parseInt(currentCell.dataset.col), 'down');
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const row = parseInt(currentCell.dataset.row);
                navigateToCell(row + 1, parseInt(currentCell.dataset.col), 'down');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const row = parseInt(currentCell.dataset.row);
                if (row > 0) navigateToCell(row - 1, parseInt(currentCell.dataset.col));
            } else if (e.key === 'ArrowLeft') {
                if (e.target.selectionStart === 0) {
                    e.preventDefault();
                    const row = parseInt(currentCell.dataset.row);
                    navigateToCell(row, parseInt(currentCell.dataset.col) - 1);
                }
            } else if (e.key === 'ArrowRight') {
                if (e.target.selectionStart === e.target.value.length) {
                    e.preventDefault();
                    const row = parseInt(currentCell.dataset.row);
                    navigateToCell(row, parseInt(currentCell.dataset.col) + 1);
                }
            }
        }
    });

    // Focus tracking
    body.addEventListener('focusin', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
            currentCell = e.target;
            e.target.closest('tr').style.backgroundColor = '#eff6ff';
        }
    });

    body.addEventListener('focusout', (e) => {
        if (e.target.closest('tr')) {
            const row = e.target.closest('tr');
            const rowIndex = Array.from(body.querySelectorAll('tr.item-row')).indexOf(row);
            row.style.backgroundColor = rowIndex % 2 === 0 ? '' : '#f9fafb';
        }
    });

    // Context menu functionality
    const contextMenu = document.getElementById('contextMenu');
    const copyDownMenuItem = document.getElementById('copyDownMenuItem');
    const copyDownSingleMenuItem = document.getElementById('copyDownSingleMenuItem');
    let contextMenuCell = null;

    // Right-click handler
    body.addEventListener('contextmenu', (e) => {
        const target = e.target;
        if (target.tagName === 'INPUT' || target.tagName === 'SELECT') {
            e.preventDefault();
            contextMenuCell = target;
            currentCell = target;
            
            // Position context menu
            contextMenu.style.display = 'block';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';
            
            // Show context menu items
            const row = parseInt(target.dataset.row);
            // Always show "Copy Down to All Filled Rows" (works even from first row)
            copyDownMenuItem.style.display = 'flex';
            // Only show "Copy Down (Single Row)" if not first row
            if (row === 0) {
                copyDownSingleMenuItem.style.display = 'none';
            } else {
                copyDownSingleMenuItem.style.display = 'flex';
            }
        }
    });

    // Hide context menu when clicking elsewhere
    document.addEventListener('click', (e) => {
        if (!contextMenu.contains(e.target)) {
            contextMenu.style.display = 'none';
            contextMenuCell = null;
        }
    });

    // Context menu item handlers
    copyDownMenuItem.addEventListener('click', () => {
        if (contextMenuCell) {
            copyDown(contextMenuCell, true); // Copy to all filled rows
            contextMenu.style.display = 'none';
            contextMenuCell = null;
        }
    });

    copyDownSingleMenuItem.addEventListener('click', () => {
        if (contextMenuCell) {
            copyDown(contextMenuCell, false); // Copy to single row below
            contextMenu.style.display = 'none';
            contextMenuCell = null;
        }
    });

    // Add rows function with maximum limit
    const MAX_ROWS = 500;
    
    // Paste handler (defined after MAX_ROWS)
    body.addEventListener('paste', handlePaste);
    
    function addRows(count = 10) {
        const existingRows = body.querySelectorAll('tr.item-row').length;
        const currentRowCount = existingRows;
        
        // Check maximum limit
        if (currentRowCount >= MAX_ROWS) {
            alert(`Maximum limit of ${MAX_ROWS} rows reached. Cannot add more rows.`);
            return;
        }
        
        // Adjust count if it would exceed maximum
        const remainingRows = MAX_ROWS - currentRowCount;
        if (count > remainingRows) {
            count = remainingRows;
            if (count > 0) {
                alert(`Adding ${count} rows (maximum limit is ${MAX_ROWS} rows).`);
            } else {
                alert(`Maximum limit of ${MAX_ROWS} rows reached.`);
                return;
            }
        }
        
        const baseRow = body.querySelector('tr.item-row');
        if (!baseRow) return;
        
        // Use DocumentFragment for better performance with large batches
        const fragment = document.createDocumentFragment();
        
        for (let i = 0; i < count; i++) {
            const newRow = baseRow.cloneNode(true);
            const newIndex = existingRows + i;
            
            // Update row number
            newRow.querySelector('td:first-child').textContent = newIndex + 1;
            
            // Update all inputs/selects
            newRow.querySelectorAll('input, select').forEach((el) => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/\[\d+\]/, `[${newIndex}]`));
                }
                
                if (el.dataset.row !== undefined) {
                    el.dataset.row = newIndex;
                }
                
                if (el.tagName === 'INPUT') {
                    if (el.type === 'checkbox') {
                        el.checked = true;
                    } else if (el.type === 'hidden') {
                        if (el.name.includes('is_active')) {
                            el.value = '0';
                        }
                    } else {
                        el.value = '';
                    }
                } else if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });
            
            fragment.appendChild(newRow);
        }
        
        // Append all rows at once for better performance
        body.appendChild(fragment);
        
        updateRowCount();
        
        // Show notification for large batches
        if (count >= 100) {
            console.log(`Added ${count} rows. Total rows: ${body.querySelectorAll('tr.item-row').length}`);
        }
    }

    // Update row number display
    function updateRowCount() {
        const count = body.querySelectorAll('tr.item-row').length;
        rowCountEl.textContent = count;
    }

    // Add row index attributes
    body.querySelectorAll('tr.item-row').forEach((row, idx) => {
        row.setAttribute('data-row-index', idx);
    });

    // Add rows buttons
    const addRows10Btn = document.getElementById('addRows10');
    const addRows50Btn = document.getElementById('addRows50');
    const addRows100Btn = document.getElementById('addRows100');
    const addRows200Btn = document.getElementById('addRows200');
    
    if (addRows10Btn) addRows10Btn.addEventListener('click', () => addRows(10));
    if (addRows50Btn) addRows50Btn.addEventListener('click', () => addRows(50));
    if (addRows100Btn) addRows100Btn.addEventListener('click', () => addRows(100));
    if (addRows200Btn) addRows200Btn.addEventListener('click', () => addRows(200));

    // Copy down button
    const copyDownBtn = document.getElementById('copyDownBtn');
    copyDownBtn.addEventListener('click', () => {
        if (currentCell) {
            copyDown();
        } else {
            alert('Please select a cell first by clicking on it');
        }
    });

    // Copy down column button
    const copyDownColumnBtn = document.getElementById('copyDownColumnBtn');
    copyDownColumnBtn.addEventListener('click', () => {
        if (currentCell) {
            copyDownColumn();
        } else {
            alert('Please select a cell first by clicking on it');
        }
    });

    // Clear all button
    clearAllBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to clear all data?')) {
            body.querySelectorAll('input:not([type="hidden"]), select').forEach(el => {
                if (el.type === 'checkbox') {
                    el.checked = true;
                } else if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                } else {
                    el.value = '';
                }
            });
        }
    });

    // Collect all form data and submit as JSON to bypass max_input_vars limit
    function collectFormData() {
        const items = [];
        // Get all rows - use both methods to ensure we get all rows
        const rows = Array.from(body.querySelectorAll('tr.item-row'));
        
        console.log('Total rows found:', rows.length);
        
        rows.forEach((row, index) => {
            // Try multiple selectors to find item_name input
            const itemNameInput = row.querySelector('input[name*="[item_name]"]') || 
                                 row.querySelector('input[data-col="0"]') ||
                                 row.querySelectorAll('input[type="text"]')[0];
            
            if (!itemNameInput) {
                console.warn('Row', index, 'has no item_name input');
                return;
            }
            
            const itemName = itemNameInput.value ? itemNameInput.value.trim() : '';
            
            // Only include rows with item names
            if (!itemName) {
                return;
            }
            
            // Helper function to get value or null
            const getValue = (selector, isNumeric = false) => {
                // Try multiple selector strategies
                let el = row.querySelector(selector);
                if (!el) {
                    // Try by data-col attribute
                    const colMatch = selector.match(/data-col="(\d+)"/);
                    if (colMatch) {
                        const colIndex = parseInt(colMatch[1]);
                        const inputs = row.querySelectorAll('input, select');
                        el = inputs[colIndex];
                    }
                }
                if (!el) return null;
                const value = el.value ? el.value.trim() : '';
                if (!value) return null;
                if (isNumeric) {
                    const num = parseFloat(value);
                    return isNaN(num) ? null : num;
                }
                return value;
            };
            
            // Helper function to get select value or null
            const getSelectValue = (selector) => {
                let el = row.querySelector(selector);
                if (!el) {
                    // Try by data-col attribute
                    const colMatch = selector.match(/data-col="(\d+)"/);
                    if (colMatch) {
                        const colIndex = parseInt(colMatch[1]);
                        const selects = row.querySelectorAll('select');
                        // Find select at approximate column index
                        selects.forEach(sel => {
                            if (parseInt(sel.dataset.col) === colIndex) {
                                el = sel;
                            }
                        });
                    }
                }
                if (!el || !el.value) return null;
                return el.value.trim() || null;
            };
            
            // Get all inputs and selects in order
            const allInputs = row.querySelectorAll('input:not([type="hidden"]), select');
            const inputsArray = Array.from(allInputs);
            
            // Build item object by finding elements more reliably
            const item = {
                item_name: itemName,
                item_number: (() => {
                    const el = row.querySelector('input[name*="[item_number]"]') || inputsArray[1];
                    return el && el.value ? el.value.trim() : null;
                })(),
                item_type: (() => {
                    const el = row.querySelector('select[name*="[item_type]"]') || inputsArray[2];
                    return el && el.value ? el.value : '';
                })(),
                sales_price: (() => {
                    const el = row.querySelector('input[name*="[sales_price]"]') || inputsArray[3];
                    if (!el || !el.value) return null;
                    const num = parseFloat(el.value);
                    return isNaN(num) ? null : num;
                })(),
                cost: (() => {
                    const el = row.querySelector('input[name*="[cost]"]') || inputsArray[4];
                    if (!el || !el.value) return null;
                    const num = parseFloat(el.value);
                    return isNaN(num) ? null : num;
                })(),
                income_account_id: (() => {
                    const el = row.querySelector('select[name*="[income_account_id]"]') || inputsArray[5];
                    return el && el.value ? el.value : null;
                })(),
                cogs_account_id: (() => {
                    const el = row.querySelector('select[name*="[cogs_account_id]"]') || inputsArray[6];
                    return el && el.value ? el.value : null;
                })(),
                asset_account_id: (() => {
                    const el = row.querySelector('select[name*="[asset_account_id]"]') || inputsArray[7];
                    return el && el.value ? el.value : null;
                })(),
                preferred_vendor_id: (() => {
                    const el = row.querySelector('select[name*="[preferred_vendor_id]"]') || inputsArray[8];
                    return el && el.value ? el.value : null;
                })(),
                is_active: (() => {
                    const checkbox = row.querySelector('input[name*="[is_active]"]:not([type="hidden"])') || inputsArray[9];
                    return checkbox && checkbox.checked ? '1' : '0';
                })()
            };
            
            items.push(item);
        });
        
        console.log('Items collected:', items.length);
        console.log('Total rows in DOM:', rows.length);
        console.log('Rows with item names:', items.length);
        if (items.length > 0) {
            console.log('First item:', items[0]);
            console.log('Last item:', items[items.length - 1]);
        }
        
        // Debug: Check how many rows have item names
        let rowsWithNames = 0;
        rows.forEach((row, index) => {
            const itemNameInput = row.querySelector('input[name*="[item_name]"]') || 
                                 row.querySelector('input[data-col="0"]') ||
                                 row.querySelectorAll('input[type="text"]')[0];
            if (itemNameInput && itemNameInput.value && itemNameInput.value.trim()) {
                rowsWithNames++;
            }
        });
        console.log('Rows with non-empty item names:', rowsWithNames);
        
        return items;
    }

    // Form submission handler - use AJAX to submit JSON only
    const bulkItemsForm = document.getElementById('bulkItemsForm');
    bulkItemsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const items = collectFormData();
        
        console.log('=== FORM SUBMISSION ===');
        console.log('Collected items:', items.length);
        const totalRows = body.querySelectorAll('tr.item-row').length;
        console.log('Total rows in table:', totalRows);
        
        if (items.length === 0) {
            alert('Please enter at least one item with a name.');
            return;
        }
        
        // Show summary
        if (items.length < totalRows * 0.8 && totalRows > 50) {
            console.warn(`Warning: Only ${items.length} items with names found out of ${totalRows} total rows.`);
            console.warn('This might indicate that some rows are empty or the paste operation did not complete.');
        }
        
        // Show loading state
        const submitBtn = bulkItemsForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving ' + items.length + ' items...';
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        // Submit via AJAX with JSON data only
        fetch(bulkItemsForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                '_token': csrfToken,
                'items_json': JSON.stringify(items)
            })
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        if (data.success) {
                            // Show success message and redirect
                            window.location.href = data.redirect || '{{ route("items.web.index") }}';
                        } else {
                            throw new Error(data.message || 'Failed to save items');
                        }
                    });
                } else {
                    // HTML response - might be a redirect or error page
                    return response.text().then(html => {
                        // Check if it's a redirect (Laravel redirects return HTML)
                        if (html.includes('Redirecting') || html.includes('window.location')) {
                            // Extract redirect URL if present
                            const urlMatch = html.match(/window\.location\s*=\s*['"]([^'"]+)['"]/);
                            if (urlMatch) {
                                window.location.href = urlMatch[1];
                            } else {
                                window.location.href = '{{ route("items.web.index") }}';
                            }
                        } else if (html.includes('<!DOCTYPE') || html.includes('<html')) {
                            // Error page - try to extract error
                            const errorMatch = html.match(/<div[^>]*class="[^"]*error[^"]*"[^>]*>([^<]+)<\/div>/i) ||
                                             html.match(/<li[^>]*>([^<]+)<\/li>/);
                            const errorMsg = errorMatch ? errorMatch[1] : 'An error occurred while saving items';
                            throw new Error(errorMsg);
                        } else {
                            // Success - redirect
                            window.location.href = '{{ route("items.web.index") }}';
                        }
                    });
                }
            } else {
                return response.text().then(html => {
                    // Try to extract error from HTML
                    const errorMatch = html.match(/<div[^>]*class="[^"]*error[^"]*"[^>]*>([^<]+)<\/div>/i) ||
                                       html.match(/<li[^>]*>([^<]+)<\/li>/);
                    const errorMsg = errorMatch ? errorMatch[1] : 'Failed to save items (HTTP ' + response.status + ')';
                    throw new Error(errorMsg);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving items: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Ctrl+S to save
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            bulkItemsForm.dispatchEvent(new Event('submit'));
        }
    });
});
</script>
@endsection
