<?php
/**
 * ============================================================================
 * VIEW SALES RETURN DETAILS
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    redirect('sales_returns_list.php');
}

$return_id = sanitize_sql($connection, $_GET['id']);
$return = db_fetch_one($connection, "SELECT sr.*, i.invoice_number, c.customer_name, c.email, c.phone, c.billing_address as address 
                                     FROM sale_returns sr 
                                     JOIN invoices i ON sr.invoice_id = i.invoice_id 
                                     JOIN customers c ON sr.customer_id = c.customer_id 
                                     WHERE sr.return_id = '$return_id'");

if (!$return) {
    die("Return not found.");
}

$items = db_fetch_all($connection, "SELECT sri.*, p.product_name 
                                    FROM sale_return_items sri 
                                    JOIN products p ON sri.product_id = p.product_id 
                                    WHERE sri.return_id = '$return_id'");

$page_title = "Return " . $return['return_number'];
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">↩️</span> Return Details: <?php echo $return['return_number']; ?>
    </h2>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="window.print()">🖨️ Print Credit Note</button>
        <a href="sales_returns_list.php" class="btn btn-primary">Back to History</a>
    </div>
</div>

<div class="card" id="print-area">
    <div class="card-body">
        <!-- Header Info -->
        <div class="row mb-4" style="margin-bottom:30px; border-bottom:1px solid #eee; padding-bottom:20px;">
            <div class="col-md-6">
                <h3 style="color:#dc2626;">CREDIT NOTE</h3>
                <p><strong>Return #:</strong> <?php echo $return['return_number']; ?></p>
                <p><strong>Date:</strong> <?php echo format_date($return['return_date']); ?></p>
                <p><strong>Ref Invoice:</strong> <a href="view_invoice.php?id=<?php echo $return['invoice_id']; ?>"><?php echo $return['invoice_number']; ?></a></p>
            </div>
            <div class="col-md-6 text-right">
                <h4>Customer Details</h4>
                <p><strong><?php echo $return['customer_name']; ?></strong></p>
                <p><?php echo $return['email']; ?></p>
                <p><?php echo $return['phone']; ?></p>
            </div>
        </div>

        <!-- Items -->
        <table class="table table-bordered">
            <thead style="background:#f8fafc;">
                <tr>
                    <th>Product</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Returned Qty</th>
                    <th class="text-right">Total Refund</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td class="text-right"><?php echo format_currency($item['unit_price']); ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right"><?php echo format_currency($item['total_amount']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right" style="font-weight:bold;">Total Credit Amount:</td>
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
