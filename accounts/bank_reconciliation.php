<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle form submission for reconciliation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['reconcile_items'])) {
            $conn->autocommit(FALSE);
            
            $reconciled_items = $_POST['reconcile_items'];
            $reconciliation_date = $_POST['reconciliation_date'];
            $bank_account_id = $_POST['bank_account_id'];
            $branch_id = $_POST['branch_id'];
            
            foreach ($reconciled_items as $item_id) {
                $stmt = $conn->prepare("UPDATE journal_entries 
                                      SET reconciled = 1, reconciliation_date = ?, reconciled_by = ? 
                                      WHERE id = ?");
                $stmt->bind_param("sii", $reconciliation_date, $_SESSION['user_id'], $item_id);
                $stmt->execute();
            }
            
            // Create reconciliation record
            $stmt = $conn->prepare("INSERT INTO bank_reconciliations 
                                  (bank_account_id, branch_id, reconciliation_date, reconciled_items, 
                                   created_by, created_at) 
                                  VALUES (?, ?, ?, ?, ?, NOW())");
            $reconciled_count = count($reconciled_items);
            $stmt->bind_param("iisis", $bank_account_id, $branch_id, $reconciliation_date, 
                             $reconciled_count, $_SESSION['user_id']);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Successfully reconciled $reconciled_count items.";
            header("Location: bank_reconciliation.php?success=1");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Reconciliation Error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to reconcile items: " . $e->getMessage();
    }
}

// Get filter parameters
$bank_account_id = $_GET['bank_account_id'] ?? '';
$branch_id = $_GET['branch_id'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Get data for dropdowns
$branches = $conn->query("SELECT * FROM branches WHERE status = 'active' ORDER BY name");
$bank_accounts = $conn->query("SELECT * FROM chart_of_accounts 
                              WHERE category_id IN 
                              (SELECT id FROM account_categories WHERE name = 'Assets')
                              AND is_active = 1 
                              AND (account_name LIKE '%Bank%' OR account_name LIKE '%Cash%')
                              ORDER BY account_code");

// Build query for unreconciled transactions
$where_conditions = ["je.reconciled = 0"];
$params = [];
$types = '';

if (!empty($bank_account_id)) {
    $where_conditions[] = "je.account_id = ?";
    $params[] = $bank_account_id;
    $types .= 'i';
}

if (!empty($branch_id)) {
    $where_conditions[] = "je.branch_id = ?";
    $params[] = $branch_id;
    $types .= 'i';
}

$where_conditions[] = "gj.transaction_date BETWEEN ? AND ?";
$params[] = $date_from;
$params[] = $date_to;
$types .= 'ss';

$where_clause = implode(" AND ", $where_conditions);

$query = "
    SELECT 
        je.id,
        je.debit,
        je.credit,
        je.description,
        je.reconciled,
        gj.transaction_date,
        gj.reference,
        coa.account_code,
        coa.account_name,
        sa.sub_account_code,
        sa.sub_account_name,
        b.name as branch_name
    FROM journal_entries je
    JOIN general_journal gj ON je.journal_id = gj.id
    JOIN chart_of_accounts coa ON je.account_id = coa.id
    LEFT JOIN sub_accounts sa ON je.sub_account_id = sa.id
    LEFT JOIN branches b ON je.branch_id = b.id
    WHERE $where_clause
    ORDER BY gj.transaction_date DESC, je.id DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$unreconciled_transactions = $stmt->get_result();

// Get reconciliation summary
$summary_query = "
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN je.reconciled = 1 THEN 1 ELSE 0 END) as reconciled_count,
        SUM(CASE WHEN je.reconciled = 0 THEN 1 ELSE 0 END) as unreconciled_count,
        SUM(CASE WHEN je.reconciled = 1 THEN je.debit - je.credit ELSE 0 END) as reconciled_balance,
        SUM(CASE WHEN je.reconciled = 0 THEN je.debit - je.credit ELSE 0 END) as unreconciled_balance
    FROM journal_entries je
    JOIN general_journal gj ON je.journal_id = gj.id
    JOIN chart_of_accounts coa ON je.account_id = coa.id
    WHERE coa.id = ? AND gj.transaction_date BETWEEN ? AND ?
";

$summary_stmt = $conn->prepare($summary_query);
$bank_account_param = $bank_account_id ?: 0;
$summary_stmt->bind_param("iss", $bank_account_param, $date_from, $date_to);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2><i class="fas fa-balance-scale me-2"></i>Bank Reconciliation</h2>
            <p class="mb-0">Match bank statements with company records</p>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">All Bank Accounts</option>
                            <?php while($account = $bank_accounts->fetch_assoc()): ?>
                                <option value="<?= $account['id'] ?>" <?= $bank_account_id == $account['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account['account_code'] . ' - ' . $account['account_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
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
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Summary Cards -->
            <?php if ($bank_account_id): ?>
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary"><?= $summary['total_transactions'] ?? 0 ?></h5>
                            <p class="card-text small">Total Transactions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= $summary['reconciled_count'] ?? 0 ?></h5>
                            <p class="card-text small">Reconciled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= $summary['unreconciled_count'] ?? 0 ?></h5>
                            <p class="card-text small">Unreconciled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= number_format($summary['reconciled_balance'] ?? 0, 2) ?></h5>
                            <p class="card-text small">Reconciled Balance</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= number_format($summary['unreconciled_balance'] ?? 0, 2) ?></h5>
                            <p class="card-text small">Unreconciled Balance</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Reconciliation Form -->
            <form method="POST" id="reconciliationForm">
                <input type="hidden" name="reconciliation_date" value="<?= date('Y-m-d') ?>">
                <input type="hidden" name="bank_account_id" value="<?= $bank_account_id ?>">
                <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Unreconciled Transactions</h4>
                    <div>
                        <button type="submit" class="btn btn-success" id="reconcileBtn" disabled>
                            <i class="fas fa-check me-1"></i> Reconcile Selected
                        </button>
                        <button type="button" class="btn btn-info ms-2" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="reconciliationTable">
                        <thead class="table-dark">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Balance</th>
                                <th>Branch</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_debit = 0;
                            $total_credit = 0;
                            $total_balance = 0;
                            
                            if ($unreconciled_transactions->num_rows > 0):
                                while($transaction = $unreconciled_transactions->fetch_assoc()): 
                                    $balance = $transaction['debit'] - $transaction['credit'];
                                    $total_debit += $transaction['debit'];
                                    $total_credit += $transaction['credit'];
                                    $total_balance += $balance;
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="reconcile_items[]" value="<?= $transaction['id'] ?>" 
                                           class="form-check-input reconcile-checkbox">
                                </td>
                                <td><?= date('m/d/Y', strtotime($transaction['transaction_date'])) ?></td>
                                <td><?= htmlspecialchars($transaction['reference']) ?></td>
                                <td>
                                    <?= htmlspecialchars($transaction['account_code'] . ' - ' . $transaction['account_name']) ?>
                                    <?php if ($transaction['sub_account_code']): ?>
                                        <br><small class="text-muted">
                                            <?= htmlspecialchars($transaction['sub_account_code'] . ' - ' . $transaction['sub_account_name']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['description']) ?></td>
                                <td class="text-end"><?= number_format($transaction['debit'], 2) ?></td>
                                <td class="text-end"><?= number_format($transaction['credit'], 2) ?></td>
                                <td class="text-end <?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($balance, 2) ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['branch_name']) ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No unreconciled transactions found for the selected criteria
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if ($unreconciled_transactions->num_rows > 0): ?>
                        <tfoot class="table-active">
                            <tr>
                                <th colspan="5" class="text-end">Totals:</th>
                                <th class="text-end"><?= number_format($total_debit, 2) ?></th>
                                <th class="text-end"><?= number_format($total_credit, 2) ?></th>
                                <th class="text-end <?= $total_balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($total_balance, 2) ?>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.reconcile-checkbox').prop('checked', $(this).is(':checked'));
        updateReconcileButton();
    });
    
    // Individual checkbox change
    $(document).on('change', '.reconcile-checkbox', function() {
        updateReconcileButton();
        
        // Update select all checkbox
        const totalCheckboxes = $('.reconcile-checkbox').length;
        const checkedCheckboxes = $('.reconcile-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    });
    
    function updateReconcileButton() {
        const checkedCount = $('.reconcile-checkbox:checked').length;
        $('#reconcileBtn').prop('disabled', checkedCount === 0);
        
        if (checkedCount > 0) {
            $('#reconcileBtn').html(`<i class="fas fa-check me-1"></i> Reconcile Selected (${checkedCount})`);
        } else {
            $('#reconcileBtn').html('<i class="fas fa-check me-1"></i> Reconcile Selected');
        }
    }
    
    // Form submission confirmation
    $('#reconciliationForm').submit(function(e) {
        const checkedCount = $('.reconcile-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Please select at least one transaction to reconcile.');
            return false;
        }
        
        return confirm(`Are you sure you want to reconcile ${checkedCount} selected transaction(s)?`);
    });
});

function exportToExcel() {
    // Create a temporary table for export
    const table = document.getElementById('reconciliationTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Bank Reconciliation"});
    XLSX.writeFile(wb, `bank_reconciliation_${new Date().toISOString().split('T')[0]}.xlsx`);
}
</script>

<!-- Include SheetJS for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<?php include '../includes/footer.php'; ?>
