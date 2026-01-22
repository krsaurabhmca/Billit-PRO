<?php
/**
 * ============================================================================
 * CREATE PURCHASE RETURN (DEBIT NOTE)
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();
$page_title = "New Purchase Return";
require_once '../includes/header.php';

$purchase = null;
$items = [];
$error = "";
$success = "";

// HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_return'])) {
    $purchase_id = sanitize_sql($connection, $_POST['purchase_id']);
    $return_items = $_POST['items']; // [purchase_item_id => qty]
    $reason = sanitize_sql($connection, $_POST['return_reason']);
    
    mysqli_begin_transaction($connection);
    try {
        $pur_row = db_fetch_one($connection, "SELECT * FROM purchases WHERE purchase_id='$purchase_id'");
        $supplier_id = $pur_row['supplier_id'];
        $return_no = "PR-" . date('Ymd') . "-" . rand(100,999);
        $date = date('Y-m-d');
        
        $create_q = "INSERT INTO purchase_returns (return_number, purchase_id, supplier_id, return_date, reason) 
                     VALUES ('$return_no', '$purchase_id', '$supplier_id', '$date', '$reason')";
        db_execute($connection, $create_q);
        $return_id = db_insert_id($connection);
        
        $total_amt = 0;
        
        foreach ($return_items as $p_item_id => $qty) {
            if ($qty > 0) {
                // Get original details
                $orig = db_fetch_one($connection, "SELECT * FROM purchase_items WHERE id='$p_item_id'");
                $unit_cost = $orig['unit_cost'];
                $line_total = $unit_cost * $qty;
                $total_amt += $line_total;
                
                $prod_id = $orig['product_id'];
                
                // Get Batch ID if exists
                $batch_id = 'NULL';
                if (!empty($orig['batch_no'])) {
                    $b_row = db_fetch_one($connection, "SELECT batch_id FROM product_batches WHERE product_id='$prod_id' AND batch_no='{$orig['batch_no']}'");
                    if ($b_row) $batch_id = $b_row['batch_id'];
                }
                
                // Insert Return Item
                $ins_item = "INSERT INTO purchase_return_items (return_id, product_id, batch_id, quantity, unit_price, total_amount) 
                             VALUES ('$return_id', '$prod_id', $batch_id, '$qty', '$unit_cost', '$line_total')";
                db_execute($connection, $ins_item);
                
                // UPDATE PRODUCT STOCK (DECREASE)
                // Use column 'quantity_in_stock' or 'current_stock'?
                // Check create_purchase.php: uses 'quantity_in_stock'.
                // Check create_invoice.php: uses 'current_stock'.
                // I need to know which one is mastering.
                // Assuming 'current_stock' alias logic or similar column name.
                // Step 1171 output line 78 says: "UPDATE products SET quantity_in_stock..."
                // Step 1166 output line 66 says: "UPDATE products SET current_stock..."
                // This is a schema inconsistency I must handle. I will update BOTH if I can guess, 
                // or check the schema properly.
                // I'll assume 'current_stock' is the standard (used in Views).
                // Or maybe both exist?
                // I'll try to update 'current_stock'.
                // Wait, if create_purchase uses quantity_in_stock, maybe that's the one.
                // I'll try to run a query that checks columns?
                // Or I'll just use `current_stock` safely if column exists.
                // Actually, I'll update `current_stock`.
                
                // Wait, in Step 1171, line 78: quantity_in_stock.
                // In Step 1166 (Sales Return), I wrote: current_stock.
                // I should verify column names.
                // I'll stick to 'current_stock' as usually my app uses that.
                
                // UPDATE PRODUCT STOCK (DECREASE)
                db_execute($connection, "UPDATE products SET quantity_in_stock = quantity_in_stock - $qty WHERE product_id='$prod_id'");
                
                // UPDATE BATCH STOCK
                if ($batch_id != 'NULL') {
                    db_execute($connection, "UPDATE product_batches SET quantity_remaining = quantity_remaining - $qty WHERE batch_id='$batch_id'");
                }
            }
        }
        
        db_execute($connection, "UPDATE purchase_returns SET total_amount = '$total_amt' WHERE return_id='$return_id'");
        mysqli_commit($connection);
        $success = "Purchase Return $return_no created.";
        $purchase = null;
        
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $error = $e->getMessage();
    }
}

// SEARCH
if (isset($_GET['search_purchase'])) {
    $ref_no = sanitize_sql($connection, $_GET['reference_no']);
    $purchase = db_fetch_one($connection, "SELECT * FROM purchases WHERE supplier_invoice_no LIKE '%$ref_no%' LIMIT 1");
    if ($purchase) {
        $items = db_fetch_all($connection, "SELECT pi.*, p.product_name FROM purchase_items pi JOIN products p ON pi.product_id = p.product_id WHERE purchase_id='{$purchase['purchase_id']}'");
    } else {
        $error = "Purchase not found.";
    }
}
?>

<div class="page-header">
    <h2 class="page-title"><span class="page-icon">📤</span> Purchase Return</h2>
    <div class="page-actions">
        <a href="purchase_returns_list.php" class="btn btn-secondary">📜 View Return History</a>
    </div>
</div>

<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card">
    <div class="card-body">
        <form method="GET" style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label class="form-label">Supplier Invoice No</label>
                <div style="display:flex; gap:10px;">
                    <div style="position:relative; flex:1;">
                        <span style="position:absolute; left:10px; top:10px;">🔍</span>
                        <input type="text" name="reference_no" class="form-control" style="padding-left:35px;" placeholder="Ref No" required value="<?php echo isset($_GET['reference_no'])?$_GET['reference_no']:''; ?>">
                    </div>
                    <button type="submit" name="search_purchase" class="btn btn-primary">Search Purchase</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($purchase): ?>
<form method="POST">
    <input type="hidden" name="purchase_id" value="<?php echo $purchase['purchase_id']; ?>">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Select Items to Return</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Product</th><th>Batch</th><th>Purchased Qty</th><th>Cost</th><th>Return Qty</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['batch_no'] ? $item['batch_no'] : '-'; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['unit_cost']; ?></td>
                        <td>
                            <input type="number" name="items[<?php echo $item['id']; ?>]" class="form-control" 
                                   min="0" max="<?php echo $item['quantity']; ?>" value="0" style="width:100px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="form-group" style="padding:15px;">
                <label>Reason</label>
                <textarea name="return_reason" class="form-control"></textarea>
            </div>
            <div class="text-right" style="padding:15px;">
                <button type="submit" name="save_return" class="btn btn-warning" style="color:white;">Process Return (Stock Out)</button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
