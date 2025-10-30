<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .company-info h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
        }
        .company-info p {
            color: #6c757d;
            margin: 5px 0;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .invoice-info p {
            color: #6c757d;
            margin: 5px 0;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .bill-to, .ship-to {
            flex: 1;
            margin-right: 20px;
        }
        .bill-to h3, .ship-to h3 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .address {
            color: #6c757d;
            line-height: 1.5;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: 600;
        }
        .items-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 300px;
        }
        .totals-table td {
            padding: 8px 12px;
            border: none;
        }
        .totals-table .label {
            text-align: right;
            font-weight: 500;
        }
        .totals-table .amount {
            text-align: right;
            font-weight: 600;
        }
        .total-row {
            border-top: 2px solid #2c3e50;
            font-size: 18px;
            color: #2c3e50;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .additional-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .additional-info h4 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .additional-info p {
            margin: 5px 0;
            color: #6c757d;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>Your Company Name</h1>
                <p>123 Business Street</p>
                <p>City, State 12345</p>
                <p>Phone: (555) 123-4567</p>
                <p>Email: info@yourcompany.com</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_no }}</p>
                <p><strong>Date:</strong> {{ $invoice->date->format('M d, Y') }}</p>
                @if($invoice->due_date)
                    <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                @endif
                @if($invoice->po_number)
                    <p><strong>PO #:</strong> {{ $invoice->po_number }}</p>
                @endif
                @if($invoice->ship_date)
                    <p><strong>Ship Date:</strong> {{ $invoice->ship_date->format('M d, Y') }}</p>
                @endif
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-to">
                <h3>Bill To:</h3>
                <div class="address">
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    @if($invoice->billing_address)
                        {!! nl2br(e($invoice->billing_address)) !!}<br>
                    @else
                        {{ $invoice->customer->address }}<br>
                    @endif
                    {{ $invoice->customer->email }}<br>
                    {{ $invoice->customer->phone }}
                </div>
            </div>
            <div class="ship-to">
                <h3>Ship To:</h3>
                <div class="address">
                    @if($invoice->shipping_address)
                        {!! nl2br(e($invoice->shipping_address)) !!}
                    @else
                        Same as billing address
                    @endif
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        @if($invoice->terms || $invoice->rep || $invoice->via || $invoice->fob)
            <div class="additional-info">
                @if($invoice->terms)
                    <p><strong>Terms:</strong> {{ $invoice->terms }}</p>
                @endif
                @if($invoice->rep)
                    <p><strong>Sales Rep:</strong> {{ $invoice->rep }}</p>
                @endif
                @if($invoice->via)
                    <p><strong>Ship Via:</strong> {{ $invoice->via }}</p>
                @endif
                @if($invoice->fob)
                    <p><strong>FOB:</strong> {{ $invoice->fob }}</p>
                @endif
            </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 100px;">Unit Price</th>
                    <th style="width: 80px;">Tax %</th>
                    <th style="width: 100px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->tax_rate, 2) }}%</td>
                        <td>${{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Tax:</td>
                    <td class="amount">${{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @if($invoice->shipping_amount > 0)
                    <tr>
                        <td class="label">Shipping:</td>
                        <td class="amount">${{ number_format($invoice->shipping_amount, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->discount_amount > 0)
                    <tr>
                        <td class="label">Discount:</td>
                        <td class="amount">-${{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="label">Total:</td>
                    <td class="amount">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @if($invoice->payments_applied > 0)
                    <tr>
                        <td class="label">Payments Applied:</td>
                        <td class="amount">${{ number_format($invoice->payments_applied, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Balance Due:</td>
                        <td class="amount">${{ number_format($invoice->balance_due, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Customer Message -->
        @if($invoice->customer_message)
            <div class="additional-info">
                <h4>Message:</h4>
                <p>{{ $invoice->customer_message }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            @if($invoice->is_online_payment_enabled)
                <p><strong>Online Payment:</strong> This invoice can be paid online through our secure payment portal.</p>
            @endif
            <p class="no-print">Generated on {{ now()->format('M d, Y \a\t g:i A') }}</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>
