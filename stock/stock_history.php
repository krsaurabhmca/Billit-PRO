<?php
/**
 * ============================================================================
 * STOCK TRANSACTION HISTORY PAGE
 * ============================================================================
 * Purpose: Display all stock transactions with filtering options
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Stock Transaction History";

// Include header
require_once '../includes/header.php';

// ============================================================================
// FILTER PROCESSING
// ============================================================================

$product_filter = '';
$type_filter = '';
$date_from = '';
$date_to = '';

if (isset($_GET['product'])) {
    $product_filter = sanitize_sql($connection, $_GET['product']);
}

if (isset($_GET['type'])) {
    $type_filter = sanitize_sql($connection, $_GET['type']);
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

$query = "SELECT st.*, p.product_code, p.product_name, p.unit_of_measure, u.username
          FROM stock_transactions st
          INNER JOIN products p ON st.product_id = p.product_id
          LEFT JOIN users u ON st.created_by = u.user_id
          WHERE 1=1";

// Add product filter
if (!empty($product_filter)) {
    $query .= " AND st.product_id = '{$product_filter}'";
}

// Add type filter
if (!empty($type_filter)) {
    $query .= " AND st.transaction_type = '{$type_filter}'";
}

// Add date range filter
if (!empty($date_from)) {
    $query .= " AND DATE(st.transaction_date) >= '{$date_from}'";
}

if (!empty($date_to)) {
    $query .= " AND DATE(st.transaction_date) <= '{$date_to}'";
}

$query .= " ORDER BY st.transaction_date DESC, st.transaction_id DESC";

// Execute query
$transactions_result = db_query($connection, $query);

// ============================================================================
// FETCH PRODUCTS FOR FILTER DROPDOWN
// ============================================================================

$products_query = "SELECT product_id, product_code, product_name FROM products ORDER BY product_name";
$products_result = db_query($connection, $products_query);

// ============================================================================
// CALCULATE SUMMARY STATISTICS
// ============================================================================

$summary_query = "SELECT 
                  COUNT(*) as total_transactions,
                  SUM(CASE WHEN transaction_type = 'stock_in' THEN total_amount ELSE 0 END) as total_stock_in_value,
                  SUM(CASE WHEN transaction_type = 'stock_out' THEN total_amount ELSE 0 END) as total_stock_out_value
                  FROM stock_transactions st
                  WHERE 1=1";

if (!empty($product_filter)) {
    $summary_query .= " AND st.product_id = '{$product_filter}'";
}

if (!empty($type_filter)) {
    $summary_query .= " AND st.transaction_type = '{$type_filter}'";
}

if (!empty($date_from)) {
    $summary_query .= " AND DATE(st.transaction_date) >= '{$date_from}'";
}

if (!empty($date_to)) {
    $summary_query .= " AND DATE(st.transaction_date) <= '{$date_to}'";
}

$summary = db_fetch_one($connection, $summary_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📊</span>
        Stock Transaction History
    </h2>
    <div class="page-actions">
        <a href="stock_in.php" class="btn btn-success">
            <span class="btn-icon">📥</span>
            Stock In
        </a>
        <a href="stock_out.php" class="btn btn-warning">
            <span class="btn-icon">📤</span>
            Stock Out
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
            <div class="stat-value"><?php echo number_format($summary['total_transactions']); ?></div>
            <div class="stat-label">Total Transactions</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-green">
        <div class="stat-icon">📥</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($summary['total_stock_in_value']); ?></div>
            <div class="stat-label">Stock In Value</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-orange">
        <div class="stat-icon">📤</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($summary['total_stock_out_value']); ?></div>
            <div class="stat-label">Stock Out Value</div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- FILTER FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <!-- Product Filter -->
                <div class="form-group col-md-3">
                    <select name="product" class="form-control">
                        <option value="">All Products</option>
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                            <option value="<?php echo $product['product_id']; ?>" 
                                    <?php echo ($product_filter == $product['product_id']) ? 'selected' : ''; ?>>
                                <?php echo escape_html($product['product_code'] . ' - ' . $product['product_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Type Filter -->
                <div class="form-group col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="stock_in" <?php echo ($type_filter === 'stock_in') ? 'selected' : ''; ?>>Stock In</option>
                        <option value="stock_out" <?php echo ($type_filter === 'stock_out') ? 'selected' : ''; ?>>Stock Out</option>
                        <option value="adjustment" <?php echo ($type_filter === 'adjustment') ? 'selected' : ''; ?>>Adjustment</option>
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="form-group col-md-2">
                    <input 
                        type="date" 
                        name="date_from" 
                        class="form-control" 
                        placeholder="Date From"
                        value="<?php echo escape_html($date_from); ?>"
                    >
                </div>
                
                <!-- Date To -->
                <div class="form-group col-md-2">
                    <input 
                        type="date" 
                        name="date_to" 
                        class="form-control" 
                        placeholder="Date To"
                        value="<?php echo escape_html($date_to); ?>"
                    >
                </div>
                
                <!-- Filter Buttons -->
                <div class="form-group col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- TRANSACTIONS TABLE -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Transaction Records</h3>
        <span class="card-subtitle">
            Showing: <?php echo mysqli_num_rows($transactions_result); ?> transactions
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Amount</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions_result && mysqli_num_rows($transactions_result) > 0): ?>
                        <?php while ($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                            <tr>
                                <td><?php echo $transaction['transaction_id']; ?></td>
                                <td><?php echo format_datetime($transaction['transaction_date'], 'd-m-Y H:i'); ?></td>
                                <td>
                                    <?php if ($transaction['transaction_type'] === 'stock_in'): ?>
                                        <span class="badge badge-success">↑ Stock In</span>
                                    <?php elseif ($transaction['transaction_type'] === 'stock_out'): ?>
                                        <span class="badge badge-warning">↓ Stock Out</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">⚙ Adjustment</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo escape_html($transaction['product_code']); ?></strong><br>
                                    <small><?php echo escape_html($transaction['product_name']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo $transaction['quantity']; ?></strong> 
                                    <?php echo escape_html($transaction['unit_of_measure']); ?>
                                </td>
                                <td><?php echo format_currency($transaction['unit_price']); ?></td>
                                <td><strong><?php echo format_currency($transaction['total_amount']); ?></strong></td>
                                <td><?php echo escape_html($transaction['reference_number']); ?></td>
                                <td><?php echo escape_html($transaction['notes']); ?></td>
                                <td><?php echo escape_html($transaction['username'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No transactions found.</td>
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
