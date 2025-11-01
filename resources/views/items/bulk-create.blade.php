@extends('layouts.modern')

@section('content')
<div class="bg-gray-50 fixed-page-container">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-4 py-6 fixed-page-wrapper">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 page-header-fixed">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bulk Create Items - Spreadsheet View</h1>
                <p class="mt-1 text-sm text-gray-600">Copy and paste from Excel/Google Sheets. Use Tab to navigate between cells.</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" id="addRows" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                    </svg>
                    Add 10 Rows
                </button>
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
                            @for($i = 0; $i < 20; $i++)
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
                    <span id="rowCount">20</span> rows available
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
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('spreadsheetTable');
    const body = document.getElementById('itemsBody');
    const addRowsBtn = document.getElementById('addRows');
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
        const rows = pasteData.split(/\r\n|\n|\r/).filter(row => row.trim());
        
        if (!rows.length) return;

        const currentRow = parseInt(currentCell.closest('tr').querySelector('td:first-child').textContent) - 1;
        const currentCol = parseInt(currentCell.dataset.col);

        rows.forEach((row, rowOffset) => {
            const cols = row.split('\t');
            cols.forEach((value, colOffset) => {
                const targetRow = currentRow + rowOffset;
                const targetCol = currentCol + colOffset;
                
                if (targetCol > 9) return; // Max 10 columns (0-9)

                const rowEl = body.querySelector(`tr[data-row-index="${targetRow}"]`) || 
                             Array.from(body.querySelectorAll('tr.item-row'))[targetRow];
                
                if (!rowEl) {
                    addRows(10);
                    return;
                }

                const cells = rowEl.querySelectorAll('input, select');
                const targetCell = cells[targetCol];

                if (!targetCell) return;

                const cleanValue = value.trim();

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
                        targetCell.closest('td').querySelector('input[type="hidden"]').value = isActive ? '0' : '0';
                        break;
                }
            });
        });

        // Move to next cell after paste
        navigateToCell(currentRow + rows.length, currentCol);
    }

    // Tab navigation
    function navigateToCell(row, col, direction = 'right') {
        const rows = Array.from(body.querySelectorAll('tr.item-row'));
        if (row < 0 || row >= rows.length) {
            if (direction === 'down') {
                addRows(10);
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

    // Keyboard navigation
    body.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
            currentCell = e.target;
            
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

    // Paste handler
    body.addEventListener('paste', handlePaste);

    // Add rows function
    function addRows(count = 10) {
        const existingRows = body.querySelectorAll('tr.item-row').length;
        const baseRow = body.querySelector('tr.item-row');
        
        for (let i = 0; i < count; i++) {
            const newRow = baseRow.cloneNode(true);
            const newIndex = existingRows + i;
            
            // Update row number
            newRow.querySelector('td:first-child').textContent = newIndex + 1;
            
            // Update all inputs/selects
            newRow.querySelectorAll('input, select').forEach((el, idx) => {
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
            
            body.appendChild(newRow);
        }
        
        updateRowCount();
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

    // Add rows button
    addRowsBtn.addEventListener('click', () => addRows(10));

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

    // Ctrl+S to save
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('bulkItemsForm').submit();
        }
    });
});
</script>
@endsection
