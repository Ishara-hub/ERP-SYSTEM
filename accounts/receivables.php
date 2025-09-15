<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Handle payment application BEFORE including header.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['apply_payment'])) {
        $invoice_id = $_POST['invoice_id'];
        $payment_amount = $_POST['payment_amount'];
        $payment_date = $_POST['payment_date'];
        $payment_method = $_POST['payment_method'];
        $reference = $_POST['reference'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get invoice details with student information
            $stmt = $conn->prepare("
                SELECT i.*, s.full_name, s.contact_number, s.email 
                FROM invoices i 
                JOIN students s ON i.student_id = s.student_id 
                WHERE i.id = ?
            ");
            $stmt->bind_param("i", $invoice_id);
            $stmt->execute();
            $invoice = $stmt->get_result()->fetch_assoc();
            
            if (!$invoice) {
                throw new Exception("Invoice not found");
            }
            
            // Calculate remaining balance
            $remaining_balance = $invoice['total_amount'] - ($invoice['paid_amount'] ?? 0);
            
            if ($payment_amount > $remaining_balance) {
                throw new Exception("Payment amount ($payment_amount) exceeds remaining balance ($remaining_balance)");
            }
            
            if ($payment_amount <= 0) {
                throw new Exception("Payment amount must be greater than zero");
            }
            
            // Update invoice paid amount
            $new_paid_amount = ($invoice['paid_amount'] ?? 0) + $payment_amount;
            $new_status = ($new_paid_amount >= $invoice['total_amount']) ? 'paid' : 'partial';
            
            $stmt = $conn->prepare("UPDATE invoices SET paid_amount = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("dsi", $new_paid_amount, $new_status, $invoice_id);
            $stmt->execute();
            
            // Record payment (check if user_id column exists)
            $user_id = $_SESSION['user_id'] ?? 1;
            
            // Try to insert with user_id first, fallback to without if column doesn't exist
            try {
                $stmt = $conn->prepare("INSERT INTO invoice_payments (invoice_id, payment_amount, payment_date, payment_method, reference, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("idsssi", $invoice_id, $payment_amount, $payment_date, $payment_method, $reference, $user_id);
                $stmt->execute();
            } catch (Exception $e) {
                // Fallback to insert without user_id if column doesn't exist
                $stmt = $conn->prepare("INSERT INTO invoice_payments (invoice_id, payment_amount, payment_date, payment_method, reference, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("idssss", $invoice_id, $payment_amount, $payment_date, $payment_method, $reference);
                $stmt->execute();
            }
            
            $payment_id = $conn->insert_id;
            
            // Create journal entry for payment using your existing structure
            // First create journal header in general_journal table
            $stmt = $conn->prepare("INSERT INTO general_journal (transaction_date, reference, description, created_by) VALUES (?, ?, ?, ?)");
            $journal_reference = 'PAY-' . $reference;
            $description = "Payment for Invoice #" . $invoice['invoice_number'];
            $user_id = $_SESSION['user_id'] ?? 1;
            $stmt->bind_param("sssi", $payment_date, $journal_reference, $description, $user_id);
            $stmt->execute();
            
            $journal_id = $conn->insert_id;
            
            // Get sub-account IDs for Cash and Accounts Receivable
            $cash_sub_account = $conn->query("SELECT id, parent_account_id FROM sub_accounts WHERE sub_account_name LIKE '%Cash%' AND is_active = 1 LIMIT 1")->fetch_assoc();
            $receivable_sub_account = $conn->query("SELECT id, parent_account_id FROM sub_accounts WHERE sub_account_name LIKE '%Accounts Receivable%' AND is_active = 1 LIMIT 1")->fetch_assoc();
            
            if (!$cash_sub_account || !$receivable_sub_account) {
                throw new Exception("Required sub-accounts not found. Please ensure 'Cash' and 'Accounts Receivable' sub-accounts exist.");
            }
            
            // Create debit entry (Cash)
            $zero_amount = 0.00;
            $debit_desc = "Payment for Invoice #" . $invoice['invoice_number'] . " - Cash";
            $stmt = $conn->prepare("INSERT INTO journal_entries (date, journal_id, account_id, sub_account_id, debit, credit, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiidds", $payment_date, $journal_id, $cash_sub_account['parent_account_id'], $cash_sub_account['id'], $payment_amount, $zero_amount, $debit_desc);
            $stmt->execute();
            
            // Create credit entry (Accounts Receivable)
            $zero_amount = 0.00;
            $credit_desc = "Payment for Invoice #" . $invoice['invoice_number'] . " - Accounts Receivable";
            $stmt = $conn->prepare("INSERT INTO journal_entries (date, journal_id, account_id, sub_account_id, debit, credit, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiidds", $payment_date, $journal_id, $receivable_sub_account['parent_account_id'], $receivable_sub_account['id'], $zero_amount, $payment_amount, $credit_desc);
            $stmt->execute();
            
            $conn->commit();
            
            // Store payment details for receipt generation
            $_SESSION['last_payment'] = [
                'payment_id' => $payment_id,
                'invoice_number' => $invoice['invoice_number'],
                'student_name' => $invoice['full_name'],
                'payment_amount' => $payment_amount,
                'payment_date' => $payment_date,
                'payment_method' => $payment_method,
                'reference' => $reference,
                'remaining_balance' => $remaining_balance - $payment_amount
            ];
            
            // Redirect to print receipt page BEFORE any output
            header("Location: print_receipt.php?id=" . $payment_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error applying payment: " . $e->getMessage();
        }
    }
}

// Now include header.php after all potential redirects
require_once '../includes/header.php';

// Check if filtering by specific student
$student_filter = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$student_name = '';

if ($student_filter > 0) {
    $stmt = $conn->prepare("SELECT full_name FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student_name = $result->fetch_assoc()['full_name'];
    }
}

// Build WHERE clause for student filtering
$student_where = $student_filter > 0 ? "AND i.student_id = $student_filter" : "";

// Fetch outstanding invoices
$outstanding_query = "
    SELECT i.*, s.full_name, s.contact_number, s.email,
           (i.total_amount - COALESCE(i.paid_amount, 0)) as outstanding_amount
    FROM invoices i 
    JOIN students s ON i.student_id = s.student_id 
    WHERE i.status != 'paid' AND (i.total_amount - COALESCE(i.paid_amount, 0)) > 0 $student_where
    ORDER BY i.due_date ASC, i.invoice_date ASC
";
$outstanding_invoices = $conn->query($outstanding_query);

// Fetch overdue invoices
$overdue_query = "
    SELECT i.*, s.full_name, s.contact_number, s.email,
           (i.total_amount - COALESCE(i.paid_amount, 0)) as outstanding_amount,
           DATEDIFF(CURDATE(), i.due_date) as days_overdue
    FROM invoices i 
    JOIN students s ON i.student_id = s.student_id 
    WHERE i.status != 'paid' AND i.due_date < CURDATE() AND (i.total_amount - COALESCE(i.paid_amount, 0)) > 0 $student_where
    ORDER BY i.due_date ASC
";
$overdue_invoices = $conn->query($overdue_query);

// Calculate totals with proper WHERE clause handling
$total_receivables_query = "
    SELECT SUM(total_amount - COALESCE(paid_amount, 0)) as total
    FROM invoices 
    WHERE status != 'paid'
";
if ($student_filter > 0) {
    $total_receivables_query .= " AND student_id = $student_filter";
}
$total_receivables = $conn->query($total_receivables_query)->fetch_assoc()['total'] ?? 0;

$total_overdue_query = "
    SELECT SUM(total_amount - COALESCE(paid_amount, 0)) as total
    FROM invoices 
    WHERE status != 'paid' AND due_date < CURDATE()
";
if ($student_filter > 0) {
    $total_overdue_query .= " AND student_id = $student_filter";
}
$total_overdue = $conn->query($total_overdue_query)->fetch_assoc()['total'] ?? 0;

// Fetch recent payments
$recent_payments = $conn->query("
    SELECT p.*, i.invoice_number, s.full_name, s.contact_number
    FROM invoice_payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN students s ON i.student_id = s.student_id
    ORDER BY p.created_at DESC
    LIMIT 10
");
?>

<div class="container-fluid mt-4">
    <!-- Success Message from Receipt Print -->
    <?php if (isset($_GET['receipt_printed'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> Payment receipt has been printed. You can now return to managing receivables.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Student Filter Header -->
    <?php if ($student_filter > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Filtered by Student: <?= htmlspecialchars($student_name) ?></h5>
                            <p class="mb-0">Showing invoices and payments for this student only</p>
                        </div>
                        <div>
                            <a href="receivables.php" class="btn btn-outline-info">
                                <i class="fas fa-times"></i> Clear Filter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Total Receivables</h4>
                            <h2>$<?= number_format($total_receivables, 2) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Overdue Amount</h4>
                            <h2>$<?= number_format($total_overdue, 2) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Outstanding Invoices</h4>
                            <h2><?= $outstanding_invoices->num_rows ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Outstanding Invoices -->
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-list me-2"></i>Outstanding Invoices</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <?= $success_message ?>
                            <?php if (isset($_SESSION['last_payment'])): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="showReceipt()">
                                        <i class="fas fa-eye me-2"></i>View Receipt
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="printReceipt()">
                                        <i class="fas fa-print me-2"></i>Print Receipt
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="downloadReceipt()">
                                        <i class="fas fa-download me-2"></i>Download PDF
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Student</th>
                                    <th>Due Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Outstanding</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($invoice = $outstanding_invoices->fetch_assoc()): ?>
                                <tr class="<?= strtotime($invoice['due_date']) < time() ? 'table-warning' : '' ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>
                                        <?php if (strtotime($invoice['due_date']) < time()): ?>
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($invoice['full_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($invoice['contact_number']) ?></small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($invoice['due_date'])) ?>
                                        <?php if (strtotime($invoice['due_date']) < time()): ?>
                                            <br><small class="text-danger">
                                                <?= abs(strtotime($invoice['due_date']) - time()) / (60*60*24) ?> days overdue
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?= number_format($invoice['total_amount'], 2) ?></td>
                                    <td>$<?= number_format($invoice['paid_amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <strong class="text-danger">$<?= number_format($invoice['outstanding_amount'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= $invoice['status'] == 'partial' ? 'bg-warning' : 'bg-danger' ?>">
                                            <?= ucfirst($invoice['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="showPaymentModal(<?= $invoice['id'] ?>, '<?= htmlspecialchars($invoice['invoice_number']) ?>', <?= $invoice['outstanding_amount'] ?>)">
                                            <i class="fas fa-credit-card"></i> Pay
                                        </button>
                                        <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        
        <!-- Quick Actions & Overdue Summary -->
        <div class="col-md-4">
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="invoices.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Invoice
                        </a>
                        <a href="aging_report.php" class="btn btn-info">
                            <i class="fas fa-chart-bar me-2"></i>Aging Report
                        </a>
                        <a href="payment_history.php" class="btn btn-secondary">
                            <i class="fas fa-history me-2"></i>Payment History
                        </a>
                    </div>
                    
                    <hr>
                    
                    <h6>Overdue Summary</h6>
                    <?php 
                    $overdue_invoices->data_seek(0);
                    $overdue_count = 0;
                    while($invoice = $overdue_invoices->fetch_assoc()): 
                        $overdue_count++;
                        if ($overdue_count > 5) break;
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <small class="text-muted"><?= htmlspecialchars($invoice['invoice_number']) ?></small><br>
                                <small><?= htmlspecialchars($invoice['full_name']) ?></small>
                            </div>
                            <div class="text-end">
                                <small class="text-danger">$<?= number_format($invoice['outstanding_amount'], 2) ?></small><br>
                                <small class="text-danger"><?= $invoice['days_overdue'] ?> days</small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if ($overdue_count == 0): ?>
                        <p class="text-success text-center mt-3">
                            <i class="fas fa-check-circle"></i> No overdue invoices!
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            
            <!-- Recent Payments -->
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payments</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $recent_payments->data_seek(0);
                    $payment_count = 0;
                    while($payment = $recent_payments->fetch_assoc()): 
                        $payment_count++;
                        if ($payment_count > 5) break;
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <small class="text-muted"><?= htmlspecialchars($payment['invoice_number']) ?></small><br>
                                <small><?= htmlspecialchars($payment['full_name']) ?></small>
                            </div>
                            <div class="text-end">
                                <small class="text-success">$<?= number_format($payment['payment_amount'], 2) ?></small><br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if ($payment_count == 0): ?>
                        <p class="text-muted text-center mt-3">
                            <i class="fas fa-info-circle"></i> No recent payments
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($payment_count > 0): ?>
                        <div class="text-center mt-3">
                            <a href="payment_history.php" class="btn btn-sm btn-outline-success">
                                View All Payments
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="payment_invoice_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" id="payment_invoice_number" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Outstanding Amount</label>
                        <input type="text" id="payment_outstanding_amount" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Amount</label>
                        <input type="number" name="payment_amount" class="form-control" step="0.01" required min="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control" placeholder="Payment reference number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="apply_payment" class="btn btn-success">Apply Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showPaymentModal(invoiceId, invoiceNumber, outstandingAmount) {
    document.getElementById('payment_invoice_id').value = invoiceId;
    document.getElementById('payment_invoice_number').value = invoiceNumber;
    document.getElementById('payment_outstanding_amount').value = '$' + outstandingAmount.toFixed(2);
    
    // Set max payment amount
    document.querySelector('input[name="payment_amount"]').max = outstandingAmount;
    
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

// Validate payment amount
document.querySelector('input[name="payment_amount"]').addEventListener('change', function() {
    const outstanding = parseFloat(document.getElementById('payment_outstanding_amount').value.replace('$', ''));
    const payment = parseFloat(this.value);
    
    if (payment > outstanding) {
        alert('Payment amount cannot exceed outstanding amount');
        this.value = outstanding;
    }
});

// Receipt functions
function printReceipt() {
    const receiptContent = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Payment Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .receipt { border: 2px solid #000; padding: 20px; max-width: 600px; margin: 0 auto; }
                    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
                    .details { margin-bottom: 20px; }
                    .row { display: flex; justify-content: space-between; margin-bottom: 10px; }
                    .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; }
                    @media print { body { margin: 0; } .receipt { border: none; } }
                </style>
            </head>
            <body>
                ${receiptContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function downloadReceipt() {
    // This would typically generate a PDF
    alert('PDF download feature would be implemented here');
}

function showReceipt() {
    const payment = <?= json_encode($_SESSION['last_payment'] ?? null) ?>;
    if (payment) {
        const receiptHtml = `
            <div class="receipt">
                <div class="header">
                    <h2>PAYMENT RECEIPT</h2>
                    <h4>Student Management System</h4>
                    <p>Receipt #: ${payment.payment_id}</p>
                </div>
                
                <div class="details">
                    <div class="row">
                        <strong>Invoice Number:</strong>
                        <span>${payment.invoice_number}</span>
                    </div>
                    <div class="row">
                        <strong>Student Name:</strong>
                        <span>${payment.student_name}</span>
                    </div>
                    <div class="row">
                        <strong>Payment Amount:</strong>
                        <span>${parseFloat(payment.payment_amount).toFixed(2)}</span>
                    </div>
                    <div class="row">
                        <strong>Payment Date:</strong>
                        <span>${payment.payment_date}</span>
                    </div>
                    <div class="row">
                        <strong>Payment Method:</strong>
                        <span>${payment.payment_method}</span>
                    </div>
                    <div class="row">
                        <strong>Reference:</strong>
                        <span>${payment.reference || 'N/A'}</span>
                    </div>
                    <div class="row">
                        <strong>Remaining Balance:</strong>
                        <span>${parseFloat(payment.remaining_balance).toFixed(2)}</span>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Thank you for your payment!</p>
                    <small>Generated on ${new Date().toLocaleString()}</small>
                </div>
            </div>
        `;
        
        document.getElementById('receiptContent').innerHTML = receiptHtml;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }
}
</script>

<?php 
// Clear the last payment session data after displaying
if (isset($_SESSION['last_payment'])) {
    unset($_SESSION['last_payment']);
}

include '../includes/footer.php'; 
?>
