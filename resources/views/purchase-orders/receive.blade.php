@extends('layouts.modern')

@section('title', 'Receive Items - Purchase Order')
@section('breadcrumb', 'Receive Items')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Receive Items</h1>
                <p class="mt-1 text-sm text-gray-600">Receive inventory for Purchase Order #{{ $purchaseOrder->po_number }}</p>
            </div>
            <a href="{{ route('purchase-orders.web.show', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Purchase Order
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Purchase Order Info -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Purchase Order</label>
                    <p class="text-sm font-medium text-gray-900">{{ $purchaseOrder->po_number }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Supplier</label>
                    <p class="text-sm font-medium text-gray-900">{{ $purchaseOrder->supplier->name }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Order Date</label>
                    <p class="text-sm font-medium text-gray-900">{{ $purchaseOrder->order_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Status</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $purchaseOrder->status_color }}">
                        {{ ucfirst($purchaseOrder->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Items Form -->
    <div class="bg-white rounded-lg shadow-sm border">
        <form action="{{ route('purchase-orders.web.receive-inventory', $purchaseOrder) }}" method="POST" class="p-6" id="receiveForm">
            @csrf
            
            <!-- Items to Receive -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Items to Receive</h3>
                
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                        Item
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Ordered
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Previously Received
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Remaining
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Receive Now
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Unit Cost
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseOrder->items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->item->item_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->item->item_number }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->quantity, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->received_quantity, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->remaining_quantity, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="number" 
                                               name="received_items[{{ $index }}][received_quantity]" 
                                               class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               min="0" 
                                               max="{{ $item->remaining_quantity }}" 
                                               step="0.01" 
                                               value="0">
                                        <input type="hidden" name="received_items[{{ $index }}][item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Receive Date -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="receive_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Receive Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="receive_date" 
                               id="receive_date"
                               value="{{ old('receive_date', date('Y-m-d')) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('receive_date') border-red-500 @enderror"
                               required>
                        @error('receive_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="Additional notes about the received items">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('purchase-orders.web.show', $purchaseOrder) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Receive Items
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.getElementById('receiveForm').addEventListener('submit', function(e) {
        const receiveInputs = document.querySelectorAll('input[name*="[received_quantity]"]');
        let hasReceivedItems = false;
        
        receiveInputs.forEach(input => {
            if (parseFloat(input.value) > 0) {
                hasReceivedItems = true;
            }
        });
        
        if (!hasReceivedItems) {
            e.preventDefault();
            alert('Please enter quantities for at least one item to receive.');
            return false;
        }
    });
});
</script>
@endsection
