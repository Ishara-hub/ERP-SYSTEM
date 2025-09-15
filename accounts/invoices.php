<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_invoice'])) {
        $student_id = $_POST['student_id'];
        $invoice_date = $_POST['invoice_date'];
        $due_date = $_POST['due_date'];
        $notes = $_POST['notes'];
        $invoice_type = $_POST['invoice_type'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Validate required data
            if (empty($student_id) || empty($invoice_date) || empty($due_date) || empty($invoice_type)) {
                throw new Exception("All required fields must be filled.");
            }
            
            // Create invoice header
            $stmt = $conn->prepare("INSERT INTO invoices (student_id, invoice_number, invoice_date, due_date, notes, invoice_type, status, total_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', 0, NOW())");
            $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($student_id, 5, '0', STR_PAD_LEFT);
            
            // Debug: Log invoice number
            error_log("Generated invoice number: $invoice_number");
            
            $stmt->bind_param("isssss", $student_id, $invoice_number, $invoice_date, $due_date, $notes, $invoice_type);
            $stmt->execute();
            
            $invoice_id = $conn->insert_id;
            $total_amount = 0;
            
            // Process line items
            $item_ids = $_POST['item_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $unit_prices = $_POST['unit_price'] ?? [];
            $descriptions = $_POST['description'] ?? [];
            
            // Validate that at least one item is selected
            if (empty($item_ids) || count(array_filter($item_ids)) == 0) {
                throw new Exception("At least one item must be selected.");
            }
            
            for ($i = 0; $i < count($item_ids); $i++) {
                if (!empty($item_ids[$i]) && $quantities[$i] > 0) {
                    $item_id = $item_ids[$i];
                    $quantity = $quantities[$i];
                    $unit_price = $unit_prices[$i];
                    $description = $descriptions[$i];
                    $line_total = $quantity * $unit_price;
                    
                    // Insert invoice line item
                    $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_id, quantity, unit_price, description, line_total) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiddsd", $invoice_id, $item_id, $quantity, $unit_price, $description, $line_total);
                    $stmt->execute();
                    
                    // Update inventory if applicable
                    $item_type = $conn->query("SELECT item_type, current_stock FROM items WHERE id = $item_id")->fetch_assoc();
                    if ($item_type['item_type'] == 'inventory') {
                        $new_stock = max(0, $item_type['current_stock'] - $quantity);
                        $stmt = $conn->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
                        $stmt->bind_param("ii", $new_stock, $item_id);
                        $stmt->execute();
                        
                        // Log stock movement
                        $prev_stock = $item_type['current_stock']; // store array element into a variable

                        $stmt = $conn->prepare("
                            INSERT INTO stock_movements 
                            (item_id, quantity, operation, previous_stock, new_stock, user_id, created_at, reference_type, reference_id) 
                            VALUES (?, ?, 'subtract', ?, ?, ?, NOW(), 'invoice', ?)
                        ");

                        $stmt->bind_param("iiiiii", $item_id, $quantity, $prev_stock, $new_stock, $user_id, $invoice_id);
                        $stmt->execute();
                    }
                    
                    $total_amount += $line_total;
                }
            }
            
            // Update invoice total
            $stmt = $conn->prepare("UPDATE invoices SET total_amount = ? WHERE id = ?");
            $stmt->bind_param("di", $total_amount, $invoice_id);
            $stmt->execute();
            
            // Create journal entry using your existing structure
            // First create journal header in general_journal table
            $stmt = $conn->prepare("INSERT INTO general_journal (transaction_date, reference, description, created_by) VALUES (?, ?, ?, ?)");
            $reference = 'INV-' . $invoice_number;
            $description = "Invoice #$invoice_number - Student Invoice";
            $user_id = $_SESSION['user_id'] ?? 1;
            $stmt->bind_param("sssi", $invoice_date, $reference, $description, $user_id);
            $stmt->execute();
            
            $journal_id = $conn->insert_id;
            
            // Get sub-account ID for Accounts Receivable
            $receivable_result = $conn->query("SELECT id, parent_account_id FROM sub_accounts WHERE sub_account_name LIKE '%Accounts Receivable%' AND is_active = 1 LIMIT 1");
            $receivable_sub_account = $receivable_result->fetch_assoc();
            
            if (!$receivable_sub_account) {
                throw new Exception("Required sub-account 'Accounts Receivable' not found. Please ensure it exists and is active.");
            }

            // Create debit entry (Accounts Receivable)
            $debit_desc = "Invoice #$invoice_number - Accounts Receivable";
            $receivable_parent_id = $receivable_sub_account['parent_account_id'];
            $receivable_sub_id = $receivable_sub_account['id'];
            $zero_amount = 0.00;
            
            $stmt = $conn->prepare("INSERT INTO journal_entries 
                (date, journal_id, account_id, sub_account_id, debit, credit, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiidds", $invoice_date, $journal_id, $receivable_parent_id, $receivable_sub_id, $total_amount, $zero_amount, $debit_desc);
            $stmt->execute();

            // Create credit entries for each item based on their income accounts
            // Group items by income account to create proper journal entries
            $income_accounts = [];
            
            // Fetch invoice items with their income accounts
            $invoice_items_result = $conn->query("
                SELECT ii.quantity, ii.unit_price, ii.line_total, i.income_account_id, i.item_name
                FROM invoice_items ii 
                JOIN items i ON ii.item_id = i.id 
                WHERE ii.invoice_id = $invoice_id
            ");
            
            while ($item = $invoice_items_result->fetch_assoc()) {
                $income_account_id = $item['income_account_id'];
                if (!isset($income_accounts[$income_account_id])) {
                    $income_accounts[$income_account_id] = 0;
                }
                $income_accounts[$income_account_id] += $item['line_total'];
            }
            
            // Create credit entries for each income account
            foreach ($income_accounts as $income_account_id => $amount) {
                if ($income_account_id) {
                    // Get income account details
                    $income_result = $conn->query("SELECT id, parent_account_id, sub_account_name FROM sub_accounts WHERE id = $income_account_id AND is_active = 1");
                    $income_account = $income_result->fetch_assoc();
                    
                    if ($income_account) {
                        $credit_desc = "Invoice #$invoice_number - " . $income_account['sub_account_name'];
                        $income_parent_id = $income_account['parent_account_id'];
                        $income_sub_id = $income_account['id'];
                        
                        $stmt = $conn->prepare("INSERT INTO journal_entries 
                            (date, journal_id, account_id, sub_account_id, debit, credit, description) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("siiidds", $invoice_date, $journal_id, $income_parent_id, $income_sub_id, $zero_amount, $amount, $credit_desc);
                        $stmt->execute();
                    }
                }
            }
            
            $conn->commit();
            $success_message = "Invoice created successfully! Invoice #: $invoice_number";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error creating invoice: " . $e->getMessage();
        }
    }
}

// Get student_id from URL if provided (for pre-selection from dashboard)
$pre_selected_student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Fetch students for dropdown
$students = $conn->query("
    SELECT s.student_id, s.full_name, s.contact_number, c.course_name 
    FROM students s 
    LEFT JOIN registrations r ON s.student_id = r.student_id 
    LEFT JOIN batches b ON r.batch_id = b.batch_id 
    LEFT JOIN courses c ON b.course_id = c.course_id 
    ORDER BY s.full_name
");

// Get pre-selected student info if provided
$pre_selected_student = null;
if ($pre_selected_student_id > 0) {
    $stmt = $conn->prepare("
        SELECT s.student_id, s.full_name, s.contact_number, c.course_name 
        FROM students s 
        LEFT JOIN registrations r ON s.student_id = r.student_id 
        LEFT JOIN batches b ON r.batch_id = b.batch_id 
        LEFT JOIN courses c ON b.course_id = c.course_id 
        WHERE s.student_id = ?
    ");
    $stmt->bind_param("i", $pre_selected_student_id);
    $stmt->execute();
    $pre_selected_student = $stmt->get_result()->fetch_assoc();
}

// Fetch items for dropdown
$items = $conn->query("
    SELECT i.*, sa.sub_account_name as income_account_name 
    FROM items i 
    LEFT JOIN sub_accounts sa ON i.income_account_id = sa.id 
    WHERE i.is_active = 1 
    ORDER BY i.item_name
");

// Fetch recent invoices
$recent_invoices = $conn->query("
    SELECT i.*, s.full_name, s.contact_number 
    FROM invoices i 
    JOIN students s ON i.student_id = s.student_id 
    ORDER BY i.created_at DESC 
    LIMIT 10
");
?>

<div class="container-fluid mt-4">
    <?php if ($pre_selected_student): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Creating Invoice for: <?= htmlspecialchars($pre_selected_student['full_name']) ?></h6>
                            <small>Course: <?= htmlspecialchars($pre_selected_student['course_name'] ?? 'N/A') ?> | Contact: <?= htmlspecialchars($pre_selected_student['contact_number']) ?></small>
                        </div>
                        <div>
                            <a href="../student/dashboard.php?id=<?= $pre_selected_student_id ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Create Invoice Form -->
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-file-invoice me-2"></i>Create New Invoice</h4>
                </div>

                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="invoiceForm">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Student</label>
                                <select name="student_id" class="form-select" required onchange="loadStudentInfo(this.value)">
                                    <option value="">Select Student</option>
                                    <?php 
                                    $students->data_seek(0); // Reset result pointer
                                    while($student = $students->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $student['student_id'] ?>" <?= ($pre_selected_student_id > 0 && $student['student_id'] == $pre_selected_student_id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($student['full_name']) ?> - <?= htmlspecialchars($student['course_name'] ?? 'N/A') ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Type</label>
                                <select name="invoice_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="registration">Registration Fee</option>
                                    <option value="course">Course Fee</option>
                                    <option value="test">Test Fee</option>
                                    <option value="other">Other Services</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Additional notes...">
                            </div>
                        </div>
                        
                        <!-- Invoice Items -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Invoice Items</h6>
                            </div>
                            <div class="card-body">
                                <div id="invoice-items">
                                    <div class="row g-3 mb-3 item-row">
                                        <div class="col-md-3">
                                            <label class="form-label">Item</label>
                                            <select name="item_id[]" class="form-select item-select" onchange="updateItemPrice(this)">
                                                <option value="">Select Item</option>
                                                <?php while($item = $items->fetch_assoc()): ?>
                                                    <option value="<?= $item['id'] ?>" data-price="<?= $item['unit_price'] ?>">
                                                        <?= htmlspecialchars($item['item_name']) ?> - 
                                                        <?= number_format($item['unit_price'], 2) ?>
                                                        <?php if ($item['income_account_name']): ?>
                                                            (<?= htmlspecialchars($item['income_account_name']) ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" name="quantity[]" class="form-control quantity-input" value="1" min="1" onchange="calculateLineTotal(this)">
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <label class="form-label">Unit Price</label>
                                            <input type="number" name="unit_price[]" class="form-control unit-price-input" step="0.01" onchange="calculateLineTotal(this)">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="description[]" class="form-control" placeholder="Item description">
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <label class="form-label">Line Total</label>
                                            <input type="text" class="form-control line-total-display" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-secondary btn-sm" onclick="addItemRow()">
                                    <i class="fas fa-plus me-1"></i> Add Item
                                </button>
                                
                                <div class="row mt-3">
                                    <div class="col-md-8 text-end">
                                        <h5>Total Amount: <span id="total-amount">0.00</span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" name="create_invoice" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Create Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Recent Invoices & Student Info -->
        <div class="col-md-12">
            
            <!-- Recent Invoices -->
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Invoices</h6>
                </div>
                <div class="card-body">
                    <?php while($invoice = $recent_invoices->fetch_assoc()): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($invoice['full_name']) ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= $invoice['status'] == 'paid' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= ucfirst($invoice['status']) ?>
                                </span><br>
                                <small><?= number_format($invoice['total_amount'], 2) ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let itemRowCount = 1;

function addItemRow() {
    itemRowCount++;
    const newRow = document.querySelector('.item-row').cloneNode(true);
    newRow.id = 'item-row-' + itemRowCount;
    
    // Clear values
    newRow.querySelectorAll('input, select').forEach(input => {
        if (input.type === 'number') {
            input.value = input.name.includes('quantity') ? '1' : '';
        } else if (input.type === 'text') {
            input.value = '';
        } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
    });
    
    newRow.querySelector('.line-total-display').value = '';
    
    document.getElementById('invoice-items').appendChild(newRow);
}

function updateItemPrice(select) {
    const row = select.closest('.item-row');
    const option = select.options[select.selectedIndex];
    const price = option.dataset.price || '';
    
    row.querySelector('.unit-price-input').value = price;
    calculateLineTotal(row.querySelector('.quantity-input'));
}

function calculateLineTotal(input) {
    const row = input.closest('.item-row');
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
    const lineTotal = quantity * unitPrice;
    
    row.querySelector('.line-total-display').value = lineTotal.toFixed(2);
    calculateTotalAmount();
}

function calculateTotalAmount() {
    let total = 0;
    document.querySelectorAll('.line-total-display').forEach(display => {
        total += parseFloat(display.value) || 0;
    });
    
    document.getElementById('total-amount').textContent = total.toFixed(2);
}

function loadStudentInfo(studentId) {
    const studentInfoElement = document.getElementById('student-info');
    if (!studentInfoElement) {
        return; // Element doesn't exist, exit function
    }
    
    if (!studentId) {
        studentInfoElement.innerHTML = '<p class="text-muted">Select a student to view information</p>';
        return;
    }
    
    // You can implement AJAX call here to load student details
    // For now, we'll show a placeholder
    studentInfoElement.innerHTML = '<p class="text-muted">Loading student information...</p>';
}

// Initialize first row
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalAmount();
    
    // Auto-load student info if pre-selected and element exists
    <?php if ($pre_selected_student_id > 0): ?>
    const preSelectedStudentId = <?= $pre_selected_student_id ?>;
    if (preSelectedStudentId && document.getElementById('student-info')) {
        loadStudentInfo(preSelectedStudentId);
    }
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>
