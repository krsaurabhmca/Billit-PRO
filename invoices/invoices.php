<?php
/**
 * ============================================================================
 * INVOICES LISTING PAGE
 * ============================================================================
 * Purpose: Display all invoices with search and filter
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Invoices";

// Include header
require_once '../includes/header.php';

// ============================================================================
// SEARCH AND FILTER PROCESSING
// ============================================================================

$search_term = '';
$status_filter = '';
$payment_filter = '';
$date_from = '';
$date_to = '';

if (isset($_GET['search'])) {
    $search_term = sanitize_sql($connection, $_GET['search']);
}

if (isset($_GET['status'])) {
    $status_filter = sanitize_sql($connection, $_GET['status']);
}

if (isset($_GET['payment'])) {
    $payment_filter = sanitize_sql($connection, $_GET['payment']);
}

if (isset($_GET['date_from'])) {
    $date_from = sanitize_sql($connection, $_GET['date_from']);
}

if (isset($_GET['date_to'])) {
    $date_to = sanitize_sql($connection, $_GET['date_to']);
}

// ============================================================================
// BUILD QUERY WITH FILTERS
// ============================================================================

$query = "SELECT i.*, c.customer_name, c.customer_type
          FROM invoices i
          INNER JOIN customers c ON i.customer_id = c.customer_id
          WHERE 1=1";

// Add search condition
if (!empty($search_term)) {
    $query .= " AND (i.invoice_number LIKE '%{$search_term}%' 
                OR c.customer_name LIKE '%{$search_term}%')";
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND i.invoice_status = '{$status_filter}'";
}

// Add payment filter
if (!empty($payment_filter)) {
    $query .= " AND i.payment_status = '{$payment_filter}'";
}

// Add date range filter
if (!empty($date_from)) {
    $query .= " AND i.invoice_date >= '{$date_from}'";
}

if (!empty($date_to)) {
    $query .= " AND i.invoice_date <= '{$date_to}'";
}

$query .= " ORDER BY i.invoice_date DESC, i.invoice_id DESC";

// Execute query
$invoices_result = db_query($connection, $query);

// ============================================================================
// CALCULATE SUMMARY STATISTICS
// ============================================================================

$summary_query = "SELECT 
                  COUNT(*) as total_invoices,
                  SUM(CASE WHEN invoice_status = 'finalized' THEN total_amount ELSE 0 END) as total_sales,
                  SUM(CASE WHEN payment_status = 'unpaid' THEN amount_due ELSE 0 END) as total_due
                  FROM invoices
                  WHERE 1=1";

if (!empty($status_filter)) {
    $summary_query .= " AND invoice_status = '{$status_filter}'";
}

if (!empty($payment_filter)) {
    $summary_query .= " AND payment_status = '{$payment_filter}'";
}

if (!empty($date_from)) {
    $summary_query .= " AND invoice_date >= '{$date_from}'";
}

if (!empty($date_to)) {
    $summary_query .= " AND invoice_date <= '{$date_to}'";
}

$summary = db_fetch_one($connection, $summary_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">🧾</span>
        Invoice Management
    </h2>
    <div class="page-actions">
        <a href="create_invoice.php" class="btn btn-primary">
            <span class="btn-icon">+</span>
            Create Invoice
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- SUMMARY STATISTICS -->
<!-- ================================================================ -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">📋</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($summary['total_invoices']); ?></div>
            <div class="stat-label">Total Invoices</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-green">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($summary['total_sales']); ?></div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-red">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($summary['total_due']); ?></div>
            <div class="stat-label">Amount Due</div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- SEARCH AND FILTER FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <!-- Search Input -->
                <div class="form-group col-md-3">
                    <input type="text" name="search" class="form-control" 
                           placeholder="🔍 Search invoice/customer..."
                           value="<?php echo escape_html($search_term); ?>">
                </div>
                
                <!-- Status Filter -->
                <div class="form-group col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo ($status_filter === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="finalized" <?php echo ($status_filter === 'finalized') ? 'selected' : ''; ?>>Finalized</option>
                        <option value="cancelled" <?php echo ($status_filter === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <!-- Payment Filter -->
                <div class="form-group col-md-2">
                    <select name="payment" class="form-control">
                        <option value="">All Payments</option>
                        <option value="unpaid" <?php echo ($payment_filter === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="partial" <?php echo ($payment_filter === 'partial') ? 'selected' : ''; ?>>Partial</option>
                        <option value="paid" <?php echo ($payment_filter === 'paid') ? 'selected' : ''; ?>>Paid</option>
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="form-group col-md-2">
                    <input type="date" name="date_from" class="form-control" 
                           value="<?php echo escape_html($date_from); ?>">
                </div>
                
                <!-- Date To -->
                <div class="form-group col-md-2">
                    <input type="date" name="date_to" class="form-control" 
                           value="<?php echo escape_html($date_to); ?>">
                </div>
                
                <!-- Filter Button -->
                <div class="form-group col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- INVOICES TABLE -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoice List</h3>
        <span class="card-subtitle">
            Showing: <?php echo mysqli_num_rows($invoices_result); ?> invoices
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($invoices_result && mysqli_num_rows($invoices_result) > 0): ?>
                        <?php while ($invoice = mysqli_fetch_assoc($invoices_result)): ?>
                            <tr>
                                <td><strong><?php echo escape_html($invoice['invoice_number']); ?></strong></td>
                                <td><?php echo format_date($invoice['invoice_date']); ?></td>
                                <td><?php echo escape_html($invoice['customer_name']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $invoice['customer_type'] === 'B2B' ? 'info' : 'success'; ?>">
                                        <?php echo $invoice['customer_type']; ?>
                                    </span>
                                </td>
                                <td><strong><?php echo format_currency($invoice['total_amount']); ?></strong></td>
                                <td><?php echo format_currency($invoice['amount_paid']); ?></td>
                                <td><?php echo format_currency($invoice['amount_due']); ?></td>
                                <td>
                                    <?php if ($invoice['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php elseif ($invoice['payment_status'] === 'partial'): ?>
                                        <span class="badge badge-warning">Partial</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($invoice['invoice_status'] === 'finalized'): ?>
                                        <span class="badge badge-success">Finalized</span>
                                    <?php elseif ($invoice['invoice_status'] === 'draft'): ?>
                                        <span class="badge badge-warning">Draft</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="view_invoice.php?id=<?php echo $invoice['invoice_id']; ?>" 
                                       class="btn-action btn-edit" title="View">👁️</a>
                                    <?php if ($invoice['invoice_status'] === 'draft'): ?>
                                        <a href="edit_invoice.php?id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn-action btn-edit" title="Edit">✏️</a>
                                    <?php endif; ?>
                                    <?php if ($invoice['invoice_status'] !== 'cancelled'): ?>
                                        <a href="delete_invoice.php?id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn-action btn-delete" title="Cancel"
                                           onclick="return confirmDelete('Are you sure you want to cancel this invoice?');">🗑️</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No invoices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
