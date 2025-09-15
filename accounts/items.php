<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        $item_code = $_POST['item_code'];
        $item_name = $_POST['item_name'];
        $item_type = $_POST['item_type'];
        $category = $_POST['category'];
        $unit_price = $_POST['unit_price'];
        $description = $_POST['description'];
        $income_account_id = $_POST['income_account_id'] ?: null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // For inventory items
        $current_stock = ($item_type == 'inventory') ? $_POST['current_stock'] : 0;
        $reorder_level = ($item_type == 'inventory') ? $_POST['reorder_level'] : 0;
        
        $stmt = $conn->prepare("INSERT INTO items (item_code, item_name, item_type, category, unit_price, description, current_stock, reorder_level, income_account_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssdsiiii", $item_code, $item_name, $item_type, $category, $unit_price, $description, $current_stock, $reorder_level, $income_account_id, $is_active);
        
        if ($stmt->execute()) {
            $success_message = "Item added successfully!";
        } else {
            $error_message = "Error adding item: " . $conn->error;
        }
    }
    
    if (isset($_POST['update_stock'])) {
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        $operation = $_POST['operation']; // 'add' or 'subtract'
        
        $current_stock = $conn->query("SELECT current_stock FROM items WHERE id = $item_id")->fetch_assoc()['current_stock'];
        
        if ($operation == 'add') {
            $new_stock = $current_stock + $quantity;
        } else {
            $new_stock = max(0, $current_stock - $quantity);
        }
        
        $stmt = $conn->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_stock, $item_id);
        
        if ($stmt->execute()) {
            // Log stock movement
            $stmt = $conn->prepare("INSERT INTO stock_movements (item_id, quantity, operation, previous_stock, new_stock, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $user_id = $_SESSION['user_id'] ?? 1;
            $stmt->bind_param("iisiii", $item_id, $quantity, $operation, $current_stock, $new_stock, $user_id);
            $stmt->execute();
            
            $success_message = "Stock updated successfully!";
        } else {
            $error_message = "Error updating stock: " . $conn->error;
        }
    }
}

// Fetch all items
$items = $conn->query("
    SELECT * FROM items 
    ORDER BY item_type, category, item_name
");

// Fetch categories for dropdown
$categories = $conn->query("SELECT DISTINCT category FROM items WHERE category IS NOT NULL ORDER BY category");
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Add Item Form -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-plus me-2"></i>Add New Item</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Item Code</label>
                            <input type="text" name="item_code" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Item Type</label>
                            <select name="item_type" class="form-select" required onchange="toggleInventoryFields()">
                                <option value="">Select Type</option>
                                <option value="service">Service</option>
                                <option value="inventory">Inventory</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="unit_price" class="form-control" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <!-- Income Account Selection -->
                        <div class="mb-3">
                            <label class="form-label">Income Account <span class="text-muted">(for journal entries)</span></label>
                            <select name="income_account_id" class="form-select" required>
                                <option value="">Select Income Account</option>
                                <?php
                                // Fetch income-related sub-accounts
                                $income_accounts = $conn->query("
                                    SELECT sa.id, sa.sub_account_name, coa.account_name 
                                    FROM sub_accounts sa 
                                    JOIN chart_of_accounts coa ON sa.parent_account_id = coa.id 
                                    WHERE sa.is_active = 1 
                                    AND (sa.sub_account_name LIKE '%Revenue%' OR sa.sub_account_name LIKE '%Income%' OR sa.sub_account_name LIKE '%Sales%')
                                    ORDER BY coa.account_name, sa.sub_account_name
                                ");
                                while ($account = $income_accounts->fetch_assoc()):
                                ?>
                                <option value="<?= $account['id'] ?>">
                                    <?= htmlspecialchars($account['sub_account_name']) ?> 
                                    (<?= htmlspecialchars($account['account_name']) ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                This account will be credited when creating invoices with this item
                            </div>
                        </div>
                        
                        <!-- Inventory specific fields -->
                        <div id="inventory-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Current Stock</label>
                                <input type="number" name="current_stock" class="form-control" value="0" min="0">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_item" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i> Add Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Items List -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4><i class="fas fa-list me-2"></i>Items Management</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Income Account</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item_code']) ?></td>
                                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td>
                                        <span class="badge <?= $item['item_type'] == 'service' ? 'bg-primary' : 'bg-success' ?>">
                                            <?= ucfirst($item['item_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($item['category']) ?></td>
                                    <td><?= number_format($item['unit_price'], 2) ?></td>
                                    <td>
                                        <?php if ($item['item_type'] == 'inventory'): ?>
                                            <span class="<?= $item['current_stock'] <= $item['reorder_level'] ? 'text-danger' : 'text-success' ?>">
                                                <?= $item['current_stock'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $income_account_name = 'N/A';
                                        if ($item['income_account_id']) {
                                            $income_account_info = $conn->query("SELECT sub_account_name FROM sub_accounts WHERE id = " . $item['income_account_id'])->fetch_assoc();
                                            if ($income_account_info) {
                                                $income_account_name = htmlspecialchars($income_account_info['sub_account_name']);
                                            }
                                        }
                                        echo $income_account_name;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $item['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="editItem(<?= $item['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($item['item_type'] == 'inventory'): ?>
                                            <button class="btn btn-sm btn-warning" onclick="showStockModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['item_name']) ?>')">
                                                <i class="fas fa-boxes"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteItem(<?= $item['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="stock_item_id">
                    <p><strong>Item:</strong> <span id="stock_item_name"></span></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Operation</label>
                        <select name="operation" class="form-select" required>
                            <option value="add">Add Stock</option>
                            <option value="subtract">Subtract Stock</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_stock" class="btn btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleInventoryFields() {
    const itemType = document.querySelector('select[name="item_type"]').value;
    const inventoryFields = document.getElementById('inventory-fields');
    
    if (itemType === 'inventory') {
        inventoryFields.style.display = 'block';
    } else {
        inventoryFields.style.display = 'none';
    }
}

function showStockModal(itemId, itemName) {
    document.getElementById('stock_item_id').value = itemId;
    document.getElementById('stock_item_name').textContent = itemName;
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}

function editItem(itemId) {
    // TODO: Implement edit functionality
    alert('Edit functionality will be implemented next');
}

function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this item?')) {
        // TODO: Implement delete functionality
        alert('Delete functionality will be implemented next');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
