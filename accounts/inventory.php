<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle stock adjustments
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['adjust_stock'])) {
        $item_id = $_POST['item_id'];
        $adjustment_type = $_POST['adjustment_type'];
        $quantity = $_POST['quantity'];
        $reason = $_POST['reason'];
        $reference = $_POST['reference'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get current stock
            $stmt = $conn->prepare("SELECT current_stock, item_name FROM items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            
            if (!$item) {
                throw new Exception("Item not found");
            }
            
            $current_stock = $item['current_stock'];
            $new_stock = $current_stock;
            
            if ($adjustment_type == 'add') {
                $new_stock = $current_stock + $quantity;
            } elseif ($adjustment_type == 'subtract') {
                $new_stock = max(0, $current_stock - $quantity);
            } elseif ($adjustment_type == 'set') {
                $new_stock = $quantity;
            }
            
            // Update stock
            $stmt = $conn->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_stock, $item_id);
            $stmt->execute();
            
            // Log stock movement
            $stmt = $conn->prepare("INSERT INTO stock_movements (item_id, quantity, operation, previous_stock, new_stock, user_id, created_at, reference_type, reference_id, reason) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'adjustment', ?, ?)");
            $user_id = $_SESSION['user_id'] ?? 1;
            $stmt->bind_param("iisiiiss", $item_id, $quantity, $adjustment_type, $current_stock, $new_stock, $user_id, $reference, $reason);
            $stmt->execute();
            
            $conn->commit();
            $success_message = "Stock adjusted successfully for " . $item['item_name'];
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error adjusting stock: " . $e->getMessage();
        }
    }
}

