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

// 4. FETCH PAYMENTS
$payments_query = "SELECT * FROM payments WHERE invoice_id = '{$invoice_id}' ORDER BY payment_date DESC";
$payments_result = db_query($connection, $payments_query);
$total_paid_record = 0;
// We can't iterate here if we want to loop later, so either store array or reset pointer later
// Let's store in array
$payment_records = [];
while($pay = mysqli_fetch_assoc($payments_result)){
    $total_paid_record += $pay['payment_amount'];
    $payment_records[] = $pay;
}

// 5. FETCH COMPANY SETTINGS
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
        @page { margin: 0; size: 80mm auto; }
        body { 
            margin: 0; 
            padding: 5mm; 
            width: 78mm; /* Slightly less to fit */
            font-family: 'Courier New', monospace; 
            font-size: 12px; 
            color: #000;
            background: #fff;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .header { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .store-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .store-info { font-size: 11px; margin-top: 5px; }
        
        .inv-details { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { text-align: left; border-bottom: 1px dashed #000; font-size: 11px; }
        td { font-size: 12px; padding: 4px 0; vertical-align: top; }
        
        .item-name { display: block; font-weight: bold; }
        .item-meta { font-size: 10px; margin-left: 5px; }
        
        .totals { border-top: 1px dashed #000; padding-top: 5px; margin-bottom: 10px; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .grand-total { font-size: 16px; font-weight: bold; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-top: 5px; }
        
        .footer { text-align: center; font-size: 11px; margin-top: 20px; }
        
        .no-print { display: none; }
        @media print {
            .btn-print { display: none; }
        }
        
        .btn-print { 
            display: block; width: 100%; padding: 10px; background: #000; color: #fff; 
            text-align: center; text-decoration: none; margin-bottom: 20px; font-family: sans-serif;
        }
    </style>
</head>
<body>
    <a href="javascript:window.print()" class="btn-print">Print Receipt</a>

    <div class="header text-center">
        <div class="store-name"><?php echo escape_html($company['company_name']); ?></div>
        <div class="store-info">
            <?php echo parse_address($company['company_address']); ?><br>
            <?php echo escape_html($company['company_city']); ?><br>
            Ph: <?php echo escape_html($company['company_phone']); ?>
            <?php if(!empty($company['company_gstin'])): ?>
            <br>GSTIN: <?php echo escape_html($company['company_gstin']); ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="inv-details">
        <div>Inv No: <span class="bold"><?php echo escape_html($invoice['invoice_number']); ?></span></div>
        <div>Date: <?php echo date('d-m-Y h:i A', strtotime($invoice['created_at'])); ?></div>
        <div>Cust: <?php echo escape_html($invoice['customer_name']); ?></div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width:45%">Item</th>
                <th style="width:15%" class="text-center">Qty</th>
                <th style="width:20%" class="text-right">Rate</th>
                <th style="width:20%" class="text-right">Amt</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            mysqli_data_seek($items_result, 0); // Reset pointer
            while($item = mysqli_fetch_assoc($items_result)): 
            ?>
            <tr>
                <td colspan="4" style="padding-bottom:0;">
                    <span class="item-name"><?php echo escape_html($item['product_name']); ?></span>
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-right"><?php echo format_currency($item['unit_price']); ?></td>
                <td class="text-right"><?php echo format_currency($item['total_amount']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span><?php echo format_currency($invoice['subtotal']); ?></span>
        </div>
        <?php if($invoice['discount_amount'] > 0): ?>
        <div class="totals-row">
            <span>Discount</span>
            <span>-<?php echo format_currency($invoice['discount_amount']); ?></span>
        </div>
        <?php endif; ?>
        <div class="totals-row">
            <span>GST</span>
            <span><?php echo format_currency($invoice['total_tax']); ?></span>
        </div>
         <?php if($invoice['round_off'] != 0): ?>
        <div class="totals-row">
            <span>Round Off</span>
            <span><?php echo format_currency($invoice['round_off']); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="totals-row grand-total">
            <span>TOTAL</span>
            <span><?php echo format_currency($invoice['total_amount']); ?></span>
        </div>
        
        <div class="totals-row" style="margin-top:5px;">
            <span>Paid</span>
            <span><?php echo format_currency($invoice['amount_paid']); ?></span>
        </div>
        <div class="totals-row">
            <span>Balance</span>
            <span><?php echo format_currency($invoice['amount_due']); ?></span>
        </div>
    </div>
    
    <div class="footer">
        <p>Thank You! Visit Again.</p>
        <p style="font-size:9px;">Powered by Billit</p>
    </div>
    
    <script>
        // Helper to parse address logic if needed, or simple nl2br
        // For now, simple echo is fine
    </script>
</body>
</html>
<?php
// Functions helper if needed
function parse_address($addr) {
    return str_replace("\n", ", ", $addr);
}
?>
