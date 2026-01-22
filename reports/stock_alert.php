<?php
/**
 * ============================================================================
 * STOCK ALERT REPORT
 * ============================================================================
 * Purpose: View low stock items
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $filename = "low_stock_alert_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Product Code', 'Product Name', 'Category', 'Current Stock', 'Reorder Level', 'Shortage', 'Status']);
    
    $query = "SELECT * FROM view_low_stock_products";
    $result = db_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['product_code'],
            $row['product_name'],
            $row['category_name'],
            $row['quantity_in_stock'],
            $row['reorder_level'],
            $row['shortage_quantity'],
            'Low Stock'
        ]);
    }
    fclose($output);
    exit;
}

$page_title = "Stock Alerts";
require_once '../includes/header.php';

$query = "SELECT * FROM view_low_stock_products";
$low_stock = db_fetch_all($connection, $query);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">⚠️</span>
            Low Stock Alerts
        </h2>
        <p class="page-description">Products that have fallen below their reorder level.</p>
    </div>
    <div class="page-actions">
        <a href="?export=csv" class="btn btn-primary">
            <span class="btn-icon">⬇️</span> Export CSV
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-right">Current Stock</th>
                        <th class="text-right">Reorder Level</th>
                        <th class="text-right">Shortage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($low_stock) > 0): ?>
                        <?php foreach ($low_stock as $item): ?>
                            <tr>
                                <td><code class="text-gray-600"><?php echo escape_html($item['product_code']); ?></code></td>
                                <td><?php echo escape_html($item['product_name']); ?></td>
                                <td><?php echo escape_html($item['category_name']); ?></td>
                                <td class="text-right text-danger font-bold"><?php echo $item['quantity_in_stock']; ?></td>
                                <td class="text-right"><?php echo $item['reorder_level']; ?></td>
                                <td class="text-right text-danger"><?php echo $item['shortage_quantity']; ?></td>
                                <td>
                                    <a href="../stock/stock_in.php?product_id=<?php echo $item['product_id']; ?>" class="btn btn-sm btn-primary">
                                        Refill Stock
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-success">All stock levels are healthy! ✅</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
