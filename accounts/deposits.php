<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Get filter parameters
$branch_id = $_GET['branch_id'] ?? '';
$payment_method = $_GET['payment_method'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

// Build query for deposits
$where_conditions = ["1=1"];
$params = [];
$types = '';

if (!empty($branch_id)) {
    $where_conditions[] = "d.branch_id = ?";
    $params[] = $branch_id;
    $types .= 'i';
}

if (!empty($payment_method)) {
    $where_conditions[] = "d.payment_method = ?";
    $params[] = $payment_method;
    $types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "d.deposit_date >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "d.deposit_date <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(d.depositor_name LIKE ? OR d.cheque_no LIKE ? OR gj.reference LIKE ? OR gj.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

$where_clause = implode(" AND ", $where_conditions);

$query = "
    SELECT 
        d.id,
        d.deposit_date,
        d.amount,
        d.payment_method,
        d.cheque_no,
        d.depositor_name,
        d.contact_number,
        d.remarks,
        d.branch_id,
        b.name as branch_name,
        gj.reference,
        gj.description,
        coa.account_code as deposit_account_code,
        coa.account_name as deposit_account_name,
        sa.sub_account_code as deposit_sub_account_code,
        sa.sub_account_name as deposit_sub_account_name,
        u.username as created_by_name,
        d.created_at
    FROM deposits d
    JOIN general_journal gj ON d.journal_id = gj.id
    JOIN journal_entries je ON gj.id = je.journal_id AND je.debit > 0
    JOIN chart_of_accounts coa ON je.account_id = coa.id
    LEFT JOIN sub_accounts sa ON je.sub_account_id = sa.id
    JOIN branches b ON d.branch_id = b.id
    JOIN users u ON d.created_by = u.user_id
    WHERE $where_clause
    ORDER BY d.deposit_date DESC, d.id DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$deposits = $stmt->get_result();

// Get summary statistics
$summary_query = "
    SELECT 
        COUNT(*) as total_deposits,
        SUM(d.amount) as total_amount,
        COUNT(DISTINCT d.branch_id) as branches_count,
        COUNT(DISTINCT d.payment_method) as payment_methods_count
    FROM deposits d
    WHERE $where_clause
";

$summary_stmt = $conn->prepare($summary_query);
if (!empty($params)) {
    $summary_stmt->bind_param($types, ...$params);
}
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();

// Get data for dropdowns
$branches = $conn->query("SELECT * FROM branches WHERE status = 'active' ORDER BY name");
$payment_methods = $conn->query("SELECT DISTINCT payment_method FROM deposits ORDER BY payment_method");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-money-bill-wave me-2"></i>Deposits</h2>
                <p class="mb-0">View and manage all recorded deposits</p>
            </div>
            <a href="record_deposit.php" class="btn btn-light">
                <i class="fas fa-plus me-1"></i> New Deposit
            </a>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= number_format($summary['total_deposits'] ?? 0) ?></h5>
                            <p class="card-text small">Total Deposits</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= number_format($summary['total_amount'] ?? 0, 2) ?></h5>
                            <p class="card-text small">Total Amount (Rs.)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= $summary['branches_count'] ?? 0 ?></h5>
                            <p class="card-text small">Branches</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= $summary['payment_methods_count'] ?? 0 ?></h5>
                            <p class="card-text small">Payment Methods</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            <?php while($branch = $branches->fetch_assoc()): ?>
                                <option value="<?= $branch['id'] ?>" <?= $branch_id == $branch['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <?php while($method = $payment_methods->fetch_assoc()): ?>
                                <option value="<?= $method['payment_method'] ?>" <?= $payment_method == $method['payment_method'] ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($method['payment_method'])) ?>
                                </option>
                            <?php endwhile; ?>
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
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="deposits.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Deposits Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="depositsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Depositor</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Account</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_amount = 0;
                        if ($deposits->num_rows > 0):
                            while($deposit = $deposits->fetch_assoc()): 
                                $total_amount += $deposit['amount'];
                        ?>
                        <tr>
                            <td><?= date('m/d/Y', strtotime($deposit['deposit_date'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($deposit['reference']) ?></strong>
                                <?php if ($deposit['cheque_no']): ?>
                                    <br><small class="text-muted">Cheque: <?= htmlspecialchars($deposit['cheque_no']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($deposit['depositor_name']) ?></strong>
                                <?php if ($deposit['contact_number']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($deposit['contact_number']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($deposit['description']) ?></td>
                            <td class="text-end">
                                <strong class="text-success"><?= number_format($deposit['amount'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= ucfirst(htmlspecialchars($deposit['payment_method'])) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($deposit['deposit_account_code'] . ' - ' . $deposit['deposit_account_name']) ?>
                                <?php if ($deposit['deposit_sub_account_code']): ?>
                                    <br><small class="text-muted">
                                        <?= htmlspecialchars($deposit['deposit_sub_account_code'] . ' - ' . $deposit['deposit_sub_account_name']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($deposit['branch_name']) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="view_deposit.php?id=<?= $deposit['id'] ?>" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="print_deposit_receipt.php?id=<?= $deposit['id'] ?>" class="btn btn-sm btn-success" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning" title="Edit" onclick="editDeposit(<?= $deposit['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No deposits found for the selected criteria
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if ($deposits->num_rows > 0): ?>
                    <tfoot class="table-active">
                        <tr>
                            <th colspan="4" class="text-end">Total Amount:</th>
                            <th class="text-end text-success">
                                <strong><?= number_format($total_amount, 2) ?></strong>
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Export Buttons -->
            <?php if ($deposits->num_rows > 0): ?>
            <div class="mt-3">
                <button type="button" class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel me-1"></i> Export to Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf me-1"></i> Export to PDF
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#depositsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function editDeposit(depositId) {
    if (confirm('Edit this deposit? You will be redirected to the edit form.')) {
        window.location.href = `edit_deposit.php?id=${depositId}`;
    }
}

function exportToExcel() {
    // Create a temporary table for export
    const table = document.getElementById('depositsTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Deposits"});
    XLSX.writeFile(wb, `deposits_${new Date().toISOString().split('T')[0]}.xlsx`);
}

function exportToPDF() {
    // Implementation for PDF export
    alert('PDF export functionality will be implemented here.');
}
</script>

<!-- Include required libraries -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<?php include '../includes/footer.php'; ?>
