@extends('layouts.modern')

@section('title', 'Import Items from CSV')
@section('breadcrumb', 'Import Items from CSV')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Import Items from CSV</h1>
            <p class="text-sm text-gray-500 mt-1">Upload a CSV file to import multiple items at once</p>
        </div>

        <!-- Instructions Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-blue-900 mb-3">CSV Format Instructions</h2>
            <div class="space-y-2 text-sm text-blue-800">
                <p><strong>Required Columns:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><strong>Item Name</strong> (required) - The name of the item</li>
                    <li><strong>Item Type</strong> (required) - Service, Inventory Part, Inventory Assembly, Non-Inventory Part, Other Charge, Discount, Group, or Payment</li>
                </ul>
                <p class="mt-3"><strong>Optional Columns:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><strong>Item Number</strong> - SKU or item code</li>
                    <li><strong>Sales Price</strong> - Selling price</li>
                    <li><strong>Cost</strong> - Cost price</li>
                    <li><strong>Income Account</strong> - Income account name</li>
                    <li><strong>COGS Account</strong> - COGS account name</li>
                    <li><strong>Asset Account</strong> - Asset account name</li>
                    <li><strong>Vendor</strong> or <strong>Preferred Vendor</strong> - Supplier name</li>
                    <li><strong>Active</strong> - Yes/No or 1/0 (default: Yes)</li>
                </ul>
                <div class="mt-4 p-3 bg-white rounded border border-blue-300">
                    <p class="font-semibold mb-2">Example CSV Format:</p>
                    <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">Item Name,Item Number,Type,Sales Price,Cost,Income Account,COGS Account,Asset Account,Vendor,Active
Pen,SKU001,Inventory Part,10.00,6.00,Sales,Cost of Goods Sold,Inventory,ABC Supplier,Yes
Notebook,SKU002,Inventory Part,25.00,15.00,Sales,Cost of Goods Sold,Inventory,ABC Supplier,Yes</pre>
                </div>
            </div>
        </div>

        <!-- Import Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('items.web.csv-import-store') }}" method="POST" enctype="multipart/form-data" id="csvImportForm">
                @csrf
                
                <div class="p-6 space-y-6">
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('import_errors'))
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                            <p class="font-semibold mb-2">Import Warnings:</p>
                            <ul class="list-disc pl-5 max-h-60 overflow-y-auto">
                                @foreach (session('import_errors') as $error)
                                    <li class="text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- File Upload -->
                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                            CSV File <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="csv_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV, TXT up to 10MB</p>
                                <p id="fileName" class="text-sm text-gray-900 mt-2"></p>
                            </div>
                        </div>
                        @error('csv_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Import Options</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="skip_duplicates" value="1" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Skip duplicate item numbers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="update_existing" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Update existing items (if item number matches)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('items.web.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import Items
                    </button>
                </div>
            </form>
        </div>

        <!-- Download Sample CSV -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Need a sample CSV file?</h3>
                    <p class="text-sm text-gray-500 mt-1">Download a sample CSV template to see the correct format</p>
                </div>
                <button type="button" id="downloadSampleBtn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Sample CSV
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('csv_file');
    const fileName = document.getElementById('fileName');
    const downloadSampleBtn = document.getElementById('downloadSampleBtn');
    const form = document.getElementById('csvImportForm');

    // Show selected file name
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            fileName.textContent = 'Selected: ' + e.target.files[0].name;
            fileName.className = 'text-sm text-green-600 font-medium mt-2';
        } else {
            fileName.textContent = '';
        }
    });

    // Drag and drop functionality
    const dropZone = fileInput.closest('.border-dashed');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-400', 'bg-blue-50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        }, false);
    });

    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            fileName.textContent = 'Selected: ' + files[0].name;
            fileName.className = 'text-sm text-green-600 font-medium mt-2';
        }
    }, false);

    // Download sample CSV
    downloadSampleBtn.addEventListener('click', function() {
        const csvContent = `Item Name,Item Number,Type,Sales Price,Cost,Income Account,COGS Account,Asset Account,Vendor,Active
Pen,SKU001,Inventory Part,10.00,6.00,Sales,Cost of Goods Sold,Inventory,ABC Supplier,Yes
Notebook,SKU002,Inventory Part,25.00,15.00,Sales,Cost of Goods Sold,Inventory,ABC Supplier,Yes
Pencil,SKU003,Inventory Part,5.00,3.00,Sales,Cost of Goods Sold,Inventory,ABC Supplier,Yes
Consulting Service,SVC001,Service,100.00,0.00,Service Revenue,,,Yes
Office Supplies,SUP001,Non-Inventory Part,50.00,30.00,Sales,Office Supplies Expense,,,Yes`;

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'items_import_sample.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Please select a CSV file to import.');
            return false;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Importing...';
    });
});
</script>
@endpush
@endsection

