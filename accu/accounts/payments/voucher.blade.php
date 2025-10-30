<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $journal->reference }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 5px;
            font-size: 14px;
            color: #000;
            background: #fff;
        }
        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }
        .company-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .voucher-title {
            font-size: 16px;
            margin: 5px 0;
            font-weight: bold;
            text-decoration: underline;
        }
        .voucher-info {
            margin: 5px 0;
            font-size: 12px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .detail-label {
            font-weight: bold;
        }
        .detail-value {
            text-align: right;
        }
        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 12px;
        }
        .entries-table th {
            border-bottom: 1px solid #000;
            padding: 3px;
            text-align: left;
        }
        .entries-table td {
            padding: 3px;
            text-align: left;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .signature {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            margin: 15px 0 5px;
        }
        .amount-in-words {
            margin: 10px 0;
            padding: 5px;
            border: 1px dashed #ccc;
            font-size: 13px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div style="text-align: center;">
                <img src="{{ asset('assets/images/mf.png') }}" alt="Company Logo" style="max-width: 150px; max-height: 250px; ">
            </div>
            <div class="company-name">OSHADI INVESTMENT (Pvt) Ltd</div>
            <div class="voucher-title">PAYMENT VOUCHER</div>
            <div class="voucher-info">
                PIGALA ROAD, PELAWATTA<br>
                Tel: 0768 605 734 | Reg No: MF12345
            </div>
        </div>
        <div class="divider"></div>
        <div class="detail-row">
            <span class="detail-label">Voucher No:</span>
            <span class="detail-value">{{ $journal->reference }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date:</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Branch:</span>
            <span class="detail-value">{{ $journal->branch->name ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Description:</span>
            <span class="detail-value">{{ $journal->description }}</span>
        </div>
        <div class="divider"></div>
        <table class="entries-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Sub Account</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php $debit_total = 0; $credit_total = 0; @endphp
                @foreach($journal->entries as $entry)
                <tr>
                    <td>{{ $entry->account->account_code ?? '' }} - {{ $entry->account->account_name ?? '' }}</td>
                    <td>{{ $entry->subAccount->sub_account_code ?? '-' }} {{ $entry->subAccount->sub_account_name ?? '' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td class="text-right">{{ number_format($entry->debit, 2) }}</td>
                    <td class="text-right">{{ number_format($entry->credit, 2) }}</td>
                </tr>
                @php $debit_total += $entry->debit; $credit_total += $entry->credit; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-right">{{ number_format($debit_total, 2) }}</td>
                    <td class="text-right">{{ number_format($credit_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <div class="amount-in-words">
            <strong>Amount in Words:</strong>
            {{ amount_in_words($debit_total) }} Only
        </div>
        <div class="signature">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Payee Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Authorized Signature</div>
            </div>
        </div>
        <div class="footer">
            <div>** This is a computer generated voucher **</div>
            <div>Thank you!</div>
            <div class="no-print" style="margin-top: 15px;">
                <button onclick="window.print()" style="padding: 5px 10px;">Print Voucher</button>
                <button onclick="window.close()" style="padding: 5px 10px;">Close Window</button>
            </div>
        </div>
    </div>
    <script>
        // Auto-print with delay for better rendering
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

@php
// Helper for amount in words (simple, for demo)
function amount_in_words($num) {
    $ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
    $tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
    $num = number_format($num, 2, '.', '');
    $parts = explode('.', $num);
    $whole = intval($parts[0]);
    $cents = intval($parts[1] ?? 0);
    if ($whole == 0) return 'Zero';
    $words = '';
    if ($whole >= 1000000) {
        $words .= amount_in_words(intval($whole / 1000000)) . ' Million ';
        $whole %= 1000000;
    }
    if ($whole >= 1000) {
        $words .= amount_in_words(intval($whole / 1000)) . ' Thousand ';
        $whole %= 1000;
    }
    if ($whole >= 100) {
        $words .= $ones[intval($whole / 100)] . ' Hundred ';
        $whole %= 100;
    }
    if ($whole >= 20) {
        $words .= $tens[intval($whole / 10)] . ' ';
        $whole %= 10;
    }
    if ($whole > 0) {
        $words .= $ones[$whole] . ' ';
    }
    $words = trim($words);
    if ($cents > 0) {
        $words .= ' and ' . $cents . '/100';
    }
    return $words;
}
@endphp
</html> 