<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .quotation-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .quotation-title {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .quotation-number {
            font-size: 18px;
            color: #6b7280;
        }
        .clear {
            clear: both;
        }
        .customer-section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }
        .customer-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
        }
        .quotation-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .quotation-details-left, .quotation-details-right {
            width: 48%;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            color: #374151;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            border: 1px solid #d1d5db;
            font-weight: bold;
            color: #374151;
        }
        .items-table td {
            padding: 12px;
            border: 1px solid #d1d5db;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-left: auto;
            width: 300px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
        }
        .totals-table .label {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .totals-table .total-row {
            background-color: #1f2937;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        .notes-section {
            margin-top: 30px;
        }
        .notes-content {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-line;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-accepted { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-expired { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1 style="margin: 0; font-size: 24px; color: #1f2937;">{{ config('app.name', 'Your Company') }}</h1>
            <p style="margin: 5px 0; color: #6b7280;">Your Company Address</p>
            <p style="margin: 5px 0; color: #6b7280;">Phone: (555) 123-4567</p>
            <p style="margin: 5px 0; color: #6b7280;">Email: info@yourcompany.com</p>
        </div>
        <div class="quotation-info">
            <div class="quotation-title">QUOTATION</div>
            <div class="quotation-number">#{{ $quotation->quotation_number }}</div>
            <div style="margin-top: 10px;">
                <span class="status-badge status-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="section-title">Bill To:</div>
        <div class="customer-info">
            <strong>{{ $quotation->customer->name }}</strong><br>
            @if($quotation->customer->email)
                {{ $quotation->customer->email }}<br>
            @endif
            @if($quotation->customer->phone)
                {{ $quotation->customer->phone }}<br>
            @endif
            @if($quotation->billing_address)
                <div style="margin-top: 10px; white-space: pre-line;">{{ $quotation->billing_address }}</div>
            @endif
        </div>
    </div>

    <!-- Quotation Details -->
    <div class="quotation-details">
        <div class="quotation-details-left">
            <div class="detail-item">
                <span class="detail-label">Quotation Date:</span> {{ $quotation->quotation_date->format('M d, Y') }}
            </div>
            @if($quotation->valid_until)
                <div class="detail-item">
                    <span class="detail-label">Valid Until:</span> {{ $quotation->valid_until->format('M d, Y') }}
                </div>
            @endif
            @if($quotation->payment_terms)
                <div class="detail-item">
                    <span class="detail-label">Payment Terms:</span> {{ $quotation->payment_terms }}
                </div>
            @endif
        </div>
        <div class="quotation-details-right">
            @if($quotation->shipping_method)
                <div class="detail-item">
                    <span class="detail-label">Shipping Method:</span> {{ $quotation->shipping_method }}
                </div>
            @endif
            @if($quotation->shipping_address)
                <div class="detail-item">
                    <span class="detail-label">Shipping Address:</span><br>
                    <div style="white-space: pre-line; margin-top: 5px;">{{ $quotation->shipping_address }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Item</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 10%;" class="text-right">Unit Price</th>
                <th style="width: 8%;" class="text-center">Tax %</th>
                <th style="width: 8%;" class="text-center">Disc %</th>
                <th style="width: 12%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->lineItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->item->item_name ?? 'N/A' }}
                        @if($item->item && $item->item->item_number)
                            <br><small style="color: #6b7280;">({{ $item->item->item_number }})</small>
                        @endif
                    </td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ number_format($item->tax_rate, 2) }}%</td>
                    <td class="text-center">{{ number_format($item->discount_rate, 2) }}%</td>
                    <td class="text-right">${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="text-right">${{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tax:</td>
                <td class="text-right">${{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Discount:</td>
                <td class="text-right">-${{ number_format($quotation->discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Shipping:</td>
                <td class="text-right">${{ number_format($quotation->shipping_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">${{ number_format($quotation->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Notes and Terms -->
    @if($quotation->notes || $quotation->terms_conditions)
        <div class="notes-section">
            @if($quotation->notes)
                <div class="section-title">Notes:</div>
                <div class="notes-content">{{ $quotation->notes }}</div>
            @endif

            @if($quotation->terms_conditions)
                <div class="section-title" style="margin-top: 20px;">Terms & Conditions:</div>
                <div class="notes-content">{{ $quotation->terms_conditions }}</div>
            @endif
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This quotation is valid until {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'further notice' }}.</p>
        <p>Generated on {{ now()->format('M d, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
