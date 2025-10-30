<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $payment->payment_number ?? $payment->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .voucher {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .voucher-title {
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
        }
        .details-section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            width: 200px;
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            flex: 1;
        }
        .amount-section {
            text-align: center;
            margin: 40px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border: 2px solid #000;
        }
        .amount-label {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .amount-value {
            font-size: 36px;
            font-weight: bold;
            color: #000;
        }
        .amount-words {
            font-size: 14px;
            margin-top: 10px;
            font-style: italic;
            color: #555;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 250px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .voucher {
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-button button:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="no-print print-button">
        <button onclick="window.print()">Print Voucher</button>
    </div>

    <div class="voucher">
        <div class="header">
            <div class="company-name">Your Company Name</div>
            <div style="font-size: 14px; color: #666;">Company Address, City, State, ZIP</div>
            <div style="font-size: 14px; color: #666;">Phone: (555) 123-4567 | Email: info@company.com</div>
            <div class="voucher-title">PAYMENT VOUCHER</div>
        </div>

        <div class="details-section">
            <div class="section-title">Payment Information</div>
            <div class="detail-row">
                <div class="detail-label">Payment Number:</div>
                <div class="detail-value">{{ $payment->payment_number ?? 'N/A' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Payment Date:</div>
                <div class="detail-value">{{ $payment->payment_date->format('F d, Y') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Payment Method:</div>
                <div class="detail-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Bank Account:</div>
                <div class="detail-value">{{ $payment->bankAccount ? $payment->bankAccount->account_name : 'N/A' }}</div>
            </div>
            @if($payment->reference)
            <div class="detail-row">
                <div class="detail-label">Reference:</div>
                <div class="detail-value">{{ $payment->reference }}</div>
            </div>
            @endif
        </div>

        <div class="details-section">
            <div class="section-title">Payee Information</div>
            <div class="detail-row">
                <div class="detail-label">Supplier Name:</div>
                <div class="detail-value">{{ $payment->supplier ? $payment->supplier->name : 'N/A' }}</div>
            </div>
            @if($payment->supplier && $payment->supplier->email)
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value">{{ $payment->supplier->email }}</div>
            </div>
            @endif
        </div>

        @if($payment->bill)
        <div class="details-section">
            <div class="section-title">Bill Information</div>
            <div class="detail-row">
                <div class="detail-label">Bill Number:</div>
                <div class="detail-value">{{ $payment->bill->bill_number }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Bill Date:</div>
                <div class="detail-value">{{ $payment->bill->bill_date->format('F d, Y') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Due Date:</div>
                <div class="detail-value">{{ $payment->bill->due_date ? $payment->bill->due_date->format('F d, Y') : 'N/A' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Total Amount:</div>
                <div class="detail-value">${{ number_format($payment->bill->total_amount, 2) }}</div>
            </div>
        </div>
        @endif

        <div class="amount-section">
            <div class="amount-label">Payment Amount</div>
            <div class="amount-value">${{ number_format($payment->amount, 2) }}</div>
        </div>

        @if($payment->notes)
        <div class="details-section">
            <div class="section-title">Notes</div>
            <div style="margin-left: 20px;">{{ $payment->notes }}</div>
        </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="margin-top: 5px;">Prepared By</div>
                <div style="margin-top: 5px; font-size: 14px;">{{ $payment->received_by ?? auth()->user()->name }}</div>
                <div style="margin-top: 5px; font-size: 12px; color: #666;">Date: {{ now()->format('m/d/Y') }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="margin-top: 5px;">Authorized Signature</div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated voucher. No signature required.</p>
            <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>
