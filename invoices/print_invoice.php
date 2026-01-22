<?php
/**
 * ============================================================================
 * PRINT INVOICE PAGE
 * ============================================================================
 * Purpose: Printable Invoice with Custom Branding
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// 1. GET INVOICE ID
if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    die("Invalid invoice ID.");
}
$invoice_id = sanitize_sql($connection, $_GET['id']);

// 2. FETCH INVOICE
$invoice_query = "SELECT i.*, c.customer_name, c.customer_type, c.gstin as customer_gstin,
                  c.billing_address, c.billing_city, c.billing_state, c.billing_pincode
                  FROM invoices i
                  INNER JOIN customers c ON i.customer_id = c.customer_id
                  WHERE i.invoice_id = '{$invoice_id}' LIMIT 1";
$invoice = db_fetch_one($connection, $invoice_query);

if (!$invoice) die("Invoice not found.");

// 3. FETCH ITEMS WITH TRACKING
$items_query = "SELECT ii.*, pb.batch_no, pb.expiry_date 
                FROM invoice_items ii 
                LEFT JOIN product_batches pb ON ii.batch_id = pb.batch_id 
                WHERE ii.invoice_id = '{$invoice_id}' ORDER BY ii.item_id";
$items_result = db_query($connection, $items_query);

// 4. FETCH COMPANY SETTINGS
$company_query = "SELECT * FROM company_settings LIMIT 1";
$company = db_fetch_one($connection, $company_query);

// Theme Color
$theme_color = !empty($company['invoice_color']) ? $company['invoice_color'] : '#2563eb';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo escape_html($invoice['invoice_number']); ?></title>
    <style>
        @page { margin: 0; size: A4; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.4; color: #333; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        .invoice-container { width: 210mm; min-height: 297mm; margin: 0 auto; background: white; padding: 15mm; position: relative; }
        
        /* Flex Utils */
        .row { display: flex; width: 100%; justify-content: space-between; }
        .col-6 { width: 48%; }
        .col-12 { width: 100%; }
        
        /* Header Section */
        .header-section { margin-bottom: 30px; border-bottom: 2px solid <?php echo $theme_color; ?>; padding-bottom: 15px; }
        .logo-area { display: flex; align-items: start; }
        .logo-area img { max-height: 80px; max-width: 250px; }
        
        .company-details { text-align: right; }
        .company-name { font-size: 24px; font-weight: 700; color: <?php echo $theme_color; ?>; margin: 0 0 5px 0; text-transform: uppercase; }
        .company-meta { font-size: 11px; color: #555; line-height: 1.5; }
        
        /* Info Section */
        .info-section { margin-bottom: 25px; }
        .section-title { color: <?php echo $theme_color; ?>; font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 3px; margin-bottom: 8px; }
        
        .bill-to p { margin: 0; font-size: 13px; line-height: 1.5; }
        .bill-to strong { font-size: 15px; }
        
        .invoice-details { text-align: right; }
        .detail-row { display: flex; justify-content: flex-end; margin-bottom: 3px; }
        .detail-label { color: #666; width: 100px; font-size: 12px; }
        .detail-value { font-weight: 600; font-size: 13px; }
        
        /* Table */
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th { background-color: <?php echo $theme_color; ?>; color: white; padding: 8px 10px; text-align: left; font-size: 12px; text-transform: uppercase; }
        .main-table td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 12px; vertical-align: top; }
        .main-table .text-right { text-align: right; }
        .main-table .text-center { text-align: center; }
        
        .tracking-info { font-size: 10px; color: #666; margin-top: 2px; }
        
        /* Footer Split */
        .footer-split { display: flex; justify-content: space-between; margin-top: 10px; }
        .footer-left { width: 55%; }
        .footer-right { width: 40%; }
        
        /* Summary Box */
        .summary-box { background: #f9fafb; padding: 15px; border-radius: 6px; border: 1px solid <?php echo $theme_color; ?>; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .summary-row.total { font-size: 16px; font-weight: 700; color: <?php echo $theme_color; ?>; border-top: 2px solid <?php echo $theme_color; ?>; padding-top: 10px; margin-top: 5px; }
        
        .amount-words { font-size: 11px; font-style: italic; color: #666; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
        
        /* Bank & Terms */
        .bank-info { background: #f8fafc; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 11px; border-left: 3px solid <?php echo $theme_color; ?>; }
        .terms p { font-size: 10px; color: #666; white-space: pre-line; margin: 0; }
        
        /* Signature */
        .signature-box { margin-top: 40px; text-align: right; }
        .auth-sign { display: inline-block; border-top: 1px solid #333; width: 180px; padding-top: 5px; font-size: 11px; font-weight: 600; }
        
        /* Print Hide */
        @media print {
            .no-print { display: none !important; }
            .invoice-container { box-shadow: none; margin: 0; padding: 15mm; }
        }
        
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 4px; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <a href="javascript:window.print()" class="no-print btn-print">🖨️ Print Invoice</a>

    <div class="invoice-container">
        <!-- 1. Header Row -->
        <div class="header-section">
            <div class="row">
                <div class="col-3 logo-area">
                    <?php if(!empty($company['company_logo'])): ?>
                        <img src="../<?php echo $company['company_logo']; ?>" alt="Logo">
                    <?php endif; ?>
                </div>
                <div class="col-9 company-details">
                    <div class="company-name"><?php echo escape_html($company['company_name']); ?></div>
                    <div class="company-meta">
                        <?php echo nl2br(escape_html($company['company_address'])); ?><br>
                        <?php echo escape_html($company['company_city']); ?>, <?php echo escape_html($company['company_state']); ?><br>
                        <strong>GSTIN:</strong> <?php echo escape_html($company['company_gstin']); ?><br>
                        Phone: <?php echo escape_html($company['company_phone']); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 2. Info Row -->
        <div class="info-section">
            <div class="row">
                <div class="col-6 bill-to">
                    <div class="section-title">Bill To</div>
                    <p>
                        <strong><?php echo escape_html($invoice['customer_name']); ?></strong><br>
                        <?php echo nl2br(escape_html($invoice['customer_address'])); ?><br>
                        <?php echo escape_html($invoice['billing_city']); ?>, <?php echo escape_html($invoice['billing_state']); ?><br>
                        <?php if(!empty($invoice['customer_gstin'])): ?>
                            <strong>GSTIN:</strong> <?php echo escape_html($invoice['customer_gstin']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-6 invoice-details">
                    <div class="section-title" style="text-align:right;">Invoice Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Invoice No:</span>
                        <span class="detail-value"><?php echo escape_html($invoice['invoice_number']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?php echo format_date($invoice['invoice_date']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" style="text-transform:uppercase;"><?php echo $invoice['payment_status']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 3. Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:35%;">Product</th>
                    <th style="width:10%;" class="text-center">HSN</th>
                    <th style="width:10%;" class="text-right">Qty</th>
                    <th style="width:10%;" class="text-right">Rate</th>
                    <th style="width:5%;" class="text-center">GST</th>
                    <th style="width:10%;" class="text-right">Tax</th>
                    <th style="width:15%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $sr=1; while($item = mysqli_fetch_assoc($items_result)): 
                        $tracking_html = "";
                        if (!empty($item['batch_no'])) {
                            $exp = $item['expiry_date'] ? " Exp:".date('d/m/y', strtotime($item['expiry_date'])) : "";
                            $tracking_html .= "<div>Batch:{$item['batch_no']}{$exp}</div>";
                        }
                        if (!empty($item['serial_ids'])) {
                             // Quick fetch for Print efficiency (or could join)
                             // Re-using logic:
                             if(strlen($item['serial_ids'])>0) {
                                $sn_res = db_query($connection, "SELECT serial_no FROM product_serials WHERE serial_id IN ({$item['serial_ids']})");
                                $sns = [];
                                while($r=mysqli_fetch_assoc($sn_res))$sns[]=$r['serial_no'];
                                if($sns) $tracking_html .= "<div>SN: ".implode(", ",$sns)."</div>";
                             }
                        }
                        
                        $tax_amt = $item['cgst_amount'] + $item['sgst_amount'] + $item['igst_amount'];
                ?>
                <tr>
                    <td><?php echo $sr++; ?></td>
                    <td>
                        <strong><?php echo escape_html($item['product_name']); ?></strong>
                        <div class="tracking-info"><?php echo $tracking_html; ?></div>
                    </td>
                    <td class="text-center"><?php echo escape_html($item['hsn_code']); ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right"><?php echo format_currency($item['unit_price']); ?></td>
                    <td class="text-center"><?php echo $item['gst_rate']; ?>%</td>
                    <td class="text-right"><?php echo format_currency($tax_amt); ?></td>
                    <td class="text-right"><strong><?php echo format_currency($item['total_amount']); ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- 4. Footer Split -->
        <div class="footer-split">
            <div class="footer-left">
                <?php if(!empty($company['bank_name'])): ?>
                <div class="bank-info">
                    <div style="font-weight:700; margin-bottom:5px;">Bank Details</div>
                    Bank: <?php echo escape_html($company['bank_name']); ?><br>
                    A/c No: <?php echo escape_html($company['bank_account_number']); ?><br>
                    IFSC: <?php echo escape_html($company['bank_ifsc']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($company['terms_conditions'])): ?>
                <div class="terms">
                    <div class="section-title">Terms & Conditions</div>
                    <p><?php echo escape_html($company['terms_conditions']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-right">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?php echo format_currency($invoice['subtotal']); ?></span>
                    </div>
                    <?php if($invoice['discount_amount']>0): ?>
                    <div class="summary-row">
                        <span>Discount:</span>
                        <span style="color:red">-<?php echo format_currency($invoice['discount_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Total Tax:</span>
                        <span><?php echo format_currency($invoice['total_tax']); ?></span>
                    </div>
                    <?php if($invoice['round_off']!=0): ?>
                    <div class="summary-row">
                        <span>Round Off:</span>
                        <span><?php echo format_currency($invoice['round_off']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span>Grand Total:</span>
                        <span><?php echo format_currency($invoice['total_amount']); ?></span>
                    </div>
                    <div class="amount-words">
                        <?php echo number_to_words($invoice['total_amount']); ?> Rupees Only
                    </div>
                </div>
                
                <div class="signature-box">
                    <p style="margin-bottom:40px; font-size:11px;">For <?php echo escape_html($company['company_name']); ?></p>
                    <span class="auth-sign">Authorized Signatory</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
