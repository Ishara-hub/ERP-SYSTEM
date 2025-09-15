<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Generate deposit reference number
function generate_deposit_reference() {
    $prefix = "DEP-" . date('Y-m-');
    global $conn;
    $stmt = $conn->prepare("SELECT MAX(id) FROM general_journal WHERE reference LIKE ?");
    $like_pattern = $prefix . '%';
    $stmt->bind_param("s", $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $last_id = $result->fetch_row()[0] ?? 0;
    $next_num = str_pad(($last_id + 1), 6, '0', STR_PAD_LEFT);
    return $prefix . $next_num;
}

// Handle POST request for deposit creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['branch_id']) || empty($_POST['deposit_account_id']) || 
            empty($_POST['source_account_id']) || empty($_POST['amount'])) {
            throw new Exception("All required fields must be filled");
        }

        $branch_id = intval($_POST['branch_id']);
        $deposit_account_id = intval($_POST['deposit_account_id']);
        $source_account_id = intval($_POST['source_account_id']);
        $amount = floatval($_POST['amount']);

        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero");
        }

        // Check if branch exists
        $branch_check = $conn->prepare("SELECT id FROM branches WHERE id = ?");
        $branch_check->bind_param("i", $branch_id);
        $branch_check->execute();
        if ($branch_check->get_result()->num_rows == 0) {
            throw new Exception("Selected branch does not exist");
        }

        // Start transaction
        $conn->autocommit(FALSE);

        // Generate deposit reference
        $deposit_ref = generate_deposit_reference();

        // Insert into general_journal
        $stmt = $conn->prepare("INSERT INTO general_journal (transaction_date, reference, description, created_by, branch_id) 
                               VALUES (?, ?, ?, ?, ?)");
        $transaction_date = $_POST['deposit_date'];
        $description = "Deposit: " . $_POST['description'];
        $stmt->bind_param("sssii", $transaction_date, $deposit_ref, $description, $_SESSION['user_id'], $branch_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create journal entry: " . $stmt->error);
        }
        
        $journal_id = $conn->insert_id;

        // Insert debit entry (deposit account)
        $debit_desc = "Deposit received - " . $_POST['depositor_name'];
        $stmt = $conn->prepare("INSERT INTO journal_entries (journal_id, account_id, sub_account_id, debit, description, branch_id) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidsi", $journal_id, $deposit_account_id, $_POST['deposit_sub_account_id'], $amount, $debit_desc, $branch_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create debit entry: " . $stmt->error);
        }

        // Insert credit entry (source account)
        $credit_desc = "Deposit from " . $_POST['depositor_name'];
        $stmt = $conn->prepare("INSERT INTO journal_entries (journal_id, account_id, sub_account_id, credit, description, branch_id) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidsi", $journal_id, $source_account_id, $_POST['source_sub_account_id'], $amount, $credit_desc, $branch_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create credit entry: " . $stmt->error);
        }

        // Insert into deposits table
        $stmt = $conn->prepare("INSERT INTO deposits (journal_id, deposit_date, amount, payment_method, cheque_no, 
                               depositor_name, contact_number, remarks, branch_id, created_by) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $cheque_no = $_POST['payment_method'] == 'cheque' ? $_POST['cheque_no'] : null;
        $stmt->bind_param("isdsssssii", $journal_id, $_POST['deposit_date'], $amount, $_POST['payment_method'], 
                         $cheque_no, $_POST['depositor_name'], $_POST['contact_number'], $_POST['remarks'], 
                         $branch_id, $_SESSION['user_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create deposit record: " . $stmt->error);
        }

        // Verify balance
        $debit_total = $conn->query("SELECT COALESCE(SUM(debit), 0) as total FROM journal_entries WHERE journal_id = $journal_id")->fetch_assoc()['total'];
        $credit_total = $conn->query("SELECT COALESCE(SUM(credit), 0) as total FROM journal_entries WHERE journal_id = $journal_id")->fetch_assoc()['total'];
        
        if (abs($debit_total - $credit_total) > 0.01) {
            throw new Exception("Journal entries are not balanced. Debit: $debit_total, Credit: $credit_total");
        }

        // Commit transaction
        $conn->commit();
        
        // Redirect to print receipt
        header("Location: print_deposit_receipt.php?id=$journal_id");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Failed to record deposit: " . $e->getMessage();
    }
}

// Now include header after all potential redirects
require_once '../includes/header.php';

// Get data for dropdowns
$branches = $conn->query("SELECT * FROM branches WHERE status = 'active' ORDER BY name");

// Check if branches exist
if ($branches->num_rows == 0) {
    $_SESSION['error'] = "No branches available. Please add branches first before recording deposits.";
}

