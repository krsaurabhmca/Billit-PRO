<?php
/**
 * ============================================================================
 * SUPPLIER WISE STOCK REPORT
 * ============================================================================
 * Purpose: Inventory categorized by supplier
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $filename = "supplier_stock_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Supplier', 'Product Code', 'Product Name', 'Quantity', 'Unit Value', 'Total Value']);
    
    $supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';
    
    $query = "SELECT p.*, s.supplier_name 
              FROM products p 
              LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
              WHERE p.status != 'discontinued'";
              
    if (!empty($supplier_id)) {
        $query .= " AND p.supplier_id = '$supplier_id'";
    } else {
        $query .= " ORDER BY s.supplier_name, p.product_name";
    }
              
    $result = db_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['supplier_name'] ?? 'No Supplier',
            $row['product_code'],
            $row['product_name'],
            $row['quantity_in_stock'],
            $row['unit_price'],
            $row['quantity_in_stock'] * $row['unit_price']
        ]);
    }
    fclose($output);
    exit;
}

$page_title = "Supplier Stock Report";
require_once '../includes/header.php';

// Fetch Suppliers
$suppliers = db_fetch_all($connection, "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name");
$supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';

// Fetch Data
$query = "SELECT p.*, s.supplier_name 
          FROM products p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          WHERE p.status != 'discontinued'";

if (!empty($supplier_id)) {
    $query .= " AND p.supplier_id = '$supplier_id'";
}
$query .= " ORDER BY s.supplier_name, p.product_name";

$products = db_fetch_all($connection, $query);

// Group by Supplier for display
$grouped = [];
foreach ($products as $p) {
    $sup = $p['supplier_name'] ?: 'Unassigned';
    $grouped[$sup][] = $p;
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">🏢</span>
            Supplier Wise Stock
        </h2>
    </div>
    <div class="page-actions">
        <a href="?export=csv&supplier_id=<?php echo $supplier_id; ?>" class="btn btn-primary">
            <span class="btn-icon">⬇️</span> Export CSV
        </a>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="form-group col-md-6">
                <label>Filter by Supplier</label>
                <select name="supplier_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo $s['supplier_id']; ?>" <?php echo $supplier_id == $s['supplier_id'] ? 'selected' : ''; ?>>
                            <?php echo escape_html($s['supplier_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                 <?php if(!empty($supplier_id)): ?>
                    <a href="supplier_stock.php" class="btn btn-secondary">Clear Filter</a>
                 <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php foreach ($grouped as $supplier => $items): 
    $total_val = 0;
    foreach ($items as $i) $total_val += ($i['quantity_in_stock'] * $i['unit_price']);
?>
<div class="card mb-20 pt-0">
    <div class="card-header" style="background: var(--gray-50);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h3 class="card-title"><?php echo escape_html($supplier); ?></h3>
            <span class="badge badge-gray">Total Value: ₹<?php echo number_format($total_val, 2); ?></span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="data-table mb-0">
                <thead>
                    <tr>
                        <th width="15%">Code</th>
                        <th width="40%">Product</th>
                        <th width="15%" class="text-right">Qty</th>
                        <th width="15%" class="text-right">Unit Price</th>
                        <th width="15%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $p): ?>
                    <tr>
                        <td><?php echo escape_html($p['product_code']); ?></td>
                        <td><?php echo escape_html($p['product_name']); ?></td>
                        <td class="text-right"><?php echo $p['quantity_in_stock']; ?></td>
                        <td class="text-right">₹<?php echo number_format($p['unit_price'], 2); ?></td>
                        <td class="text-right"><strong>₹<?php echo number_format($p['quantity_in_stock'] * $p['unit_price'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($grouped)): ?>
    <div class="alert alert-info">No products found.</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
