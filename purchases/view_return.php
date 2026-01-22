<?php
/**
 * ============================================================================
 * VIEW PURCHASE RETURN DETAILS (DEBIT NOTE)
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    redirect('purchase_returns_list.php');
}

$return_id = sanitize_sql($connection, $_GET['id']);
$return = db_fetch_one($connection, "SELECT pr.*, s.supplier_name, s.email, s.phone, s.address, p.supplier_invoice_no 
                                     FROM purchase_returns pr 
                                     JOIN suppliers s ON pr.supplier_id = s.supplier_id 
                                     LEFT JOIN purchases p ON pr.purchase_id = p.purchase_id 
                                     WHERE pr.return_id = '$return_id'");

if (!$return) {
    die("Return not found.");
}

$items = db_fetch_all($connection, "SELECT pri.*, p.product_name, pb.batch_no 
                                    FROM purchase_return_items pri 
                                    JOIN products p ON pri.product_id = p.product_id 
                                    LEFT JOIN product_batches pb ON pri.batch_id = pb.batch_id 
                                    WHERE pri.return_id = '$return_id'");

$page_title = "Return " . $return['return_number'];
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📤</span> Debit Note: <?php echo $return['return_number']; ?>
    </h2>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="window.print()">🖨️ Print Debit Note</button>
        <a href="purchase_returns_list.php" class="btn btn-primary">Back to History</a>
    </div>
</div>

<div class="card" id="print-area">
    <div class="card-body">
        <!-- Header Info -->
        <div class="row mb-4" style="margin-bottom:30px; border-bottom:1px solid #eee; padding-bottom:20px;">
            <div class="col-md-6">
                <h3 style="color:#dc2626;">DEBIT NOTE</h3>
                <p><strong>Return #:</strong> <?php echo $return['return_number']; ?></p>
                <p><strong>Date:</strong> <?php echo format_date($return['return_date']); ?></p>
                <p><strong>Ref Purchase:</strong> <?php echo $return['supplier_invoice_no'] ? $return['supplier_invoice_no'] : 'N/A'; ?></p>
            </div>
            <div class="col-md-6 text-right">
                <h4>Supplier Details</h4>
                <p><strong><?php echo $return['supplier_name']; ?></strong></p>
                <p><?php echo $return['email']; ?></p>
                <p><?php echo $return['phone']; ?></p>
                <p><?php echo $return['address']; ?></p>
            </div>
        </div>

        <!-- Items -->
        <table class="table table-bordered">
            <thead style="background:#f8fafc;">
                <tr>
                    <th>Product</th>
                    <th>Batch</th>
                    <th class="text-right">Cost</th>
                    <th class="text-right">Returned Qty</th>
                    <th class="text-right">Total Debit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td><?php echo $item['batch_no'] ? $item['batch_no'] : '-'; ?></td>
                    <td class="text-right"><?php echo format_currency($item['unit_price']); ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right"><?php echo format_currency($item['total_amount']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right" style="font-weight:bold;">Total Debit Amount:</td>
                    <td class="text-right" style="font-weight:bold; color:#dc2626; font-size:16px;">
                        <?php echo format_currency($return['total_amount']); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top:20px; color:#666; font-style:italic;">
            <strong>Reason for Return:</strong> <?php echo $return['reason']; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
