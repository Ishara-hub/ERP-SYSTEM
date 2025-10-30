@extends('layouts.modern')

@section('title', 'Purchase Order Details')
@section('breadcrumb', 'Purchase Order Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $purchaseOrder->po_number }}</h1>
                <p class="mt-1 text-sm text-gray-600">Purchase order details and inventory receiving</p>
            </div>
            <div class="flex space-x-2">
                @if($purchaseOrder->status === 'draft')
                    <a href="{{ route('purchase-orders.web.edit', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit PO
                    </a>
                @endif
                @if(in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial']))
                    <a href="{{ route('purchase-orders.web.receive', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Receive Items
                    </a>
                @endif
                <a href="{{ route('purchase-orders.web.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Purchase Order Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Purchase Order Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Order details and supplier information.</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-4">
                    <!-- First Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">PO Number</label>
                            <div class="text-sm text-gray-900">{{ $purchaseOrder->po_number }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Supplier</label>
                            <div class="text-sm text-gray-900">{{ $purchaseOrder->supplier->name }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Order Date</label>
                            <div class="text-sm text-gray-900">{{ $purchaseOrder->order_date->format('M d, Y') }}</div>
                        </div>
                        @if($purchaseOrder->expected_delivery_date)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Expected Delivery</label>
                            <div class="text-sm text-gray-900">{{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</div>
                        </div>
                        @else
                        <div></div>
                        @endif
                    </div>
                    
                    <!-- Second Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if($purchaseOrder->actual_delivery_date)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Actual Delivery</label>
                            <div class="text-sm text-gray-900">{{ $purchaseOrder->actual_delivery_date->format('M d, Y') }}</div>
                        </div>
                        @else
                        <div></div>
                        @endif
                        @if($purchaseOrder->reference)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Reference</label>
                            <div class="text-sm text-gray-900">{{ $purchaseOrder->reference }}</div>
                        </div>
                        @else
                        <div></div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                            <div class="text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $purchaseOrder->status_color }}-100 text-{{ $purchaseOrder->status_color }}-800">
                                    {{ ucfirst($purchaseOrder->status) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Progress</label>
                            <div class="text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $purchaseOrder->progress_percentage }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $purchaseOrder->progress_percentage }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Items</h3>
                    <p class="mt-1 text-sm text-gray-500">Ordered items and receiving status.</p>
                </div>
                <div class="border-t border-gray-200 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">{{ $item->item->item_name }}</div>
                                        @if($item->description)
                                            <div class="text-xs text-gray-500">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">{{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure ?: 'pcs' }}</td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">${{ number_format($item->amount, 2) }}</td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">{{ number_format($item->received_quantity, 2) }}</td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        @if($item->is_fully_received)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Complete</span>
                                        @elseif($item->is_partially_received)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Partial</span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Financial Summary</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-4">
                    <!-- First Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Subtotal</label>
                            <div class="text-sm font-medium text-gray-900">${{ number_format($purchaseOrder->subtotal, 2) }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tax Amount</label>
                            <div class="text-sm font-medium text-gray-900">${{ number_format($purchaseOrder->tax_amount, 2) }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Shipping</label>
                            <div class="text-sm font-medium text-gray-900">${{ number_format($purchaseOrder->shipping_amount, 2) }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Discount</label>
                            <div class="text-sm font-medium text-gray-900">-${{ number_format($purchaseOrder->discount_amount, 2) }}</div>
                        </div>
                    </div>
                    
                    <!-- Second Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-blue-600 mb-1">Total Amount</label>
                            <div class="text-lg font-bold text-blue-600">${{ number_format($purchaseOrder->total_amount, 2) }}</div>
                        </div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Payment Information</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-4">
                    <!-- First Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Payment Status</label>
                            <div class="text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $purchaseOrder->payment_status_color }}-100 text-{{ $purchaseOrder->payment_status_color }}-800">
                                    {{ ucfirst($purchaseOrder->payment_status) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Total Payments</label>
                            <div class="text-sm font-medium text-gray-900">${{ number_format($purchaseOrder->total_payments, 2) }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Balance Due</label>
                            <div class="text-sm font-bold text-red-600">${{ number_format($purchaseOrder->balance_due, 2) }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Payment Progress</label>
                            <div class="text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $purchaseOrder->total_amount > 0 ? ($purchaseOrder->total_payments / $purchaseOrder->total_amount) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $purchaseOrder->total_amount > 0 ? number_format(($purchaseOrder->total_payments / $purchaseOrder->total_amount) * 100, 0) : 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Actions -->
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('purchase-orders.web.payments.create', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Record Payment
                        </a>
                        <a href="{{ route('payments.web.index') }}?search={{ $purchaseOrder->po_number }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View All Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    <div class="mt-3 space-y-2">
                        @if($purchaseOrder->status === 'draft')
                            <a href="{{ route('purchase-orders.web.edit', $purchaseOrder) }}" class="w-full inline-flex justify-center items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit PO
                            </a>
                        @endif
                        
                        @if(in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial']))
                            <a href="{{ route('purchase-orders.web.receive', $purchaseOrder) }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded text-gray-700 bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Receive Items
                            </a>
                        @endif

                        <form method="POST" action="{{ route('purchase-orders.web.update-status', $purchaseOrder) }}" class="w-full">
                            @csrf
                            <select name="status" onchange="this.form.submit()" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="draft" {{ $purchaseOrder->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ $purchaseOrder->status === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="confirmed" {{ $purchaseOrder->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="partial" {{ $purchaseOrder->status === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="received" {{ $purchaseOrder->status === 'received' ? 'selected' : '' }}>Received</option>
                                <option value="cancelled" {{ $purchaseOrder->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Supplier Information -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Supplier Info</h3>
                    <div class="mt-3 space-y-2">
                        <div>
                            <span class="text-xs font-medium text-gray-500">Name:</span>
                            <span class="text-xs text-gray-900 ml-2">{{ $purchaseOrder->supplier->name }}</span>
                        </div>
                        @if($purchaseOrder->supplier->company_name)
                        <div>
                            <span class="text-xs font-medium text-gray-500">Company:</span>
                            <span class="text-xs text-gray-900 ml-2">{{ $purchaseOrder->supplier->company_name }}</span>
                        </div>
                        @endif
                        <div>
                            <span class="text-xs font-medium text-gray-500">Email:</span>
                            <a href="mailto:{{ $purchaseOrder->supplier->email }}" class="text-xs text-blue-600 hover:text-blue-500 ml-2">{{ $purchaseOrder->supplier->email }}</a>
                        </div>
                        @if($purchaseOrder->supplier->phone)
                        <div>
                            <span class="text-xs font-medium text-gray-500">Phone:</span>
                            <a href="tel:{{ $purchaseOrder->supplier->phone }}" class="text-xs text-blue-600 hover:text-blue-500 ml-2">{{ $purchaseOrder->supplier->phone }}</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Receiving Form (Hidden by default) -->
    <div id="receive-form" class="mt-6 hidden">
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-4 py-4">
                <h3 class="text-lg font-medium text-gray-900">Receive Inventory</h3>
                <p class="mt-1 text-sm text-gray-500">Record received quantities for each item.</p>
            </div>
            <form method="POST" action="{{ route('purchase-orders.web.receive-inventory', $purchaseOrder) }}" class="px-4 py-4">
                @csrf
                <div class="space-y-3">
                    @foreach($purchaseOrder->items as $item)
                        @if(!$item->is_fully_received)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-medium text-gray-900">{{ $item->item->item_name }}</h4>
                                <span class="text-xs text-gray-500">Remaining: {{ number_format($item->remaining_quantity, 2) }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Ordered</label>
                                    <input type="text" value="{{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure ?: 'pcs' }}" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Received</label>
                                    <input type="text" value="{{ number_format($item->received_quantity, 2) }} {{ $item->unit_of_measure ?: 'pcs' }}" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Now</label>
                                    <input type="number" 
                                           name="received_items[{{ $loop->index }}][received_quantity]" 
                                           step="0.01" 
                                           min="0" 
                                           max="{{ $item->remaining_quantity }}"
                                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <input type="hidden" name="received_items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="toggleReceiveForm()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Receive
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleReceiveForm() {
    const form = document.getElementById('receive-form');
    form.classList.toggle('hidden');
    
    // Scroll to form if showing
    if (!form.classList.contains('hidden')) {
        form.scrollIntoView({ behavior: 'smooth' });
    }
}

// Check if we should show the form (if URL has #receive)
if (window.location.hash === '#receive') {
    toggleReceiveForm();
}
</script>
@endsection
