<?php
/**
 * ============================================================================
 * CREATE SALES RETURN (CREDIT NOTE)
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();
$page_title = "New Sales Return";
require_once '../includes/header.php';

$invoice = null;
$items = [];
$error = "";
$success = "";

// HANDLE FORM SUBMISSION (CREATE RETURN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_return'])) {
    $invoice_id = sanitize_sql($connection, $_POST['invoice_id']);
    $return_items = $_POST['items']; // Array of [item_id => return_qty]
    $reason = sanitize_sql($connection, $_POST['return_reason']);
    
    // Start Transaction
    mysqli_begin_transaction($connection);
    try {
        // 1. Create Return Record
        $inv_res = db_fetch_one($connection, "SELECT * FROM invoices WHERE invoice_id='$invoice_id'");
        $cust_id = $inv_res['customer_id'];
        $return_no = "SR-" . date('Ymd') . "-" . rand(100,999);
        $date = date('Y-m-d');
        
        $create_q = "INSERT INTO sale_returns (return_number, invoice_id, customer_id, return_date, reason) 
                     VALUES ('$return_no', '$invoice_id', '$cust_id', '$date', '$reason')";
        db_execute($connection, $create_q);
        $return_id = db_insert_id($connection);
        
        $total_return_amt = 0;
        
        // 2. Process Items
        foreach ($return_items as $item_id => $qty) {
            if ($qty > 0) {
                // Fetch original item details (price, tax)
                $orig_item = db_fetch_one($connection, "SELECT * FROM invoice_items WHERE item_id='$item_id'");
                
                // Calculate Refund Amount
                $unit_price = $orig_item['unit_price'];
                $line_total = $unit_price * $qty; // Simplifying tax calc for now (pro-rata?)
                // Ideally recalculate tax... assuming inclusive/exclusive based on original.
                // For simplicity, using unit_price * qty.
                
                $total_return_amt += $line_total;
                
                // Insert Return Item
                $prod_id = $orig_item['product_id'];
                $batch_id = $orig_item['batch_id'] ?: 'NULL';
                
                $item_q = "INSERT INTO sale_return_items (return_id, product_id, batch_id, quantity, unit_price, total_amount)
                           VALUES ('$return_id', '$prod_id', $batch_id, '$qty', '$unit_price', '$line_total')";
                db_execute($connection, $item_q);
                
                // 3. Update Stock (INCREASE)
                $upd_prod = "UPDATE products SET quantity_in_stock = quantity_in_stock + $qty WHERE product_id='$prod_id'";
                db_execute($connection, $upd_prod);
                
                if ($batch_id != 'NULL') {
                    $upd_batch = "UPDATE product_batches SET quantity = quantity + $qty WHERE batch_id='$batch_id'";
                    db_execute($connection, $upd_batch);
                }
            }
        }
        
        // Update Total
        db_execute($connection, "UPDATE sale_returns SET total_amount = '$total_return_amt' WHERE return_id='$return_id'");
        
        mysqli_commit($connection);
        $success = "Sales Return $return_no created successfully!";
        $invoice = null; // Reset
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $error = "Error: " . $e->getMessage();
    }
}

// HANDLE INVOICE SEARCH
if (isset($_GET['search_invoice'])) {
    $inv_num = sanitize_sql($connection, $_GET['invoice_number']);
    $invoice = db_fetch_one($connection, "SELECT * FROM invoices WHERE invoice_number LIKE '%$inv_num%' LIMIT 1");
    if ($invoice) {
        $items = db_fetch_all($connection, "SELECT ii.*, p.product_name FROM invoice_items ii JOIN products p ON ii.product_id = p.product_id WHERE invoice_id='{$invoice['invoice_id']}'");
    } else {
        $error = "Invoice not found.";
    }
}
?>

<div class="page-header">
    <h2 class="page-title"><span class="page-icon">↩️</span> Sales Return</h2>
    <div class="page-actions">
        <a href="sales_returns_list.php" class="btn btn-secondary">📜 View Return History</a>
    </div>
</div>

<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<!-- 1. Search Invoice -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label class="form-label">Find Invoice Number</label>
                <div style="display:flex; gap:10px;">
                    <div style="position:relative; flex:1;">
                         <span style="position:absolute; left:10px; top:10px;">🔍</span>
                         <input type="text" name="invoice_number" class="form-control" style="padding-left:35px;" placeholder="e.g. INV-0001" required value="<?php echo isset($_GET['invoice_number'])?$_GET['invoice_number']:''; ?>">
                    </div>
                    <button type="submit" name="search_invoice" class="btn btn-primary">Search Invoice</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($invoice): ?>
<!-- 2. Select Items -->
<form method="POST">
    <input type="hidden" name="invoice_id" value="<?php echo $invoice['invoice_id']; ?>">
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Select Items to Return (Inv: <?php echo $invoice['invoice_number']; ?>)</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold Qty</th>
                        <th>Price</th>
                        <th>Return Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['unit_price']; ?></td>
                        <td>
                            <input type="number" name="items[<?php echo $item['item_id']; ?>]" 
                                   class="form-control" min="0" max="<?php echo $item['quantity']; ?>" value="0" style="width:100px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="form-group" style="padding:15px;">
                <label>Reason for Return</label>
                <textarea name="return_reason" class="form-control" rows="2"></textarea>
            </div>
            
            <div style="padding:15px; text-align:right;">
                <button type="submit" name="save_return" class="btn btn-danger">Confirm Return & Update Stock</button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
