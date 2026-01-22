<?php
/**
 * ============================================================================
 * PURCHASE ORDER GENERATOR
 * ============================================================================
 * Purpose: Generate printable Purchase Orders for suppliers
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Print View (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_po'])) {
    $supplier_id = $_POST['supplier_id'];
    $po_number = $_POST['po_number'];
    $po_date = $_POST['po_date'];
    $items = $_POST['items']; // Array of [product_id => qty]
    
    // Fetch Supplier Details
    $supplier = db_fetch_one($connection, "SELECT * FROM suppliers WHERE supplier_id = '$supplier_id'");
    
    // Fetch Company Details
    $company = db_fetch_one($connection, "SELECT * FROM company_settings LIMIT 1");
    
    // Build Item List
    $po_items = [];
    $total_value = 0;
    foreach ($items as $pid => $qty) {
        if ($qty > 0) {
            $prod = db_fetch_one($connection, "SELECT * FROM products WHERE product_id = '$pid'");
            $prod['order_qty'] = $qty;
            $prod['line_total'] = $qty * $prod['unit_price']; // Assuming unit_price is cost for now or user will edit
            $total_value += $prod['line_total'];
            $po_items[] = $prod;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Purchase Order #<?php echo escape_html($po_number); ?></title>
        <style>
            body { font-family: 'Helvetica', 'Arial', sans-serif; padding: 40px; color: #333; }
            .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
            .company-info h1 { margin: 0; color: #2563eb; }
            .po-title { text-align: right; }
            .po-title h2 { margin: 0; color: #555; }
            .addresses { display: flex; justify-content: space-between; margin-bottom: 40px; }
            .box { width: 45%; }
            .box h3 { border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; font-size: 14px; text-transform: uppercase; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th { text-align: left; background: #f8f9fa; padding: 10px; border-bottom: 2px solid #ddd; font-size: 12px; text-transform: uppercase; }
            td { padding: 10px; border-bottom: 1px solid #eee; }
            .text-right { text-align: right; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 20px; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; cursor: pointer;">🖨️ Print PO</button>
            <a href="purchase_order.php" style="margin-left: 10px; text-decoration: none; color: #555;">← Back</a>
        </div>
        
        <div class="header">
            <div class="company-info">
                <h1><?php echo isset($company['company_name']) ? escape_html($company['company_name']) : APP_NAME; ?></h1>
                <p><?php echo isset($company['company_address']) ? nl2br(escape_html($company['company_address'])) : ''; ?></p>
            </div>
            <div class="po-title">
                <h2>PURCHASE ORDER</h2>
                <p><strong>PO #:</strong> <?php echo escape_html($po_number); ?></p>
                <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($po_date)); ?></p>
            </div>
        </div>
        
        <div class="addresses">
            <div class="box">
                <h3>Vendor</h3>
                <p><strong><?php echo escape_html($supplier['supplier_name']); ?></strong><br>
                <?php echo escape_html($supplier['contact_person']); ?><br>
                <?php echo escape_html($supplier['address']); ?><br>
                <?php echo escape_html($supplier['city']); ?>, <?php echo escape_html($supplier['country']); ?><br>
                Email: <?php echo escape_html($supplier['email']); ?></p>
            </div>
            <div class="box">
                <h3>Ship To</h3>
                <p><strong><?php echo isset($company['company_name']) ? escape_html($company['company_name']) : APP_NAME; ?></strong><br>
                <?php echo isset($company['company_address']) ? nl2br(escape_html($company['company_address'])) : 'Warehouse Location'; ?></p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($po_items as $item): ?>
                <tr>
                    <td><?php echo escape_html($item['product_code']); ?></td>
                    <td><?php echo escape_html($item['product_name']); ?></td>
                    <td class="text-right"><?php echo $item['order_qty']; ?> <?php echo $item['unit_of_measure']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 40px; display: flex; justify-content: space-between;">
            <div>
                <p><strong>Authorized Signature:</strong> __________________________</p>
                <p><strong>Date:</strong> __________________________</p>
            </div>
        </div>
        
        <div class="footer">
            <p>If you have any questions about this purchase order, please contact us.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$page_title = "Purchase Orders";
require_once '../includes/header.php';

// Fetch Suppliers
$suppliers = db_fetch_all($connection, "SELECT * FROM suppliers WHERE status = 'active' ORDER BY supplier_name");

$supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';
$products = [];
if (!empty($supplier_id)) {
    $products = db_fetch_all($connection, "SELECT * FROM products WHERE supplier_id = '$supplier_id' AND status != 'discontinued' ORDER BY product_name");
}

$po_number = "PO-" . date('Ymd') . "-" . rand(100, 999);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📋</span>
            Generate Purchase Order
        </h2>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <form method="GET">
            <div class="form-group">
                <label>Select Supplier</label>
                <select name="supplier_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Choose Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo $s['supplier_id']; ?>" <?php echo $supplier_id == $s['supplier_id'] ? 'selected' : ''; ?>>
                            <?php echo escape_html($s['supplier_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($supplier_id) && count($products) > 0): ?>
<form method="POST" action="" target="_blank">
    <input type="hidden" name="supplier_id" value="<?php echo $supplier_id; ?>">
    
    <div class="card mb-20">
        <div class="card-header">
            <h3 class="card-title">Order Details</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>PO Number</label>
                    <input type="text" name="po_number" class="form-control" value="<?php echo $po_number; ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>PO Date</label>
                    <input type="date" name="po_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-20">
        <div class="card-header">
            <h3 class="card-title">Select Products</h3>
        </div>
        <div class="card-body p-0">
            <table class="data-table mb-0">
                <thead>
                    <tr>
                        <th width="50">Select</th>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Reorder Level</th>
                        <th width="150">Order Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $is_low = $p['quantity_in_stock'] <= $p['reorder_level'];
                        $suggested = $is_low ? ($p['reorder_level'] * 2) - $p['quantity_in_stock'] : 0;
                    ?>
                    <tr class="<?php echo $is_low ? 'bg-red-50' : ''; ?>">
                        <td>
                            <input type="checkbox" name="select_<?php echo $p['product_id']; ?>" 
                                   onchange="toggleQty(this, 'qty_<?php echo $p['product_id']; ?>')"
                                   <?php echo $is_low ? 'checked' : ''; ?>>
                        </td>
                        <td>
                            <strong><?php echo escape_html($p['product_name']); ?></strong><br>
                            <span class="text-sm text-muted"><?php echo $p['product_code']; ?></span>
                        </td>
                        <td class="<?php echo $is_low ? 'text-danger font-bold' : ''; ?>">
                            <?php echo $p['quantity_in_stock']; ?>
                        </td>
                        <td><?php echo $p['reorder_level']; ?></td>
                        <td>
                            <input type="number" name="items[<?php echo $p['product_id']; ?>]" 
                                   id="qty_<?php echo $p['product_id']; ?>"
                                   class="form-control form-control-sm" 
                                   value="<?php echo $suggested > 0 ? $suggested : 0; ?>"
                                   min="0"
                                   <?php echo $is_low ? '' : 'disabled'; ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="submit" name="generate_po" class="btn btn-primary">
                <span class="btn-icon">🖨️</span> Generate Purchase Order
            </button>
        </div>
    </div>
</form>

<script>
function toggleQty(checkbox, inputId) {
    const input = document.getElementById(inputId);
    input.disabled = !checkbox.checked;
    if (checkbox.checked && input.value == 0) {
        input.value = 10; // Default
    }
}
</script>
<style>
.bg-red-50 { background-color: #fef2f2; }
</style>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
