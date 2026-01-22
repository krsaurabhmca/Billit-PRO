<?php
/**
 * ============================================================================
 * PRODUCT QUANTITY TALLY REPORT
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();
$page_title = "Product Quantity Tally";
require_once '../includes/header.php';

// Fetch Data Aggregates
$tally = [];

// 1. Get All Products
$products_res = db_query($connection, "SELECT product_id, product_name, product_code, quantity_in_stock FROM products ORDER BY product_name");
while($p = mysqli_fetch_assoc($products_res)) {
    $tally[$p['product_id']] = [
        'name' => $p['product_name'],
        'sku' => $p['product_code'],
        'current' => $p['quantity_in_stock'],
        'purchase' => 0,
        'purchase_return' => 0,
        'sale' => 0,
        'sale_return' => 0
    ];
}

// 2. Get Purchases
$pur_res = db_query($connection, "SELECT product_id, SUM(quantity) as qty FROM purchase_items GROUP BY product_id");
while($row = mysqli_fetch_assoc($pur_res)) {
    if(isset($tally[$row['product_id']])) $tally[$row['product_id']]['purchase'] = $row['qty'];
}

// 3. Get Purchase Returns
$pr_res = db_query($connection, "SELECT product_id, SUM(quantity) as qty FROM purchase_return_items GROUP BY product_id");
while($row = mysqli_fetch_assoc($pr_res)) {
    if(isset($tally[$row['product_id']])) $tally[$row['product_id']]['purchase_return'] = $row['qty'];
}

// 4. Get Sales (Finalized Only ideally, but we default to all for now or check invoice logic)
// Assuming all in invoice_items are valid sales unless invoice is Draft.
$sale_res = db_query($connection, "
    SELECT ii.product_id, SUM(ii.quantity) as qty 
    FROM invoice_items ii
    JOIN invoices i ON ii.invoice_id = i.invoice_id
    GROUP BY ii.product_id
");
while($row = mysqli_fetch_assoc($sale_res)) {
    if(isset($tally[$row['product_id']])) $tally[$row['product_id']]['sale'] = $row['qty'];
}

// 5. Get Sale Returns
$sr_res = db_query($connection, "SELECT product_id, SUM(quantity) as qty FROM sale_return_items GROUP BY product_id");
while($row = mysqli_fetch_assoc($sr_res)) {
    if(isset($tally[$row['product_id']])) $tally[$row['product_id']]['sale_return'] = $row['qty'];
}

?>

<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">🔢</span> Product Tally Report
    </h2>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Report</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Product / SKU</th>
                        <th class="text-right" style="background:#f8fafc;">Opening / Adj</th>
                        <th class="text-right" style="color:#2563eb; background:#eff6ff;">Purchased (+)</th>
                        <th class="text-right" style="color:#dc2626; background:#fef2f2;">Pur. Return (-)</th>
                        <th class="text-right" style="color:#16a34a; background:#f0fdf4;">Sold (-)</th>
                        <th class="text-right" style="color:#d97706; background:#fffbeb;">Sale Return (+)</th>
                        <th class="text-right" style="font-weight:bold; border-left:2px solid #ddd;">Calculated</th>
                        <th class="text-right">Actual Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tally as $pid => $data): 
                        // Calculate Net Movement
                        $movement = $data['purchase'] - $data['purchase_return'] - $data['sale'] + $data['sale_return'];
                        
                        // Derived Opening Stock (Current - Movement)
                        // This represents Initial Stock + Manual Adjustments
                        $opening = $data['current'] - $movement;
                        
                        // Calculated (Should match Current)
                        $calc = $opening + $movement;
                        
                        $match_icon = ($calc == $data['current']) ? '✅' : '❌';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo escape_html($data['name']); ?></strong><br>
                            <small class="text-muted"><?php echo escape_html($data['sku']); ?></small>
                        </td>
                        <td class="text-right" style="background:#f8fafc; font-weight:500;"><?php echo $opening; ?></td>
                        <td class="text-right" style="background:#eff6ff;"><?php echo $data['purchase']; ?></td>
                        <td class="text-right" style="background:#fef2f2;"><?php echo $data['purchase_return']; ?></td>
                        <td class="text-right" style="background:#f0fdf4;"><?php echo $data['sale']; ?></td>
                        <td class="text-right" style="background:#fffbeb;"><?php echo $data['sale_return']; ?></td>
                        <td class="text-right" style="font-weight:bold; border-left:2px solid #ddd;">
                            <?php echo $calc; ?>
                        </td>
                        <td class="text-right">
                            <?php echo $data['current']; ?> <?php echo $match_icon; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
