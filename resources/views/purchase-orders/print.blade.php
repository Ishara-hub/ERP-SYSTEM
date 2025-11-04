<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order #{{ $purchaseOrder->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 5px;
            background: #f5f7fa;
            color: #1f2937;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 0px;
            margin-bottom: 0px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-info {
            flex: 1;
        }
        .company-info h1 {
            font-size: 24px;
            color: #1e40af;
            font-weight: 400;
            margin-bottom: 5px;
        }
        .company-info p {
            font-size: 14px;
            color: #6b7280;
            margin: 2px 0;
        }
        .po-info {
            text-align: right;
        }
        .po-title {
            font-size: 14px;
            font-weight: 400;
            color: #1f2937;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .po-number {
            font-size: 14px;
            color: #3b82f6;
            font-weight: 400;
        }
        .status-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-draft { background-color: #f3f4f6; color: #374151; border: 2px solid #d1d5db; }
        .status-sent { background-color: #dbeafe; color: #1e40af; border: 2px solid #3b82f6; }
        .status-confirmed { background-color: #d1fae5; color: #065f46; border: 2px solid #10b981; }
        .status-partial { background-color: #fef3c7; color: #92400e; border: 2px solid #f59e0b; }
        .status-received { background-color: #d1fae5; color: #065f46; border: 2px solid #10b981; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; border: 2px solid #ef4444; }

        .supplier-section {
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 400;
            color: #1f2937;
            margin-bottom: 5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .supplier-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 5px;
            border-radius: 12px;
            border-left: 5px solid #3b82f6;
        }
        .supplier-info strong {
            font-size: 14px;
            color: #1e40af;
        }
        .supplier-info p {
            font-size: 12px;
            margin: 2px 0;
        }

        .po-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            margin-bottom: 10px;
        }
        .detail-item {
            margin-bottom: 5px;
            font-size: 13px;
        }
        .detail-label {
            font-weight: 400;
            color: #374151;
            display: inline-block;
            width: 80px;
        }
        .detail-value {
            color: #1f2937;
        }

        .items-section {
            margin-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .items-table th {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 5px 10px;
            text-align: left;
            border: 1px solid #1e40af;
            font-weight: 400;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 5px 10px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .items-table tr:hover {
            background-color: #eff6ff;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 250px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .totals-table td {
            padding: 5px 10px;
            border: 1px solid #e5e7eb;
        }
        .totals-table .label {
            background-color: #f3f4f6;
            font-weight: 400;
            color: #374151;
        }
        .totals-table .total-row {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            font-weight: 400;
            font-size: 16px;
        }
        .totals-table .total-row td {
            border-color: #1f2937;
        }

        .notes-section {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
        }
        .notes-content {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 5px;
            border-radius: 12px;
            white-space: pre-line;
            font-size: 13px;
            border-left: 5px solid #f59e0b;
        }

        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #6b7280;
            margin: 2px 0;
        }
        .footer .thank-you {
            font-size: 14px;
            font-weight: 400;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        
        .no-print {
            margin-top: 10px;
            text-align: center;
        }
        .no-print button {
            padding: 5px 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 400;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
            transition: all 0.3s;
        }
        .no-print button:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 5px;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
            .items-table tr {
                page-break-inside: avoid;
            }
            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ config('app.name', 'Your Company') }}</h1>
                <p>123 Business Street, Suite 100</p>
                <p>City, State ZIP Code</p>
                <p><strong>Phone:</strong> (555) 123-4567</p>
                <p><strong>Email:</strong> info@yourcompany.com</p>
            </div>
            <div class="po-info">
                <div class="po-title">PURCHASE ORDER</div>
                <div class="po-number">#{{ $purchaseOrder->po_number }}</div>
                <span class="status-badge status-{{ $purchaseOrder->status }}">{{ ucfirst($purchaseOrder->status) }}</span>
            </div>
        </div>

        <!-- Supplier Information -->
        <div class="supplier-section">
            <div class="section-title">Supplier Information</div>
            <div class="supplier-info">
                <strong>{{ $purchaseOrder->supplier->name }}</strong>
                @if($purchaseOrder->supplier->email)
                    <p><strong>Email:</strong> {{ $purchaseOrder->supplier->email }}</p>
                @endif
                @if($purchaseOrder->supplier->phone)
                    <p><strong>Phone:</strong> {{ $purchaseOrder->supplier->phone }}</p>
                @endif
                @if($purchaseOrder->billing_address)
                    <p style="margin-top: 15px;"><strong>Address:</strong><br>{{ nl2br($purchaseOrder->billing_address) }}</p>
                @endif
            </div>
        </div>

        <!-- Purchase Order Details -->
        <div class="po-details">
            <div>
                <div class="section-title">Order Details</div>
                <div class="detail-item">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value">{{ $purchaseOrder->order_date->format('F d, Y') }}</span>
                </div>
                @if($purchaseOrder->expected_delivery_date)
                    <div class="detail-item">
                        <span class="detail-label">Expected Delivery:</span>
                        <span class="detail-value">{{ $purchaseOrder->expected_delivery_date->format('F d, Y') }}</span>
                    </div>
                @endif
                @if($purchaseOrder->actual_delivery_date)
                    <div class="detail-item">
                        <span class="detail-label">Actual Delivery:</span>
                        <span class="detail-value">{{ $purchaseOrder->actual_delivery_date->format('F d, Y') }}</span>
                    </div>
                @endif
                @if($purchaseOrder->reference)
                    <div class="detail-item">
                        <span class="detail-label">Reference #:</span>
                        <span class="detail-value">{{ $purchaseOrder->reference }}</span>
                    </div>
                @endif
            </div>
            <div>
                <div class="section-title">Shipping & Terms</div>
                @if($purchaseOrder->shipping_address)
                    <div class="detail-item">
                        <span class="detail-label">Shipping To:</span>
                        <span class="detail-value">{{ nl2br($purchaseOrder->shipping_address) }}</span>
                    </div>
                @endif
                @if($purchaseOrder->terms)
                    <div class="detail-item">
                        <span class="detail-label">Payment Terms:</span>
                        <span class="detail-value">{{ $purchaseOrder->terms }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <div class="section-title">Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 25%;">Item</th>
                        <th style="width: 25%;">Description</th>
                        <th style="width: 10%;" class="text-center">Qty</th>
                        <th style="width: 12%;" class="text-right">Unit Price</th>
                        <th style="width: 8%;" class="text-center">Tax %</th>
                        <th style="width: 8%;" class="text-center">Recv.</th>
                        <th style="width: 15%;" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->item->item_name ?? 'N/A' }}</strong>
                                @if($item->item && $item->item->item_number)
                                    <br><small style="color: #6b7280;">({{ $item->item->item_number }})</small>
                                @endif
                            </td>
                            <td>{{ $item->description }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure }}</td>
                            <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ number_format($item->tax_rate, 2) }}%</td>
                            <td class="text-center">{{ number_format($item->received_quantity ?? 0, 2) }}</td>
                            <td class="text-right"><strong>${{ number_format($item->amount, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="text-right">${{ number_format($purchaseOrder->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Tax:</td>
                    <td class="text-right">${{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Shipping:</td>
                    <td class="text-right">${{ number_format($purchaseOrder->shipping_amount, 2) }}</td>
                </tr>
                @if($purchaseOrder->discount_amount > 0)
                <tr>
                    <td class="label">Discount:</td>
                    <td class="text-right">-${{ number_format($purchaseOrder->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td class="text-right">${{ number_format($purchaseOrder->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($purchaseOrder->notes)
            <div class="notes-section">
                <div class="section-title">Notes & Instructions</div>
                <div class="notes-content">{{ $purchaseOrder->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p class="thank-you">Thank you for your service!</p>
            <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
            <div class="no-print">
                <button onclick="window.print()">Print Purchase Order</button>
            </div>
        </div>
    </div>
</body>
</html>