// Get deposit accounts (Cash, Bank, etc.)
$deposit_accounts = $conn->query("SELECT * FROM chart_of_accounts 
                                 WHERE category_id IN 
                                 (SELECT id FROM account_categories WHERE name IN ('Assets'))
                                 AND is_active = 1 
                                 AND (account_name LIKE '%Cash%' OR account_name LIKE '%Bank%' OR account_name LIKE '%Cash%')
                                 ORDER BY account_code");

// Get source accounts (Revenue, Liability, etc.)
$source_accounts = $conn->query("SELECT * FROM chart_of_accounts 
                                WHERE category_id IN 
                                (SELECT id FROM account_categories WHERE name IN ('Revenue', 'Liability', 'Equity'))
                                AND is_active = 1 ORDER BY account_code");

// Get all active sub-accounts
$sub_accounts = $conn->query("SELECT * FROM sub_accounts WHERE is_active = 1 ORDER BY parent_account_id ASC, sub_account_code ASC");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Record Deposit</h2>
            <p class="mb-0">Record cash/cheque received by the company</p>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if ($branches->num_rows == 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> No branches are available. Please add branches first before recording deposits.
                </div>
            <?php else: ?>
            <form method="POST" id="depositForm">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">Select Branch</option>
                            <?php 
                            if ($branches->num_rows > 0):
                                while($branch = $branches->fetch_assoc()): 
                            ?>
                                <option value="<?= htmlspecialchars($branch['id']) ?>">
                                    <?= htmlspecialchars($branch['name']) ?>
                                </option>
                            <?php 
                                endwhile;
                            endif; 
                            ?>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Branch is required for proper financial reporting
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Deposit Reference</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars(generate_deposit_reference()) ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Deposit Date <span class="text-danger">*</span></label>
                        <input type="date" name="deposit_date" class="form-control" required 
                               value="<?= date('Y-m-d') ?>" 
                               max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required id="paymentMethod">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online_payment">Online Payment</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3" id="chequeDetails" style="display:none;">
                    <div class="col-md-6">
                        <label class="form-label">Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control" placeholder="Enter cheque number">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Depositor Name <span class="text-danger">*</span></label>
                        <input type="text" name="depositor_name" class="form-control" required 
                               placeholder="Enter depositor's full name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" 
                               placeholder="Enter contact number (optional)">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required 
                               placeholder="e.g., Course registration fee, Test payment, etc.">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required 
                               placeholder="0.00" id="amountInput">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" 
                               placeholder="Additional notes (optional)">
                    </div>
                </div>
                
                <h4 class="mt-4">Account Details</h4>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Deposit Account (Debit) <span class="text-danger">*</span></label>
                        <select name="deposit_account_id" class="form-select" required id="depositAccount">
                            <option value="">Select Deposit Account</option>
                            <?php while($account = $deposit_accounts->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($account['id']) ?>">
                                    <?= htmlspecialchars($account['account_code'] . ' - ' . $account['account_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            This account will be debited (increased) - usually Cash or Bank
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Source Account (Credit) <span class="text-danger">*</span></label>
                        <select name="source_account_id" class="form-select" required id="sourceAccount">
                            <option value="">Select Source Account</option>
                            <?php while($account = $source_accounts->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($account['id']) ?>">
                                    <?= htmlspecialchars($account['account_code'] . ' - ' . $account['account_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            This account will be credited (increased) - usually Revenue or Liability
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Deposit Sub Account</label>
                        <select name="deposit_sub_account_id" class="form-select" id="depositSubAccount">
                            <option value="">None</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Source Sub Account</label>
                        <select name="source_sub_account_id" class="form-select" id="sourceSubAccount">
                            <option value="">None</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success float-end me-2">
                            <i class="fas fa-save me-1"></i> Record Deposit
                        </button>
                        <button type="button" class="btn btn-secondary float-end me-2" onclick="window.location.href='deposits.php'">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show/hide cheque details based on payment method
    $('#paymentMethod').change(function() {
        if ($(this).val() === 'cheque') {
            $('#chequeDetails').show();
            $('input[name="cheque_no"]').prop('required', true);
        } else {
            $('#chequeDetails').hide();
            $('input[name="cheque_no"]').prop('required', false);
        }
    });
    
    // Load sub accounts for deposit account
    $('#depositAccount').change(function() {
        const accountId = $(this).val();
        const subAccountSelect = $('#depositSubAccount');
        
        if (accountId) {
            $.ajax({
                url: 'get_sub_accounts.php',
                method: 'POST',
                data: { account_id: accountId },
                success: function(response) {
                    subAccountSelect.html(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading sub-accounts: " + error);
                    subAccountSelect.html('<option value="">Error loading sub-accounts</option>');
                }
            });
        } else {
            subAccountSelect.html('<option value="">None</option>');
        }
    });
    
    // Load sub accounts for source account
    $('#sourceAccount').change(function() {
        const accountId = $(this).val();
        const subAccountSelect = $('#sourceSubAccount');
        
        if (accountId) {
            $.ajax({
                url: 'get_sub_accounts.php',
                method: 'POST',
                data: { account_id: accountId },
                success: function(response) {
                    subAccountSelect.html(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading sub-accounts: " + error);
                    subAccountSelect.html('<option value="">Error loading sub-accounts</option>');
                }
            });
        } else {
            subAccountSelect.html('<option value="">None</option>');
        }
    });
    
    // Format amount input
    $('#amountInput').on('input', function() {
        let value = $(this).val();
        if (value < 0) {
            $(this).val('');
            alert("Amount cannot be negative");
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
