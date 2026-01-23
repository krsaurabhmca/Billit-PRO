<?php
/**
 * ============================================================================
 * VIEW PURCHASE PAGE
 * ============================================================================
 * Purpose: View purchase details and items
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// 1. GET PURCHASE ID
if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid purchase ID.");
    redirect('index.php');
}

$purchase_id = sanitize_sql($connection, $_GET['id']);

// 2. FETCH PURCHASE HEADER
$query = "SELECT p.*, s.supplier_name, s.email, s.phone, s.address, u.full_name 
          FROM purchases p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          LEFT JOIN users u ON p.created_by = u.user_id 
          WHERE p.purchase_id = '$purchase_id' LIMIT 1";
$purchase = db_fetch_one($connection, $query);

if (!$purchase) {
    set_error_message("Purchase header not found.");
    redirect('index.php');
}

// 3. FETCH ITEMS
$items_query = "SELECT pi.*, p.product_name, p.product_code 
                FROM purchase_items pi 
                LEFT JOIN products p ON pi.product_id = p.product_id 
                WHERE pi.purchase_id = '$purchase_id'";
$items = db_fetch_all($connection, $items_query);

$page_title = "Purchase Details #" . $purchase_id;
require_once '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title"><span class="page-icon">📄</span> Purchase Details</h2>
        <p class="page-description">Reference: #<?php echo str_pad($purchase['purchase_id'], 5, '0', STR_PAD_LEFT); ?></p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- LEFT COLUMN: INFO -->
    <div class="col-md-4">
        <div class="card mb-20">
            <div class="card-header">
                <h3 class="card-title">Supplier Info</h3>
            </div>
            <div class="card-body">
                <h4 style="margin-top:0;"><?php echo escape_html($purchase['supplier_name']); ?></h4>
                <p class="text-muted" style="font-size:13px; margin-bottom:5px;">
                    Invoice No: <strong><?php echo escape_html($purchase['supplier_invoice_no']); ?></strong>
                </p>
                <p class="text-muted" style="font-size:13px;">
                    <?php if($purchase['phone']) echo '📞 ' . escape_html($purchase['phone']) . '<br>'; ?>
                    <?php if($purchase['email']) echo '📧 ' . escape_html($purchase['email']) . '<br>'; ?>
                    <?php if($purchase['address']) echo '📍 ' . nl2br(escape_html($purchase['address'])); ?>
                </p>
            </div>
        </div>
        
        <div class="card">
             <div class="card-header">
                <h3 class="card-title">Purchase Info</h3>
            </div>
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span class="text-muted">Date:</span>
                    <strong><?php echo date('d M Y', strtotime($purchase['purchase_date'])); ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span class="text-muted">Status:</span>
                    <span class="badge badge-<?php echo $purchase['status']=='received'?'success':'warning'; ?>">
                        <?php echo ucfirst($purchase['status']); ?>
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span class="text-muted">Created By:</span>
                    <span><?php echo escape_html($purchase['full_name']); ?></span>
                </div>
                 <div style="display:flex; justify-content:space-between; margin-top:20px; border-top:1px solid #eee; padding-top:10px;">
                    <span class="text-muted">Total Amount:</span>
                    <strong style="font-size:18px; color:#2563eb;">₹<?php echo number_format($purchase['total_amount'], 2); ?></strong>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT COLUMN: ITEMS -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Items Received</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 12px; text-align: left;">Product</th>
                                <th style="padding: 12px; text-align: left;">Tracking</th>
                                <th style="padding: 12px; text-align: right;">Qty</th>
                                <th style="padding: 12px; text-align: right;">Cost</th>
                                <th style="padding: 12px; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): 
                                $tracking = "";
                                if($item['batch_no']) $tracking .= "<div><small>Batch: <strong>{$item['batch_no']}</strong></small></div>";
                                if($item['expiry_date']) $tracking .= "<div><small>Exp: ".date('d/m/y', strtotime($item['expiry_date']))."</small></div>";
                                if($item['serial_numbers']) $tracking .= "<div><small>SN: <span style='color:#666;'>{$item['serial_numbers']}</span></small></div>";
                            ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 12px;">
                                    <strong><?php echo escape_html($item['product_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo escape_html($item['product_code']); ?></small>
                                </td>
                                <td style="padding: 12px;"><?php echo $tracking ? $tracking : '<span class="text-muted">-</span>'; ?></td>
                                <td style="padding: 12px; text-align: right;"><?php echo $item['quantity']; ?></td>
                                <td style="padding: 12px; text-align: right;">₹<?php echo number_format($item['unit_cost'], 2); ?></td>
                                <td style="padding: 12px; text-align: right;"><strong>₹<?php echo number_format($item['total_cost'], 2); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #666;">No items found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($purchase['notes']): ?>
                <div style="margin-top:20px; background:#f8fafc; padding:15px; border-radius:6px;">
                    <strong>Notes:</strong><br>
                    <span class="text-muted"><?php echo nl2br(escape_html($purchase['notes'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
