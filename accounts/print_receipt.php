<?php
session_start();
require_once '../config/database.php';

// Check if payment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid payment ID";
    header("Location: payment_history.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Get payment information with invoice, student, course, and batch details
$stmt = $conn->prepare("
    SELECT p.*, i.invoice_number, i.total_amount, i.paid_amount, i.invoice_date, i.due_date,
           s.full_name, s.contact_number, s.email, s.address,
           c.course_name, b.batch_name
    FROM invoice_payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN students s ON i.student_id = s.student_id
    LEFT JOIN registrations r ON s.student_id = r.student_id
    LEFT JOIN batches b ON r.batch_id = b.batch_id
    LEFT JOIN courses c ON b.course_id = c.course_id
    WHERE p.id = ?
");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

// Get invoice items with item names
$items_stmt = $conn->prepare("
    SELECT ii.quantity, ii.unit_price, ii.line_total, ii.description,
           i.item_name, i.item_type
    FROM invoice_items ii
    JOIN items i ON ii.item_id = i.id
    WHERE ii.invoice_id = ?
    ORDER BY ii.id
");
$items_stmt->bind_param("i", $payment['invoice_id']);
$items_stmt->execute();
$invoice_items = $items_stmt->get_result();

if (!$payment) {
    $_SESSION['error'] = "Payment not found";
    header("Location: payment_history.php");
    exit();
}

// Calculate remaining balance
$remaining_balance = $payment['total_amount'] - $payment['paid_amount'];

// Number to words function
function numberToWords($num) {
    $num = number_format($num, 2, '.', '');
    $parts = explode('.', $num);
    $whole = $parts[0];
    $cents = isset($parts[1]) ? $parts[1] : '00';
    
    $ones = array(
        0 => "Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four",
        5 => "Five", 6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine",
        10 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen",
        14 => "Fourteen", 15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen",
        18 => "Eighteen", 19 => "Nineteen"
    );
    
    $tens = array(
        2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty",
        6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
    );
    
    $formatted = "";
    
    if ($whole < 20) {
        $formatted = $ones[$whole];
    } elseif ($whole < 100) {
        $formatted = $tens[substr($whole, 0, 1)];
        if (substr($whole, 1, 1) != "0") {
            $formatted .= " " . $ones[substr($whole, 1, 1)];
        }
    } elseif ($whole < 1000) {
        $formatted = $ones[substr($whole, 0, 1)] . " Hundred";
        if (substr($whole, 1, 2) != "00") {
            $formatted .= " and " . numberToWords(substr($whole, 1, 2));
        }
    } elseif ($whole < 100000) {
        $formatted = numberToWords(substr($whole, 0, strlen($whole)-3)) . " Thousand";
        if (substr($whole, -3) != "000") {
            $formatted .= " " . numberToWords(substr($whole, -3));
        }
    } elseif ($whole < 10000000) {
        $formatted = numberToWords(substr($whole, 0, strlen($whole)-5)) . " Lakh";
        if (substr($whole, -5) != "00000") {
            $formatted .= " " . numberToWords(substr($whole, -5));
        }
    } else {
        $formatted = numberToWords(substr($whole, 0, strlen($whole)-7)) . " Crore";
        if (substr($whole, -7) != "0000000") {
            $formatted .= " " . numberToWords(substr($whole, -7));
        }
    }
    
    if ($cents != "00") {
        $formatted .= " and " . numberToWords($cents) . " Cents";
    }
    
    return $formatted;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?= htmlspecialchars($payment['invoice_number']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .receipt {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .company-logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 8px 0;
            text-transform: uppercase;
        }
        .company-name {
            font-size: 16px;
            color: #666;
            font-weight: bold;
        }
        .divider {
            height: 1px;
            background: #333;
            margin: 12px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
            width: 40%;
            font-size: 13px;
        }
        .detail-value {
            color: #666;
            width: 60%;
            text-align: right;
            font-size: 13px;
        }
        .student-info {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 5px;
            margin: 12px 0;
            border-left: 3px solid #007bff;
        }
        .student-info h4 {
            margin: 0 0 8px 0;
            color: #007bff;
            font-size: 15px;
        }
        .payment-details {
            background: #e8f5e8;
            padding: 12px;
            border-radius: 5px;
            margin: 12px 0;
            border-left: 3px solid #28a745;
        }
        .payment-details h4 {
            margin: 0 0 8px 0;
            color: #28a745;
            font-size: 15px;
        }
        .amount-in-words {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin: 12px 0;
            border-left: 3px solid #ffc107;
            font-size: 12px;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 12px;
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
            margin: 12px 0 6px;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 12px 0;
            padding: 10px;
            background: #e8f5e8;
            border-radius: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-partial {
            background: #fff3cd;
            color: #856404;
        }
        @media print {
            body {
                padding: 0;
                background: white;
                font-size: 12px;
            }
            .receipt {
                box-shadow: none;
                border-radius: 0;
                padding: 15px;
                max-width: 100%;
            }
            .header {
                margin-bottom: 15px;
                padding-bottom: 10px;
            }
            .company-logo {
                max-width: 120px;
                max-height: 60px;
            }
            .receipt-title {
                font-size: 20px;
                margin: 5px 0;
            }
            .company-name {
                font-size: 14px;
            }
            .divider {
                margin: 8px 0;
            }
            .detail-row {
                margin: 4px 0;
                padding: 2px 0;
            }
            .student-info, .payment-details, .invoice-items {
                padding: 8px;
                margin: 8px 0;
            }
            .student-info h4, .payment-details h4, .invoice-items h4 {
                margin: 0 0 6px 0;
                font-size: 13px;
            }
            .amount-in-words {
                padding: 8px;
                margin: 8px 0;
                font-size: 11px;
            }
            .total-amount {
                font-size: 16px;
                margin: 8px 0;
                padding: 8px;
            }
            .footer {
                margin-top: 10px;
                padding-top: 8px;
                font-size: 9px;
            }
            .signature {
                margin-top: 15px;
            }
            .signature-line {
                margin: 8px 0 4px;
            }
            .no-print {
                display: none;
            }
            @page {
                size: A4;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div>
                <img src="../assets/images/logo.jpg" alt="Company Logo" class="company-logo">
            </div>
            <div class="receipt-title">Payment Receipt</div>
            <div class="company-name">CSTI Bureau (Pvt) Ltd</div>
        </div>

        <div class="divider"></div>

        <!-- Receipt Details -->
        <div class="detail-row">
            <span class="detail-label">Receipt Number:</span>
            <span class="detail-value">#<?= $payment['id'] ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Invoice Number:</span>
            <span class="detail-value"><?= htmlspecialchars($payment['invoice_number']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Date:</span>
            <span class="detail-value"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Method:</span>
            <span class="detail-value"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></span>
        </div>
        <?php if (!empty($payment['reference'])): ?>
        <div class="detail-row">
            <span class="detail-label">Reference:</span>
            <span class="detail-value"><?= htmlspecialchars($payment['reference']) ?></span>
        </div>
        <?php endif; ?>

        <div class="divider"></div>

        <!-- Student Information -->
        <div class="student-info">
            <h4><i class="fas fa-user"></i> Student Information</h4>
            <div class="detail-row">
                <span class="detail-label">Student Name:</span>
                <span class="detail-value"><?= htmlspecialchars($payment['full_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Number:</span>
                <span class="detail-value"><?= htmlspecialchars($payment['contact_number']) ?></span>
            </div>
            <?php if (!empty($payment['email'])): ?>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?= htmlspecialchars($payment['email']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($payment['address'])): ?>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value"><?= htmlspecialchars($payment['address']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($payment['course_name'])): ?>
            <div class="detail-row">
                <span class="detail-label">Course:</span>
                <span class="detail-value"><?= htmlspecialchars($payment['course_name']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($payment['batch_name'])): ?>
            <div class="detail-row">
                <span class="detail-label">Batch:</span>
                <span class="detail-value"><?= htmlspecialchars($payment['batch_name']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Details -->
        <div class="payment-details">
            <h4><i class="fas fa-credit-card"></i> Payment Details</h4>
            <div class="detail-row">
                <span class="detail-label">Invoice Total:</span>
                <span class="detail-value"><?= number_format($payment['total_amount'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Previously Paid:</span>
                <span class="detail-value"><?= number_format($payment['paid_amount'] - $payment['payment_amount'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Amount:</span>
                <span class="detail-value"><?= number_format($payment['payment_amount'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Remaining Balance:</span>
                <span class="detail-value"><?= number_format($remaining_balance, 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <span class="status-badge <?= $remaining_balance <= 0 ? 'status-paid' : 'status-partial' ?>">
                        <?= $remaining_balance <= 0 ? 'Paid' : 'Partial' ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Invoice Items -->
        <?php if ($invoice_items->num_rows > 0): ?>
        <div class="invoice-items" style="background: #f8f9fa; padding: 12px; border-radius: 5px; margin: 12px 0; border-left: 3px solid #17a2b8;">
            <h4 style="margin: 0 0 8px 0; color: #17a2b8; font-size: 15px;"><i class="fas fa-list"></i> Invoice Items</h4>
            <div style="font-size: 12px;">
                <?php while($item = $invoice_items->fetch_assoc()): ?>
                <div style="display: flex; justify-content: space-between; margin: 4px 0; padding: 2px 0; border-bottom: 1px solid #dee2e6;">
                    <span style="font-weight: bold; color: #333; width: 40%;">
                        <?= htmlspecialchars($item['item_name']) ?>
                        <?php if (!empty($item['description'])): ?>
                            <br><small style="color: #666;"><?= htmlspecialchars($item['description']) ?></small>
                        <?php endif; ?>
                    </span>
                    <span style="color: #666; width: 20%; text-align: center;">
                        <?= $item['quantity'] ?> x <?= number_format($item['unit_price'], 2) ?>
                    </span>
                    <span style="color: #333; width: 40%; text-align: right; font-weight: bold;">
                        <?= number_format($item['line_total'], 2) ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="divider"></div>

        <!-- Total Amount -->
        <div class="total-amount">
            Payment Amount: <?= number_format($payment['payment_amount'], 2) ?>
        </div>

        <!-- Amount in Words -->
        <div class="amount-in-words">
            <strong>Amount in Words:</strong><br>
            <?= ucfirst(strtolower(numberToWords($payment['payment_amount']))) ?> Only
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            <div><strong>** This is a computer generated receipt **</strong></div>
            <div>Generated on: <?= date('d/m/Y H:i:s') ?></div>
            <div>Receipt ID: <?= $payment['id'] ?></div>
        </div>
        
        <!-- Signatures -->
        <div class="signature">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Student Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Authorized Signature</div>
            </div>
        </div>
        
        <!-- Print Button -->
        <div class="no-print text-center" style="margin-top: 20px; padding: 20px;">
            <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 25px; font-size: 14px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="receivables.php?receipt_printed=1" style="padding: 10px 25px; font-size: 14px; background: #28a745; color: white; text-decoration: none; display: inline-block; border-radius: 5px; margin-right: 10px;">
                <i class="fas fa-arrow-left"></i> Back to Receivables
            </a>
            <a href="payment_history.php" style="padding: 10px 25px; font-size: 14px; background: #6c757d; color: white; text-decoration: none; display: inline-block; border-radius: 5px;">
                <i class="fas fa-history"></i> Payment History
            </a>
        </div>
    </div>

    <script>
    // Auto-print with delay for better rendering
    window.onload = function() {
        setTimeout(function() {
            // Check if we're in an iframe
            if (window.self === window.top) {
                window.print();
            }
        }, 500);
        
        // Close window after print (optional)
        window.onafterprint = function() {
            setTimeout(function() {
                // Only close if we're not in an iframe
                if (window.self === window.top) {
                    window.close();
                }
            }, 500);
        };
    };
    </script>
</body>
</html>
