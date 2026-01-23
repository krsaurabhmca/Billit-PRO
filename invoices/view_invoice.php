<?php
/**
 * ============================================================================
 * VIEW INVOICE PAGE
 * ============================================================================
 * Purpose: Display invoice details with GST breakdown
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "View Invoice";

// Include header
require_once '../includes/header.php';

// ============================================================================
// GET INVOICE ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid invoice ID.");
    redirect('invoices.php');
}

$invoice_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// FETCH INVOICE DATA
// ============================================================================

$invoice_query = "SELECT i.*, c.customer_name, c.customer_type, c.gstin as customer_gstin,
                  c.billing_address, c.billing_city, c.billing_state, c.billing_pincode
                  FROM invoices i
                  INNER JOIN customers c ON i.customer_id = c.customer_id
                  WHERE i.invoice_id = '{$invoice_id}' LIMIT 1";
$invoice = db_fetch_one($connection, $invoice_query);

if (!$invoice) {
    set_error_message("Invoice not found.");
    redirect('invoices.php');
}

// ============================================================================
// FETCH INVOICE ITEMS
// ============================================================================

$items_query = "SELECT ii.*, pb.batch_no, pb.expiry_date 
                FROM invoice_items ii 
                LEFT JOIN product_batches pb ON ii.batch_id = pb.batch_id 
                WHERE ii.invoice_id = '{$invoice_id}' ORDER BY ii.item_id";
$items_result = db_query($connection, $items_query);

// ============================================================================
// FETCH COMPANY SETTINGS
// ============================================================================

$company_query = "SELECT * FROM company_settings LIMIT 1";
$company = db_fetch_one($connection, $company_query);

// Theme Configuration
$theme_color = !empty($company['invoice_color']) ? $company['invoice_color'] : '#2563eb';

// CHECK RETURNS
$has_returns = false;
$returned_total = 0;
$return_status_badge = "";
$returns_res = db_query($connection, "SELECT * FROM sale_returns WHERE invoice_id = '$invoice_id'");
if (mysqli_num_rows($returns_res) > 0) {
    $has_returns = true;
    while($r = mysqli_fetch_assoc($returns_res)) {
        $returned_total += $r['total_amount'];
    }
    // Reset Data Seek
    mysqli_data_seek($returns_res, 0);
    
    if ($returned_total >= $invoice['total_amount']) {
        $return_status_badge = '<span class="badge badge-danger" style="padding: 10px; margin-right: 10px;">↩️ FULLY RETURNED</span>';
    } else {
        $return_status_badge = '<span class="badge badge-warning" style="padding: 10px; margin-right: 10px;">↩️ PARTIALLY RETURNED</span>';
    }
}
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">👁️</span>
        Invoice Details
    </h2>
    <div class="page-actions">
        <?php if ($invoice['invoice_status'] === 'draft'): ?>
        <a href="edit_invoice.php?id=<?php echo $invoice_id; ?>" class="btn btn-warning" title="Edit this draft invoice">
            <span class="btn-icon">✏️</span>
            Edit Draft
        </a>
        <span class="badge badge-warning" style="padding: 10px; margin-right: 10px;">DRAFT - Finalize to accept payments</span>
        <?php endif; ?>
        
        <?php if ($invoice['invoice_status'] === 'finalized'): ?>
            <?php if ($invoice['payment_status'] !== 'paid'): ?>
            <a href="add_payment.php?id=<?php echo $invoice_id; ?>" class="btn btn-success" title="Record a payment for this invoice">
                <span class="btn-icon">💰</span>
                Add Payment
            </a>
            <?php else: ?>
            <span class="badge badge-success" style="padding: 10px; margin-right: 10px;">✓ FULLY PAID</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="print_invoice.php?id=<?php echo $invoice_id; ?>" class="btn btn-primary" target="_blank" title="Open printable invoice (save as PDF)">
            <span class="btn-icon">📄</span>
            PDF
        </a>
        
        <a href="print_pos.php?id=<?php echo $invoice_id; ?>" class="btn btn-primary" style="background-color: #334155; border-color: #334155;" target="_blank" title="Thermal Receipt Print">
            <span class="btn-icon">🖨️</span>
            POS Print
        </a>
        
        <a href="invoices.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Back
        </a>
    </div>
</div>

<?php if ($has_returns): ?>
<div class="alert alert-warning" style="border-left: 5px solid #d97706; background-color: #fffbeb; color: #92400e;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <strong>⚠️ Product Returns Found</strong><br>
            Total Refunded Amount: <strong><?php echo format_currency($returned_total); ?></strong>
        </div>
        <div>
            <?php echo $return_status_badge; ?>
            <a href="#returns-section" class="btn btn-sm btn-outline-secondary" style="background:white;">View Details</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ================================================================ -->
<!-- INVOICE DISPLAY -->
<!-- ================================================================ -->
<div class="card" id="invoice-container">
    <div class="card-body" style="padding: 40px;">
        
        <!-- Company Header -->
        <div style="border-bottom: 3px solid <?php echo $theme_color; ?>; padding-bottom: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <?php if(!empty($company['company_logo'])): ?>
                        <div style="margin-bottom: 15px;">
                            <img src="../<?php echo $company['company_logo']; ?>" style="max-height: 80px; max-width: 250px;">
                        </div>
                    <?php endif; ?>
                    
                    <h1 style="font-size: 24px; color: <?php echo $theme_color; ?>; margin-top:0; margin-bottom: 10px;">
                        <?php echo escape_html($company['company_name']); ?>
                    </h1>
                    
                    <p style="margin: 0; line-height: 1.6; color: #555;">
                        <?php echo nl2br(escape_html($company['company_address'])); ?><br>
                        <?php echo escape_html($company['company_city']); ?>, 
                        <?php echo escape_html($company['company_state']); ?> - 
                        <?php echo escape_html($company['company_pincode']); ?><br>
                        <?php if(!empty($company['company_gstin'])): ?>
                            <strong>GSTIN:</strong> <?php echo escape_html($company['company_gstin']); ?><br>
                        <?php endif; ?>
                        <strong>Phone:</strong> <?php echo escape_html($company['company_phone']); ?><br>
                        <strong>Email:</strong> <?php echo escape_html($company['company_email']); ?>
                    </p>
                </div>
                <div style="text-align: right;">
                    <h2 style="font-size: 32px; color: <?php echo $theme_color; ?>; margin: 0;">TAX INVOICE</h2>
                    <p style="margin-top: 10px; font-size: 14px;">
                        <strong style="font-size:16px;">Invoice #: <?php echo escape_html($invoice['invoice_number']); ?></strong><br>
                        <span style="color:#666;">Date: <?php echo format_date($invoice['invoice_date']); ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Bill To Section -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: <?php echo $theme_color; ?>; margin-bottom: 10px; font-size:16px; border-bottom:1px solid #eee; padding-bottom:5px; display:inline-block;">BILL TO</h3>
            <p style="margin: 0; line-height: 1.6;">
                <strong style="font-size: 18px; color: #333;"><?php echo escape_html($invoice['customer_name']); ?></strong>
                <span class="badge badge-<?php echo $invoice['customer_type'] === 'B2B' ? 'info' : 'success'; ?>" 
                      style="margin-left: 10px; font-size:11px;">
                    <?php echo $invoice['customer_type']; ?>
                </span><br>
                <?php if (!empty($invoice['customer_gstin'])): ?>
                    <strong>GSTIN:</strong> <?php echo escape_html($invoice['customer_gstin']); ?><br>
                <?php endif; ?>
                <?php echo nl2br(escape_html($invoice['customer_address'])); ?><br>
                <?php echo escape_html($invoice['billing_city']); ?>, 
                <?php echo escape_html($invoice['billing_state']); ?> - 
                <?php echo escape_html($invoice['billing_pincode']); ?>
            </p>
        </div>
        
        <!-- Invoice Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background: <?php echo $theme_color; ?>; color: white;">
                    <th style="padding: 12px; text-align: left;">#</th>
                    <th style="padding: 12px; text-align: left;">Product</th>
                    <th style="padding: 12px; text-align: center;">HSN</th>
                    <th style="padding: 12px; text-align: right;">Qty</th>
                    <th style="padding: 12px; text-align: right;">Rate</th>
                    <th style="padding: 12px; text-align: right;">Amount</th>
                    <th style="padding: 12px; text-align: center;">GST%</th>
                    <th style="padding: 12px; text-align: right;">Tax</th>
                    <th style="padding: 12px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr_no = 1;
                while ($item = mysqli_fetch_assoc($items_result)): 
                    // Process Tracking Info
                    $tracking_html = "";
                    
                    // Batch Info
                    if (!empty($item['batch_no'])) {
                        $exp = $item['expiry_date'] ? " | Exp: " . date('d/m/y', strtotime($item['expiry_date'])) : "";
                        $tracking_html .= "<div style='font-size:11px; color:#4b5563; margin-top:2px;'>Batch: <strong>{$item['batch_no']}</strong>{$exp}</div>";
                    }
                    
                    // Serial Info
                    if (!empty($item['serial_ids'])) {
                        $sids = $item['serial_ids'];
                        if (strlen($sids) > 0) {
                             $sn_res = db_query($connection, "SELECT serial_no FROM product_serials WHERE serial_id IN ($sids)");
                             $sns = [];
                             while($r = mysqli_fetch_assoc($sn_res)) $sns[] = $r['serial_no'];
                             if (!empty($sns)) {
                                 $tracking_html .= "<div style='font-size:11px; color:#4b5563; margin-top:2px; max-width:200px; word-break:break-all;'>SN: " . implode(", ", $sns) . "</div>";
                             }
                        }
                    }
                ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 10px; border-right: 1px solid #e5e7eb;"><?php echo $sr_no++; ?></td>
                    <td style="padding: 10px; border-right: 1px solid #e5e7eb;">
                        <strong><?php echo escape_html($item['product_name']); ?></strong><br>
                        <small style="color:#666;"><?php echo escape_html($item['product_code']); ?></small>
                        <?php echo $tracking_html; ?>
                    </td>
                    <td style="padding: 10px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <?php echo escape_html($item['hsn_code']); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border-right: 1px solid #e5e7eb;">
                        <?php echo $item['quantity']; ?> <?php echo escape_html($item['unit_of_measure']); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border-right: 1px solid #e5e7eb;">
                        <?php echo format_currency($item['unit_price']); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border-right: 1px solid #e5e7eb;">
                        <?php echo format_currency($item['taxable_amount']); ?>
                    </td>
                    <td style="padding: 10px; text-align: center; border-right: 1px solid #e5e7eb;">
                        <?php echo $item['gst_rate']; ?>%
                    </td>
                    <td style="padding: 10px; text-align: right; border-right: 1px solid #e5e7eb;">
                        <?php 
                        $tax = $item['cgst_amount'] + $item['sgst_amount'] + $item['igst_amount'];
                        echo format_currency($tax); 
                        ?>
                    </td>
                    <td style="padding: 10px; text-align: right;">
                        <strong><?php echo format_currency($item['total_amount']); ?></strong>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- Totals Section -->
        <div style="display: flex; justify-content: space-between; margin-top: 30px;">
            <!-- GST Breakdown -->
            <div style="width: 50%;">
                <h4 style="color: <?php echo $theme_color; ?>; margin-bottom: 10px; font-size:14px; text-transform:uppercase;">Tax Breakdown</h4>
                <table style="width: 100%; font-size: 13px; border-collapse:collapse;">
                    <?php if ($invoice['cgst_amount'] > 0): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:5px 0;">CGST</td>
                        <td style="text-align: right;"><strong><?php echo format_currency($invoice['cgst_amount']); ?></strong></td>
                    </tr>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:5px 0;">SGST</td>
                        <td style="text-align: right;"><strong><?php echo format_currency($invoice['sgst_amount']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice['igst_amount'] > 0): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:5px 0;">IGST</td>
                        <td style="text-align: right;"><strong><?php echo format_currency($invoice['igst_amount']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Amount Summary -->
            <div style="width: 40%; border: 1px solid <?php echo $theme_color; ?>; border-radius: 6px; overflow:hidden;">
                <!-- Header -->
                <div style="background: <?php echo $theme_color; ?>; color:white; padding:8px 15px; font-weight:bold; text-align:right;">
                    Summary
                </div>
                <div style="padding: 15px; background: #fff;">
                    <table style="width: 100%; font-size: 14px;">
                        <tr>
                            <td style="padding-bottom:5px;">Subtotal:</td>
                            <td style="text-align: right;"><?php echo format_currency($invoice['subtotal']); ?></td>
                        </tr>
                        <?php if ($invoice['discount_amount'] > 0): ?>
                        <tr>
                            <td style="padding-bottom:5px;">Discount:</td>
                            <td style="text-align: right; color: #ef4444;">
                                - <?php echo format_currency($invoice['discount_amount']); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="padding-bottom:5px;">Taxable Amount:</td>
                            <td style="text-align: right;"><?php echo format_currency($invoice['taxable_amount']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-bottom:5px;">Total Tax:</td>
                            <td style="text-align: right;"><?php echo format_currency($invoice['total_tax']); ?></td>
                        </tr>
                        <?php if ($invoice['round_off'] != 0): ?>
                        <tr>
                            <td style="padding-bottom:5px;">Round Off:</td>
                            <td style="text-align: right;">
                                <?php echo $invoice['round_off'] > 0 ? '+' : ''; ?>
                                <?php echo format_currency($invoice['round_off']); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr style="border-top: 2px solid <?php echo $theme_color; ?>; font-size: 18px; font-weight: bold;">
                            <td style="padding-top: 10px; color: <?php echo $theme_color; ?>;">Grand Total:</td>
                            <td style="text-align: right; padding-top: 10px; color: <?php echo $theme_color; ?>;">
                                <?php echo format_currency($invoice['total_amount']); ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div style="padding: 10px 15px; background: #f9fafb; border-top: 1px solid #eee;">
                    <p style="margin: 0; font-size: 11px; text-transform:uppercase; color:#666;">Amount in Words</p>
                    <p style="margin: 2px 0 0 0; font-style: italic; font-weight:500;">
                        <?php echo number_to_words($invoice['total_amount']); ?> Rupees Only
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Payment Status + Terms -->
        <div style="margin-top: 40px; display: flex; gap: 40px;">
            <div style="flex: 1;">
                 <h4 style="color: <?php echo $theme_color; ?>; margin-bottom: 10px; font-size:14px; text-transform:uppercase;">Terms & Conditions</h4>
                 <?php if (!empty($company['terms_conditions'])): ?>
                    <p style="font-size: 12px; line-height: 1.6; white-space: pre-line; color:#555;">
                        <?php echo escape_html($company['terms_conditions']); ?>
                    </p>
                 <?php else: ?>
                    <p style="font-size:12px; color:#999; font-style:italic;">No terms specified.</p>
                 <?php endif; ?>
            </div>
            
             <div style="width: 250px; text-align: center;">
                 <div style="margin-bottom:10px;">
                     <span class="badge badge-<?php 
                        echo $invoice['payment_status'] === 'paid' ? 'success' : 
                            ($invoice['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                    ?>" style="font-size: 14px; padding:8px 15px;">
                        <?php echo strtoupper($invoice['payment_status']); ?>
                    </span>
                 </div>
                 
                 <!-- Payment History Mini Table -->
                 <?php 
                 $pay_res = db_query($connection, "SELECT * FROM payments WHERE invoice_id='$invoice_id' ORDER BY payment_date DESC");
                 if(mysqli_num_rows($pay_res) > 0): 
                 ?>
                 <div style="margin-top: 15px; text-align: left; font-size: 12px; border: 1px solid #eee; padding: 10px; background: #f8fafc;">
                     <strong>Payment History</strong>
                     <table style="width: 100%; margin-top: 5px;">
                         <?php while($p = mysqli_fetch_assoc($pay_res)): ?>
                         <tr>
                             <td><?php echo date('d/m', strtotime($p['payment_date'])); ?></td>
                             <td style="text-align: right;"><?php echo format_currency($p['payment_amount']); ?></td>
                         </tr>
                         <?php endwhile; ?>
                         <tr style="border-top:1px solid #ccc;">
                            <td><strong>Total Paid</strong></td>
                            <td style="text-align: right;"><strong><?php echo format_currency($invoice['amount_paid']); ?></strong></td>
                         </tr>
                         <tr>
                            <td style="color: <?php echo $invoice['amount_due'] > 0 ? 'red' : 'green'; ?>"><strong>Due</strong></td>
                            <td style="text-align: right; color: <?php echo $invoice['amount_due'] > 0 ? 'red' : 'green'; ?>"><strong><?php echo format_currency($invoice['amount_due']); ?></strong></td>
                         </tr>
                     </table>
                 </div>
                 <?php endif; ?>

                 <div style="border: 1px solid #eee; padding: 15px; margin-top: 20px;">
                     <p style="margin: 0; font-size:14px; font-weight:600;">Authorized Signatory</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0; font-size: 12px;">For <?php echo escape_html($company['company_name']); ?></p>
                 </div>
            </div>
        </div>
        
    </div>
</div>

<style>
@media print {
    .main-header, .page-actions, .main-footer, .btn, .page-header {
        display: none !important;
    }
    .main-content {
        padding: 0 !important;
        margin: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
    body {
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<?php if ($has_returns): ?>
<div class="card" id="returns-section" style="margin-top:30px; border-top:3px solid #dc2626;">
    <div class="card-header">
        <h3 class="card-title">↩️ Return History</h3>
    </div>
    <div class="card-body">
        <table class="table">
             <thead><tr><th>Return #</th><th>Date</th><th>Reason</th><th class="text-right">Amount</th><th>Action</th></tr></thead>
             <tbody>
                 <?php while($ret = mysqli_fetch_assoc($returns_res)): ?>
                 <tr>
                     <td><?php echo $ret['return_number']; ?></td>
                     <td><?php echo format_date($ret['return_date']); ?></td>
                     <td><?php echo escape_html($ret['reason']); ?></td>
                     <td class="text-right">-<?php echo format_currency($ret['total_amount']); ?></td>
                     <td><a href="view_return.php?id=<?php echo $ret['return_id']; ?>" class="btn btn-sm btn-light">View</a></td>
                 </tr>
                 <?php endwhile; ?>
             </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
