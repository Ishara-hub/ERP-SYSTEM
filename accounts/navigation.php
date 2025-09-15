<?php
// Navigation menu for the Accounts section
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">
            <i class="fas fa-graduation-cap me-2"></i>Student MSYS
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Main Navigation -->
                <li class="nav-item">
                    <a class="nav-link" href="../student/dashboard.php">
                        <i class="fas fa-home me-1"></i>Dashboard
                    </a>
                </li>
                
                <!-- Accounts Section -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($current_page, 'chart_of_accounts') !== false || strpos($current_page, 'journal_entry') !== false || strpos($current_page, 'general_ledger') !== false ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-book me-1"></i>General Ledger
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="chart_of_accounts.php">
                            <i class="fas fa-list me-2"></i>Chart of Accounts
                        </a></li>
                        <li><a class="dropdown-item" href="journal_entry.php">
                            <i class="fas fa-edit me-2"></i>Journal Entries
                        </a></li>
                        <li><a class="dropdown-item" href="general_ledger.php">
                            <i class="fas fa-book-open me-2"></i>General Ledger
                        </a></li>
                        <li><a class="dropdown-item" href="trial_balance.php">
                            <i class="fas fa-balance-scale me-2"></i>Trial Balance
                        </a></li>
                        <li><a class="dropdown-item" href="balance_sheet.php">
                            <i class="fas fa-chart-pie me-2"></i>Balance Sheet
                        </a></li>
                        <li><a class="dropdown-item" href="income_statement.php">
                            <i class="fas fa-chart-line me-2"></i>Income Statement
                        </a></li>
                    </ul>
                </li>
                
                <!-- Invoice Management -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($current_page, 'invoice') !== false ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-file-invoice me-1"></i>Invoices
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="invoices.php">
                            <i class="fas fa-plus me-2"></i>Create Invoice
                        </a></li>
                        <li><a class="dropdown-item" href="invoice_list.php">
                            <i class="fas fa-list me-2"></i>All Invoices
                        </a></li>
                        <li><a class="dropdown-item" href="receivables.php">
                            <i class="fas fa-money-bill-wave me-2"></i>Accounts Receivable
                        </a></li>
                        <li><a class="dropdown-item" href="aging_report.php">
                            <i class="fas fa-chart-bar me-2"></i>Aging Report
                        </a></li>
                    </ul>
                </li>
                
                <!-- Items & Inventory -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($current_page, 'item') !== false || strpos($current_page, 'inventory') !== false ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-boxes me-1"></i>Items & Inventory
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="items.php">
                            <i class="fas fa-plus me-2"></i>Manage Items
                        </a></li>
                        <li><a class="dropdown-item" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory Control
                        </a></li>
                        <li><a class="dropdown-item" href="stock_movements.php">
                            <i class="fas fa-exchange-alt me-2"></i>Stock Movements
                        </a></li>
                        <li><a class="dropdown-item" href="low_stock_report.php">
                            <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Report
                        </a></li>
                    </ul>
                </li>
                
                <!-- Payments -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($current_page, 'payment') !== false ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-credit-card me-1"></i>Payments
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="account_payment.php">
                            <i class="fas fa-plus me-2"></i>Record Payment
                        </a></li>
                        <li><a class="dropdown-item" href="view_payments.php">
                            <i class="fas fa-list me-2"></i>Payment History
                        </a></li>
                        <li><a class="dropdown-item" href="payment_reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Payment Reports
                        </a></li>
                    </ul>
                </li>
                
                <!-- Reports -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-bar me-1"></i>Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="financial_reports.php">
                            <i class="fas fa-chart-line me-2"></i>Financial Reports
                        </a></li>
                        <li><a class="dropdown-item" href="student_ledger.php">
                            <i class="fas fa-user-graduate me-2"></i>Student Ledger
                        </a></li>
                        <li><a class="dropdown-item" href="revenue_analysis.php">
                            <i class="fas fa-chart-pie me-2"></i>Revenue Analysis
                        </a></li>
                        <li><a class="dropdown-item" href="inventory_reports.php">
                            <i class="fas fa-boxes me-2"></i>Inventory Reports
                        </a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- User Menu -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?= $_SESSION['username'] ?? 'User' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../profile.php">
                            <i class="fas fa-user-edit me-2"></i>Profile
                        </a></li>
                        <li><a class="dropdown-item" href="../settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mt-3">
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Accounts</a></li>
            <?php
            // Dynamic breadcrumb based on current page
            $page_titles = [
                'chart_of_accounts' => 'Chart of Accounts',
                'journal_entry' => 'Journal Entries',
                'general_ledger' => 'General Ledger',
                'trial_balance' => 'Trial Balance',
                'balance_sheet' => 'Balance Sheet',
                'income_statement' => 'Income Statement',
                'invoices' => 'Create Invoice',
                'invoice_list' => 'All Invoices',
                'receivables' => 'Accounts Receivable',
                'aging_report' => 'Aging Report',
                'items' => 'Manage Items',
                'inventory' => 'Inventory Control',
                'stock_movements' => 'Stock Movements',
                'low_stock_report' => 'Low Stock Report',
                'account_payment' => 'Record Payment',
                'view_payments' => 'Payment History',
                'payment_reports' => 'Payment Reports',
                'financial_reports' => 'Financial Reports',
                'student_ledger' => 'Student Ledger',
                'revenue_analysis' => 'Revenue Analysis',
                'inventory_reports' => 'Inventory Reports'
            ];
            
            if (isset($page_titles[$current_page])) {
                echo '<li class="breadcrumb-item active" aria-current="page">' . $page_titles[$current_page] . '</li>';
            }
            ?>
        </ol>
    </div>
</nav>

<!-- Quick Stats Bar -->
<div class="container-fluid mb-3">
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Total Receivables</small>
                            <h6 class="mb-0" id="quick-receivables">Loading...</h6>
                        </div>
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Overdue Amount</small>
                            <h6 class="mb-0" id="quick-overdue">Loading...</h6>
                        </div>
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Inventory Value</small>
                            <h6 class="mb-0" id="quick-inventory">Loading...</h6>
                        </div>
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Low Stock Items</small>
                            <h6 class="mb-0" id="quick-lowstock">Loading...</h6>
                        </div>
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load quick stats
document.addEventListener('DOMContentLoaded', function() {
    // You can implement AJAX calls here to load real-time stats
    // For now, we'll show placeholder data
    setTimeout(function() {
        document.getElementById('quick-receivables').textContent = '$0.00';
        document.getElementById('quick-overdue').textContent = '$0.00';
        document.getElementById('quick-inventory').textContent = '$0.00';
        document.getElementById('quick-lowstock').textContent = '0';
    }, 500);
});
</script>
