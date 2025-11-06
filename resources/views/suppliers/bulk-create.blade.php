@extends('layouts.modern')

@section('content')
<div class="bg-gray-50 fixed-page-container">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-4 py-6 fixed-page-wrapper">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 page-header-fixed">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bulk Create Suppliers - Spreadsheet View</h1>
                <p class="mt-1 text-sm text-gray-600">Copy and paste from Excel/Google Sheets. Use Tab to navigate between cells.</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" id="addRows" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                    </svg>
                    Add 10 Rows
                </button>
                <a href="{{ route('suppliers.web.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300">
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

        <form method="POST" action="{{ route('suppliers.web.bulk-store') }}" id="bulkSuppliersForm">
            @csrf

            <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden">
                <div class="overflow-x-auto overflow-y-auto">
                    <table class="min-w-full border-collapse" id="spreadsheetTable" style="font-family: 'Courier New', monospace;">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr>
                                <th class="w-12 px-2 py-2 text-center text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">#</th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">A</span> Name <span class="text-red-500">*</span>
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">B</span> Company Name
                                </th>
                                <th class="min-w-[180px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">C</span> Contact Person
                                </th>
                                <th class="min-w-[200px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">D</span> Email
                                </th>
                                <th class="min-w-[150px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">E</span> Phone
                                </th>
                                <th class="min-w-[200px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">F</span> Address
                                </th>
                                <th class="min-w-[150px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">G</span> Website
                                </th>
                                <th class="min-w-[120px] px-2 py-2 text-left text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">H</span> Tax ID
                                </th>
                                <th class="min-w-[120px] px-2 py-2 text-center text-xs font-semibold text-gray-600 border border-gray-300 bg-gray-200">
                                    <span class="text-blue-600">I</span> Active
                                </th>
                            </tr>
                        </thead>
                        <tbody id="suppliersBody" class="bg-white">
                            @for($i = 0; $i < 100; $i++)
                            <tr class="supplier-row hover:bg-blue-50">
                                <td class="px-2 py-1 text-center text-xs text-gray-500 border border-gray-300 bg-gray-50 select-none">{{ $i + 1 }}</td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][name]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Supplier name" 
                                           data-col="0" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][company_name]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Company Name" 
                                           data-col="1" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][contact_person]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Contact Person" 
                                           data-col="2" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][email]" type="email" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="email@example.com" 
                                           data-col="3" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][phone]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Phone" 
                                           data-col="4" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][address]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Address" 
                                           data-col="5" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][website]" type="url" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="https://example.com" 
                                           data-col="6" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300">
                                    <input name="suppliers[{{ $i }}][tax_id]" type="text" 
                                           class="w-full px-2 py-1.5 border-0 outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 bg-transparent text-sm" 
                                           placeholder="Tax ID" 
                                           data-col="7" data-row="{{ $i }}" />
                                </td>
                                <td class="px-1 py-0 border border-gray-300 text-center">
                                    <input type="hidden" name="suppliers[{{ $i }}][is_active]" value="0" />
                                    <input type="checkbox" name="suppliers[{{ $i }}][is_active]" value="1" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer"
                                           checked
                                           data-col="8" data-row="{{ $i }}" />
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span id="rowCount">100</span> rows available
                </div>
                <div class="flex space-x-3">
                    <button type="button" id="clearAll" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                        Clear All
                    </button>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Suppliers (Ctrl+S)
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
    #bulkSuppliersForm {
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow: hidden;
    }
    
    /* Table container takes remaining space */
    #bulkSuppliersForm > div.bg-white {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    #bulkSuppliersForm > div.bg-white > div.overflow-x-auto {
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
    
    #spreadsheetTable tbody tr td input:focus {
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
    const body = document.getElementById('suppliersBody');
    const addRowsBtn = document.getElementById('addRows');
    const clearAllBtn = document.getElementById('clearAll');
    const rowCountEl = document.getElementById('rowCount');
    let currentCell = null;

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
                
                if (targetCol > 8) return; // Max 9 columns (0-8)

                const rowEl = body.querySelector(`tr[data-row-index="${targetRow}"]`) || 
                             Array.from(body.querySelectorAll('tr.supplier-row'))[targetRow];
                
                if (!rowEl) {
                    addRows(10);
                    return;
                }

                const cells = rowEl.querySelectorAll('input:not([type="hidden"])');
                const targetCell = cells[targetCol];

                if (!targetCell) return;

                const cleanValue = value.trim();

                // Handle different column types
                switch(targetCol) {
                    case 8: // Active
                        const activeValue = cleanValue.toLowerCase();
                        const isActive = ['yes', 'true', '1', '✓', 'x'].includes(activeValue) || activeValue === '';
                        targetCell.checked = isActive;
                        targetCell.closest('td').querySelector('input[type="hidden"]').value = isActive ? '0' : '0';
                        break;
                    default:
                        targetCell.value = cleanValue;
                        break;
                }
            });
        });

        // Move to next cell after paste
        navigateToCell(currentRow + rows.length, currentCol);
    }

    // Tab navigation
    function navigateToCell(row, col, direction = 'right') {
        const rows = Array.from(body.querySelectorAll('tr.supplier-row'));
        if (row < 0 || row >= rows.length) {
            if (direction === 'down') {
                addRows(10);
                setTimeout(() => navigateToCell(row, col), 100);
                return;
            }
            return;
        }

        const targetRow = rows[row];
        const cells = targetRow.querySelectorAll('input:not([type="hidden"])');
        
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
        if (e.target.tagName === 'INPUT') {
            currentCell = e.target;
            
            if (e.key === 'Tab') {
                e.preventDefault();
                const row = parseInt(currentCell.dataset.row);
                const col = parseInt(currentCell.dataset.col);
                const direction = e.shiftKey ? 'left' : 'right';
                
                if (direction === 'right') {
                    if (col < 8) {
                        navigateToCell(row, col + 1);
                    } else if (row < body.querySelectorAll('tr').length - 1) {
                        navigateToCell(row + 1, 0);
                    }
                } else {
                    if (col > 0) {
                        navigateToCell(row, col - 1);
                    } else if (row > 0) {
                        navigateToCell(row - 1, 8);
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
        if (e.target.tagName === 'INPUT') {
            currentCell = e.target;
            e.target.closest('tr').style.backgroundColor = '#eff6ff';
        }
    });

    body.addEventListener('focusout', (e) => {
        if (e.target.closest('tr')) {
            const row = e.target.closest('tr');
            const rowIndex = Array.from(body.querySelectorAll('tr.supplier-row')).indexOf(row);
            row.style.backgroundColor = rowIndex % 2 === 0 ? '' : '#f9fafb';
        }
    });

    // Paste handler
    body.addEventListener('paste', handlePaste);

    // Add rows function
    function addRows(count = 10) {
        const existingRows = body.querySelectorAll('tr.supplier-row').length;
        const baseRow = body.querySelector('tr.supplier-row');
        
        for (let i = 0; i < count; i++) {
            const newRow = baseRow.cloneNode(true);
            const newIndex = existingRows + i;
            
            // Update row number
            newRow.querySelector('td:first-child').textContent = newIndex + 1;
            
            // Update all inputs
            newRow.querySelectorAll('input').forEach((el) => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/\[\d+\]/, `[${newIndex}]`));
                }
                
                if (el.dataset.row !== undefined) {
                    el.dataset.row = newIndex;
                }
                
                if (el.type === 'checkbox') {
                    el.checked = true;
                } else if (el.type === 'hidden') {
                    if (el.name.includes('is_active')) {
                        el.value = '0';
                    }
                } else {
                    el.value = '';
                }
            });
            
            body.appendChild(newRow);
        }
        
        updateRowCount();
    }

    // Update row number display
    function updateRowCount() {
        const count = body.querySelectorAll('tr.supplier-row').length;
        rowCountEl.textContent = count;
    }

    // Add row index attributes
    body.querySelectorAll('tr.supplier-row').forEach((row, idx) => {
        row.setAttribute('data-row-index', idx);
    });

    // Add rows button
    addRowsBtn.addEventListener('click', () => addRows(10));

    // Clear all button
    clearAllBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to clear all data?')) {
            body.querySelectorAll('input:not([type="hidden"])').forEach(el => {
                if (el.type === 'checkbox') {
                    el.checked = true;
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
            document.getElementById('bulkSuppliersForm').submit();
        }
    });
});
</script>
@endsection
