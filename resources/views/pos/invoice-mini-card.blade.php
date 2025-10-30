<!-- Mini Invoice Card Component -->
<div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
    <div class="flex justify-between items-start mb-2">
        <div>
            <div class="font-medium text-gray-900 text-sm">{{ $invoice->invoice_no }}</div>
            <div class="text-xs text-gray-600">{{ $invoice->date->format('M d, Y') }}</div>
        </div>
        <div class="text-right">
            <div class="font-semibold text-gray-900 text-sm">${{ number_format($invoice->total_amount, 2) }}</div>
            <div class="text-xs px-2 py-1 rounded-full {{ 
                $invoice->status === 'paid' ? 'bg-green-100 text-green-800' :
                $invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                'bg-red-100 text-red-800'
            }}">
                {{ ucfirst($invoice->status) }}
            </div>
        </div>
    </div>
    
    <div class="text-xs text-gray-500 mb-2">
        {{ $invoice->lineItems->count() }} item{{ $invoice->lineItems->count() !== 1 ? 's' : '' }} • 
        Balance: ${{ number_format($invoice->balance_due, 2) }}
    </div>
    
    <div class="text-xs text-gray-600">
        @if($invoice->lineItems->count() > 0)
            @foreach($invoice->lineItems->take(2) as $item)
                <div class="truncate">{{ $item->item ? $item->item->item_name : $item->description }} ({{ $item->quantity }})</div>
            @endforeach
            @if($invoice->lineItems->count() > 2)
                <div class="text-gray-400">+{{ $invoice->lineItems->count() - 2 }} more items</div>
            @endif
        @endif
    </div>
</div>
