<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle search and filtering
$search = $_GET['search'] ?? '';
$payment_method = $_GET['payment_method'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$student_filter = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Build query with filters
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(i.invoice_number LIKE ? OR s.full_name LIKE ? OR s.contact_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if (!empty($payment_method)) {
    $where_conditions[] = "p.payment_method = ?";
    $params[] = $payment_method;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "p.payment_date >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "p.payment_date <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

if ($student_filter > 0) {
    $where_conditions[] = "i.student_id = ?";
    $params[] = $student_filter;
    $param_types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch payments with filters
$query = "
    SELECT p.*, i.invoice_number, i.total_amount, i.paid_amount, s.full_name, s.contact_number, s.email
    FROM invoice_payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN students s ON i.student_id = s.student_id
    $where_clause
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$payments = $stmt->get_result();

// Calculate totals
$total_payments = $payments->num_rows;
$total_amount = 0;
$payments->data_seek(0);
while ($payment = $payments->fetch_assoc()) {
    $total_amount += $payment['payment_amount'];
}
$payments->data_seek(0);

// Get student name if filtering by student
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

// Fetch recent payments for quick print button
$recent_payments = $conn->query("
    SELECT p.*, i.invoice_number, s.full_name, s.contact_number
    FROM invoice_payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN students s ON i.student_id = s.student_id
    ORDER BY p.created_at DESC
    LIMIT 1
");
?>

<div class="container-fluid mt-4">
    <!-- Student Filter Header -->
    <?php if ($student_filter > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Filtered by Student: <?= htmlspecialchars($student_name) ?></h5>
                            <p class="mb-0">Showing payment history for this student only</p>
                        </div>
                        <div>
                            <a href="payment_history.php" class="btn btn-outline-info">
                                <i class="fas fa-times"></i> Clear Filter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-history me-2"></i>Payment History</h2>
            <p class="text-muted">Track all payments made to invoices</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="receivables.php" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Receivables
            </a>
            <?php 
            // Get the latest payment ID for quick print
            $recent_payments->data_seek(0);
            $latest_payment = $recent_payments->fetch_assoc();
            if ($latest_payment): 
            ?>
            <a href="print_receipt.php?id=<?= $latest_payment['id'] ?>" class="btn btn-success" target="_blank" title="Print Latest Receipt">
                <i class="fas fa-print me-2"></i>Print Receipt
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Total Payments</h4>
                            <h2>$<?= number_format($total_amount, 2) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-3x"></i>
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
                            <h4 class="card-title">Payment Count</h4>
                            <h2><?= $total_payments ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-credit-card fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Average Payment</h4>
                            <h2>$<?= $total_payments > 0 ? number_format($total_amount / $total_payments, 2) : '0.00' ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-search me-2"></i>Search & Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Invoice #, Student Name, Contact" value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="cash" <?= $payment_method == 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="bank_transfer" <?= $payment_method == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="credit_card" <?= $payment_method == 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                        <option value="check" <?= $payment_method == 'check' ? 'selected' : '' ?>>Check</option>
                        <option value="other" <?= $payment_method == 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($search) || !empty($payment_method) || !empty($date_from) || !empty($date_to)): ?>
                <div class="mt-3">
                    <a href="payment_history.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Payment Records</h5>
        </div>
        <div class="card-body">
            <?php if ($total_payments > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Payment ID</th>
                                <th>Invoice #</th>
                                <th>Student</th>
                                <th>Payment Amount</th>
                                <th>Payment Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
            // Reset result pointer for table display
            $payments->data_seek(0);
            while($payment = $payments->fetch_assoc()): 
            ?>
                            <tr>
                                <td>
                                    <strong>#<?= $payment['id'] ?></strong>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($payment['invoice_number']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($payment['full_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($payment['contact_number']) ?></small>
                                </td>
                                <td>
                                    <strong class="text-success">$<?= number_format($payment['payment_amount'], 2) ?></strong>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($payment['payment_date'])) ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($payment['reference'] ?: 'N/A') ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPaymentDetails(<?= $payment['id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="print_receipt.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No payments found</h5>
                    <p class="text-muted">Try adjusting your search criteria or create some invoices first.</p>
                    <a href="invoices.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Invoice
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function viewPaymentDetails(paymentId) {
    // This would typically open a modal with payment details
    alert('Payment details view would be implemented here for payment ID: ' + paymentId);
}
</script>

<?php include '../includes/footer.php'; ?>