// Fetch inventory items
$inventory_items = $conn->query("
    SELECT * FROM items 
    WHERE item_type = 'inventory' 
    ORDER BY 
        CASE WHEN current_stock <= reorder_level THEN 0 ELSE 1 END,
        current_stock ASC
");

// Fetch service items
$service_items = $conn->query("
    SELECT * FROM items 
    WHERE item_type = 'service' 
    ORDER BY item_name ASC
");

// Fetch low stock items
$low_stock_items = $conn->query("
    SELECT * FROM items 
    WHERE item_type = 'inventory' AND current_stock <= reorder_level
    ORDER BY current_stock ASC
");

// Fetch recent stock movements
$recent_movements = $conn->query("
    SELECT sm.*, i.item_name, i.item_code, u.username
    FROM stock_movements sm
    JOIN items i ON sm.item_id = i.id
    LEFT JOIN users u ON sm.user_id = u.user_id
    ORDER BY sm.created_at DESC
    LIMIT 20
");

// Calculate inventory value
$total_inventory_value = $conn->query("
    SELECT SUM(current_stock * unit_price) as total_value
    FROM items 
    WHERE item_type = 'inventory'
")->fetch_assoc()['total_value'] ?? 0;

$total_inventory_items = $conn->query("
    SELECT COUNT(*) as total FROM items WHERE item_type = 'inventory'
")->fetch_assoc()['total'];

$total_service_items = $conn->query("
    SELECT COUNT(*) as total FROM items WHERE item_type = 'service'
")->fetch_assoc()['total'];

$total_items = $total_inventory_items + $total_service_items;
$low_stock_count = $low_stock_items->num_rows;
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-boxes me-2"></i>Items & Inventory Management</h2>
            <p class="text-muted">Monitor and manage both inventory items (with stock control) and service items</p>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Items</h6>
                            <h3><?= $total_items ?></h3>
                            <small>Inventory: <?= $total_inventory_items ?> | Services: <?= $total_service_items ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Low Stock Items</h6>
                            <h3><?= $low_stock_count ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Inventory Value</h6>
                            <h3>$<?= number_format($total_inventory_value, 2) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Stock Movements</h6>
                            <h3><?= $recent_movements->num_rows ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Items Management with Tabs -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-boxes me-2"></i>Items Management</h4>
                    <a href="items.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Item
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs mb-3" id="itemsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="true">
                                <i class="fas fa-boxes me-2"></i>Inventory Items (<?= $total_inventory_items ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-controls="services" aria-selected="false">
                                <i class="fas fa-cogs me-2"></i>Service Items (<?= $total_service_items ?>)
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="itemsTabContent">
                        <!-- Inventory Items Tab -->
                        <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                            <?php if ($inventory_items->num_rows > 0): ?>
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Item Code</th>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Unit Price</th>
                                            <th>Stock Value</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $inventory_items->data_seek(0);
                                        while($item = $inventory_items->fetch_assoc()): 
                                        ?>
                                        <tr class="<?= $item['current_stock'] <= $item['reorder_level'] ? 'table-warning' : '' ?>">
                                            <td><strong><?= htmlspecialchars($item['item_code']) ?></strong></td>
                                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                                            <td><?= htmlspecialchars($item['category']) ?></td>
                                            <td>
                                                <span class="badge <?= $item['current_stock'] <= $item['reorder_level'] ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= $item['current_stock'] ?>
                                                </span>
                                            </td>
                                            <td><?= $item['reorder_level'] ?></td>
                                            <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                            <td>$<?= number_format($item['current_stock'] * $item['unit_price'], 2) ?></td>
                                            <td>
                                                <?php if ($item['current_stock'] <= $item['reorder_level']): ?>
                                                    <span class="badge bg-danger">Low Stock</span>
                                                <?php elseif ($item['current_stock'] == 0): ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="showStockModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['item_name']) ?>', <?= $item['current_stock'] ?>)">
                                                    <i class="fas fa-edit"></i> Adjust
                                                </button>
                                                <a href="item_history.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No inventory items found</h5>
                                    <p class="text-muted">You haven't created any inventory items yet.</p>
                                    <a href="items.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create Inventory Item
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Service Items Tab -->
                        <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
                            <?php if ($service_items->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Item Code</th>
                                                <th>Item Name</th>
                                                <th>Category</th>
                                                <th>Unit Price</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($item = $service_items->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($item['item_code']) ?></strong></td>
                                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                                <td><?= htmlspecialchars($item['category']) ?></td>
                                                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                                <td><?= htmlspecialchars(substr($item['description'] ?? '', 0, 50)) ?><?= strlen($item['description'] ?? '') > 50 ? '...' : '' ?></td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                                <td>
                                                    <a href="items.php?edit=<?= $item['id'] ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No service items found</h5>
                                    <p class="text-muted">You haven't created any service items yet.</p>
                                    <a href="items.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create Service Item
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions & Low Stock Alerts -->
        <div class="col-md-4">
            <!-- Low Stock Alerts -->
            <div class="card shadow mb-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $low_stock_items->data_seek(0);
                    $alert_count = 0;
                    while($item = $low_stock_items->fetch_assoc()): 
                        $alert_count++;
                        if ($alert_count > 8) break;
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <small class="text-muted"><?= htmlspecialchars($item['item_code']) ?></small><br>
                                <small><?= htmlspecialchars($item['item_name']) ?></small>
                            </div>
                            <div class="text-end">
                                <small class="text-danger">Stock: <?= $item['current_stock'] ?></small><br>
                                <small class="text-muted">Reorder: <?= $item['reorder_level'] ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if ($alert_count == 0): ?>
                        <p class="text-success text-center mt-3">
                            <i class="fas fa-check-circle"></i> All items have sufficient stock!
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Stock Movements -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Recent Movements</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $recent_movements->data_seek(0);
                    $movement_count = 0;
                    while($movement = $recent_movements->fetch_assoc()): 
                        $movement_count++;
                        if ($movement_count > 6) break;
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <small class="text-muted"><?= htmlspecialchars($movement['item_code']) ?></small><br>
                                <small><?= htmlspecialchars($movement['operation']) ?> <?= $movement['quantity'] ?></small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted"><?= date('d/m', strtotime($movement['created_at'])) ?></small><br>
                                <small class="text-muted"><?= htmlspecialchars($movement['username'] ?? 'System') ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="text-center mt-3">
                        <a href="stock_movements.php" class="btn btn-outline-info btn-sm">
                            View All Movements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="stock_item_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" id="stock_item_name" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" id="stock_current_stock" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="adjustment_type" class="form-select" required onchange="toggleAdjustmentFields()">
                            <option value="">Select Type</option>
                            <option value="add">Add Stock</option>
                            <option value="subtract">Subtract Stock</option>
                            <option value="set">Set Stock Level</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason" class="form-select" required>
                            <option value="">Select Reason</option>
                            <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                            <option value="adjustment">Stock Adjustment</option>
                            <option value="damage">Damage/Loss</option>
                            <option value="return">Return</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control" placeholder="Reference number or note">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="adjust_stock" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showStockModal(itemId, itemName, currentStock) {
    document.getElementById('stock_item_id').value = itemId;
    document.getElementById('stock_item_name').value = itemName;
    document.getElementById('stock_current_stock').value = currentStock;
    
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}

function toggleAdjustmentFields() {
    const adjustmentType = document.querySelector('select[name="adjustment_type"]').value;
    const quantityField = document.querySelector('input[name="quantity"]');
    
    if (adjustmentType === 'set') {
        quantityField.placeholder = 'Enter new stock level';
    } else {
        quantityField.placeholder = 'Enter quantity';
    }
}

// Auto-refresh every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>

<?php include '../includes/footer.php'; ?>
