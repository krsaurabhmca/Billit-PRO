<?php
/**
 * ============================================================================
 * STOCK REPORT
 * ============================================================================
 * Purpose: Current inventory valuation
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $filename = "stock_valuation_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Product Code', 'Product Name', 'Category', 'Quantity', 'Reorder Level', 'Unit Price', 'Total Value']);
    
    $query = "SELECT p.*, c.category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.category_id 
              WHERE p.status != 'discontinued'
              ORDER BY p.product_name";
              
    $result = db_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['product_code'],
            $row['product_name'],
            $row['category_name'],
            $row['quantity_in_stock'],
            $row['reorder_level'],
            $row['unit_price'],
            $row['quantity_in_stock'] * $row['unit_price']
        ]);
    }
    fclose($output);
    exit;
}

$page_title = "Stock Report";
require_once '../includes/header.php';

$query = "SELECT p.*, c.category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          WHERE p.status != 'discontinued'
          ORDER BY p.product_name";
$products = db_fetch_all($connection, $query);

$total_stock_value = 0;
$total_items = 0;
foreach ($products as $p) {
    if ($p['quantity_in_stock'] > 0) {
        $total_stock_value += ($p['quantity_in_stock'] * $p['unit_price']);
        $total_items += $p['quantity_in_stock'];
    }
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📦</span>
            Current Stock Report
        </h2>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">
            <span class="btn-icon">🖨️</span> Print
        </button>
        <a href="?export=csv" class="btn btn-primary">
            <span class="btn-icon">⬇️</span> Export CSV
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card-purple">
        <div class="stat-icon">💲</div>
        <div class="stat-content">
            <div class="stat-value">₹<?php echo number_format($total_stock_value, 2); ?></div>
            <div class="stat-label">Total Inventory Value (Selling)</div>
        </div>
    </div>
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">🔢</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($total_items); ?></div>
            <div class="stat-label">Total Units in Stock</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr class="<?php echo ($p['quantity_in_stock'] <= $p['reorder_level']) ? 'bg-red-50' : ''; ?>">
                            <td><code class="text-gray-600"><?php echo escape_html($p['product_code']); ?></code></td>
                            <td><?php echo escape_html($p['product_name']); ?></td>
                            <td><?php echo escape_html($p['category_name']); ?></td>
                            <td class="text-right">
                                <?php if ($p['quantity_in_stock'] <= $p['reorder_level']): ?>
                                    <span class="text-danger font-bold"><?php echo $p['quantity_in_stock']; ?></span>
                                <?php else: ?>
                                    <?php echo $p['quantity_in_stock']; ?>
                                <?php endif; ?>
                                <small class="text-muted"><?php echo $p['unit_of_measure']; ?></small>
                            </td>
                            <td class="text-right">₹<?php echo number_format($p['unit_price'], 2); ?></td>
                            <td class="text-right"><strong>₹<?php echo number_format($p['quantity_in_stock'] * $p['unit_price'], 2); ?></strong></td>
                            <td>
                                <?php if ($p['quantity_in_stock'] == 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php elseif ($p['quantity_in_stock'] <= $p['reorder_level']): ?>
                                    <span class="badge badge-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.bg-red-50 { background-color: #fef2f2; }
</style>

<?php require_once '../includes/footer.php'; ?>
