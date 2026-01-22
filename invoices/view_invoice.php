<?php
/**
 * ============================================================================
 * VIEW INVOICE PAGE
 * ============================================================================
 * Purpose: Display invoice details with GST breakdown
 * Author: Inventory Management System
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

$items_query = "SELECT * FROM invoice_items WHERE invoice_id = '{$invoice_id}' ORDER BY item_id";
$items_result = db_query($connection, $items_query);

// ============================================================================
// FETCH COMPANY SETTINGS
// ============================================================================

$company_query = "SELECT * FROM company_settings LIMIT 1";
$company = db_fetch_one($connection, $company_query);
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
        
        <a href="print_invoice.php?id=<?php echo $invoice_id; ?>" class="btn btn-primary" title="Open printable invoice (save as PDF)">
            <span class="btn-icon">📄</span>
            PDF / Print
        </a>
        
        <a href="invoices.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Back
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- INVOICE DISPLAY -->
<!-- ================================================================ -->
<div class="card" id="invoice-container">
    <div class="card-body" style="padding: 40px;">
        
        <!-- Company Header -->
        <div style="border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h1 style="font-size: 28px; color: #2563eb; margin-bottom: 10px;">
                        <?php echo escape_html($company['company_name']); ?>
                    </h1>
                    <p style="margin: 0; line-height: 1.6;">
                        <?php echo nl2br(escape_html($company['company_address'])); ?><br>
                        <?php echo escape_html($company['company_city']); ?>, 
                        <?php echo escape_html($company['company_state']); ?> - 
                        <?php echo escape_html($company['company_pincode']); ?><br>
                        <strong>GSTIN:</strong> <?php echo escape_html($company['company_gstin']); ?><br>
                        <strong>Phone:</strong> <?php echo escape_html($company['company_phone']); ?><br>
                        <strong>Email:</strong> <?php echo escape_html($company['company_email']); ?>
                    </p>
                </div>
                <div style="text-align: right;">
                    <h2 style="font-size: 32px; color: #2563eb; margin: 0;">TAX INVOICE</h2>
                    <p style="margin-top: 10px; font-size: 14px;">
                        <strong>Invoice #:</strong> <?php echo escape_html($invoice['invoice_number']); ?><br>
                        <strong>Date:</strong> <?php echo format_date($invoice['invoice_date']); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Bill To Section -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #2563eb; margin-bottom: 10px;">Bill To:</h3>
            <p style="margin: 0; line-height: 1.6;">
                <strong style="font-size: 16px;"><?php echo escape_html($invoice['customer_name']); ?></strong>
                <span class="badge badge-<?php echo $invoice['customer_type'] === 'B2B' ? 'info' : 'success'; ?>" 
                      style="margin-left: 10px;">
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
                <tr style="background: #f3f4f6; border-bottom: 2px solid #2563eb;">
                    <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">#</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Product</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #e5e7eb;">HSN</th>
                    <th style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">Qty</th>
                    <th style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">Rate</th>
                    <th style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">Amount</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #e5e7eb;">GST%</th>
                    <th style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">Tax</th>
                    <th style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr_no = 1;
                while ($item = mysqli_fetch_assoc($items_result)): 
                ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;"><?php echo $sr_no++; ?></td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">
                        <strong><?php echo escape_html($item['product_name']); ?></strong><br>
                        <small><?php echo escape_html($item['product_code']); ?></small>
                    </td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">
                        <?php echo escape_html($item['hsn_code']); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border: 1px solid #e5e7eb;">
                        <?php echo $item['quantity']; ?> <?php echo escape_html($item['unit_of_measure']); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border: 1px solid #e5e7eb;">
                        <?php echo format_currency($item['unit_price']); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border: 1px solid #e5e7eb;">
                        <?php echo format_currency($item['taxable_amount']); ?>
                    </td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">
                        <?php echo $item['gst_rate']; ?>%
                    </td>
                    <td style="padding: 10px; text-align: right; border: 1px solid #e5e7eb;">
                        <?php 
                        $tax = $item['cgst_amount'] + $item['sgst_amount'] + $item['igst_amount'];
                        echo format_currency($tax); 
                        ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border: 1px solid #e5e7eb;">
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
                <h4 style="color: #2563eb; margin-bottom: 10px;">Tax Breakdown:</h4>
                <table style="width: 100%; font-size: 14px;">
                    <?php if ($invoice['cgst_amount'] > 0): ?>
                    <tr>
                        <td>CGST:</td>
                        <td style="text-align: right;"><strong><?php echo format_currency($invoice['cgst_amount']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>SGST:</td>
                        <td style="text-align: right;"><strong><?php echo format_currency($invoice['sgst_amount']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice['igst_amount'] > 0): ?>
                    <tr>
                        <td>IGST:</td>
                        <td style="text-align: right;"><strong><?php echo format_currency($invoice['igst_amount']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Amount Summary -->
            <div style="width: 45%; border: 2px solid #2563eb; padding: 15px; background: #f9fafb;">
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;"><?php echo format_currency($invoice['subtotal']); ?></td>
                    </tr>
                    <?php if ($invoice['discount_amount'] > 0): ?>
                    <tr>
                        <td>Discount:</td>
                        <td style="text-align: right; color: #ef4444;">
                            - <?php echo format_currency($invoice['discount_amount']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Taxable Amount:</td>
                        <td style="text-align: right;"><?php echo format_currency($invoice['taxable_amount']); ?></td>
                    </tr>
                    <tr>
                        <td>Total Tax:</td>
                        <td style="text-align: right;"><?php echo format_currency($invoice['total_tax']); ?></td>
                    </tr>
                    <?php if ($invoice['round_off'] != 0): ?>
                    <tr>
                        <td>Round Off:</td>
                        <td style="text-align: right;">
                            <?php echo $invoice['round_off'] > 0 ? '+' : ''; ?>
                            <?php echo format_currency($invoice['round_off']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr style="border-top: 2px solid #667eea; font-size: 18px; font-weight: bold;">
                        <td style="padding-top: 10px;">Grand Total:</td>
                        <td style="text-align: right; padding-top: 10px; color: #667eea;">
                            <?php echo format_currency($invoice['total_amount']); ?>
                        </td>
                    </tr>
                </table>
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                    <p style="margin: 0; font-size: 12px;"><strong>Amount in Words:</strong></p>
                    <p style="margin: 5px 0 0 0; font-style: italic;">
                        <?php echo number_to_words($invoice['total_amount']); ?> Rupees Only
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Payment Status -->
        <div style="margin-top: 30px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="margin: 0 0 5px 0; color: #667eea;">Payment Status</h4>
                    <span class="badge badge-<?php 
                        echo $invoice['payment_status'] === 'paid' ? 'success' : 
                            ($invoice['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                    ?>" style="font-size: 14px;">
                        <?php echo ucfirst($invoice['payment_status']); ?>
                    </span>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; font-size: 14px;">
                        <strong>Paid:</strong> <?php echo format_currency($invoice['amount_paid']); ?><br>
                        <strong>Due:</strong> <span style="color: #ef4444;"><?php echo format_currency($invoice['amount_due']); ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Terms and Conditions -->
        <?php if (!empty($company['terms_conditions'])): ?>
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h4 style="color: #667eea; margin-bottom: 10px;">Terms & Conditions:</h4>
            <p style="font-size: 12px; line-height: 1.6; white-space: pre-line;">
                <?php echo escape_html($company['terms_conditions']); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Signature -->
        <div style="margin-top: 40px; text-align: right;">
            <p style="margin: 0;">
                <strong>For <?php echo escape_html($company['company_name']); ?></strong>
            </p>
            <div style="height: 60px;"></div>
            <p style="margin: 0; border-top: 1px solid #000; display: inline-block; padding-top: 5px;">
                Authorized Signatory
            </p>
        </div>
        
    </div>
</div>

<style>
@media print {
    .main-header, .page-actions, .main-footer, .btn {
        display: none !important;
    }
    .main-content {
        padding: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>

<?php
// Include footer
require_once '../includes/footer.php';
?>
