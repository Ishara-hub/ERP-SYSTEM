<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$journal_id = $_GET['id'] ?? 0;

// Validate journal ID
if (!$journal_id || !is_numeric($journal_id)) {
    $_SESSION['error'] = "Invalid journal entry ID.";
    header("Location: journal_entry.php");
    exit();
}

$journal = $conn->query("SELECT * FROM general_journal WHERE id = $journal_id")->fetch_assoc();

// Check if journal exists
if (!$journal) {
    $_SESSION['error'] = "Journal entry not found.";
    header("Location: journal_entry.php");
    exit();
}

$entries = $conn->query("
    SELECT je.*, coa.account_code, coa.account_name, 
           sa.sub_account_code, sa.sub_account_name
    FROM journal_entries je
    JOIN chart_of_accounts coa ON je.account_id = coa.id
    LEFT JOIN sub_accounts sa ON je.sub_account_id = sa.id
    WHERE je.journal_id = $journal_id
");


?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2><i class="fas fa-book me-2"></i>Journal Entry #<?= $journal_id ?></h2>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <strong>Date:</strong> <?= date('m/d/Y', strtotime($journal['transaction_date'])) ?>
                </div>
                <div class="col-md-3">
                    <strong>Reference:</strong> <?= htmlspecialchars($journal['reference']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong> 
                    <span class="badge bg-success">Posted</span>
                </div>
                <div class="col-md-3">
                    <strong>Created:</strong> <?= isset($journal['created_at']) ? date('m/d/Y H:i', strtotime($journal['created_at'])) : 'N/A' ?>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <strong>Description:</strong> <?= htmlspecialchars($journal['description']) ?>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Account</th>
                            <th>Sub Account</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalDebit = 0;
                        $totalCredit = 0;
                        while($entry = $entries->fetch_assoc()): 
                            $totalDebit += $entry['debit'];
                            $totalCredit += $entry['credit'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['account_code'].' - '.$entry['account_name']) ?></td>
                            <td>
                                <?= $entry['sub_account_code'] ? 
                                    htmlspecialchars($entry['sub_account_code'].' - '.$entry['sub_account_name']) : 
                                    'N/A' ?>
                            </td>
                            <td class="text-end"><?= number_format($entry['debit'], 2) ?></td>
                            <td class="text-end"><?= number_format($entry['credit'], 2) ?></td>
                            <td><?= htmlspecialchars($entry['description']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <th colspan="2" class="text-end">Totals:</th>
                            <th class="text-end"><?= number_format($totalDebit, 2) ?></th>
                            <th class="text-end"><?= number_format($totalCredit, 2) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-3">
                <a href="journal_entry.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Create Journal
                </a>
                <a href="../chart_of_accounts_data.php" class="btn btn-info ms-2">
                    <i class="fas fa-chart-bar me-1"></i> View Chart of Accounts
                </a>
                <button onclick="window.print()" class="btn btn-primary ms-2">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>