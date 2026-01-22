<?php
/**
 * ============================================================================
 * GENERATE PDF INVOICE
 * ============================================================================
 * Purpose: Generate printable HTML invoice that can be saved as PDF
 * Author: Inventory Management System
 * Date: 2026-01-23
 * Note: Uses browser print function to save as PDF
 * ============================================================================
 */

// Include configuration and functions
require_once '../config/config.php';
require_once '../includes/functions.php';

// Require login
require_login();

// ============================================================================
// GET INVOICE ID
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    die("Invalid invoice ID.");
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
    die("Invoice not found.");
}

// Fetch invoice items
$items_query = "SELECT * FROM invoice_items WHERE invoice_id = '{$invoice_id}' ORDER BY item_id";
$items_result = db_query($connection, $items_query);

// Fetch company settings
$company_query = "SELECT * FROM company_settings LIMIT 1";
$company = db_fetch_one($connection, $company_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo escape_html($invoice['invoice_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .company-info h1 {
            font-size: 24px;
            color: #2563eb;
            margin-bottom: 8px;
        }
        
        .company-info p {
            font-size: 11px;
            line-height: 1.6;
            color: #555;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .invoice-title .invoice-meta {
            font-size: 12px;
        }
        
        .invoice-title .invoice-meta strong {
            color: #333;
        }
        
        /* Bill To Section */
        .bill-to {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        
        .bill-to h3 {
            font-size: 12px;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .bill-to .customer-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .customer-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #2563eb;
            color: white;
            font-size: 10px;
            border-radius: 10px;
            margin-left: 8px;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .items-table th {
            background: #2563eb;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .product-name {
            font-weight: 600;
        }
        
        .product-code {
            font-size: 10px;
            color: #6b7280;
        }
        
        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .tax-breakdown {
            width: 45%;
        }
        
        .tax-breakdown h4 {
            font-size: 12px;
            color: #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .tax-breakdown table {
            width: 100%;
            font-size: 11px;
        }
        
        .tax-breakdown td {
            padding: 4px 0;
        }
        
        .totals-box {
            width: 45%;
            background: #f8fafc;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 15px;
        }
        
        .totals-box table {
            width: 100%;
            font-size: 12px;
        }
        
        .totals-box td {
            padding: 5px 0;
        }
        
        .totals-box .grand-total {
            border-top: 2px solid #2563eb;
            font-size: 16px;
            font-weight: 700;
            color: #2563eb;
        }
        
        .totals-box .grand-total td {
            padding-top: 10px;
        }
        
        .amount-words {
            font-size: 11px;
            font-style: italic;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Bank Details */
        .bank-details {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        
        .bank-details h4 {
            font-size: 12px;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .bank-details table {
            font-size: 11px;
        }
        
        .bank-details td {
            padding: 3px 15px 3px 0;
        }
        
        /* Terms */
        .terms {
            margin-bottom: 25px;
        }
        
        .terms h4 {
            font-size: 12px;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .terms p {
            font-size: 10px;
            color: #6b7280;
            white-space: pre-line;
        }
        
        /* Signature */
        .signature {
            text-align: right;
            margin-top: 40px;
        }
        
        .signature-line {
            display: inline-block;
            width: 200px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 11px;
        }
        
        /* Print Button */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }
        
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-print:hover {
            background: #1d4ed8;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #6b7280;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            margin-left: 10px;
        }
        
        @media print {
            .print-actions {
                display: none !important;
            }
            
            body {
                background: white;
            }
            
            .invoice-container {
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print / Save PDF
        </button>
        <a href="view_invoice.php?id=<?php echo $invoice_id; ?>" class="btn-back">
            ← Back
        </a>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1><?php echo escape_html($company['company_name']); ?></h1>
                <p>
                    <?php echo nl2br(escape_html($company['company_address'])); ?><br>
                    <?php echo escape_html($company['company_city']); ?>, 
                    <?php echo escape_html($company['company_state']); ?> - 
                    <?php echo escape_html($company['company_pincode']); ?><br>
                    <strong>GSTIN:</strong> <?php echo escape_html($company['company_gstin']); ?><br>
                    <strong>Phone:</strong> <?php echo escape_html($company['company_phone']); ?> | 
                    <strong>Email:</strong> <?php echo escape_html($company['company_email']); ?>
                </p>
            </div>
            <div class="invoice-title">
                <h2>TAX INVOICE</h2>
                <div class="invoice-meta">
                    <strong>Invoice #:</strong> <?php echo escape_html($invoice['invoice_number']); ?><br>
                    <strong>Date:</strong> <?php echo format_date($invoice['invoice_date']); ?><br>
                    <strong>Status:</strong> 
                    <?php if ($invoice['payment_status'] === 'paid'): ?>
                        <span style="color: #059669;">PAID</span>
                    <?php elseif ($invoice['payment_status'] === 'partial'): ?>
                        <span style="color: #d97706;">PARTIAL</span>
                    <?php else: ?>
                        <span style="color: #dc2626;">UNPAID</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Bill To -->
        <div class="bill-to">
            <h3>Bill To</h3>
            <div class="customer-name">
                <?php echo escape_html($invoice['customer_name']); ?>
                <span class="customer-badge"><?php echo $invoice['customer_type']; ?></span>
            </div>
            <?php if (!empty($invoice['customer_gstin'])): ?>
                <strong>GSTIN:</strong> <?php echo escape_html($invoice['customer_gstin']); ?><br>
            <?php endif; ?>
            <?php echo nl2br(escape_html($invoice['customer_address'])); ?><br>
            <?php echo escape_html($invoice['billing_city']); ?>, 
            <?php echo escape_html($invoice['billing_state']); ?> - 
            <?php echo escape_html($invoice['billing_pincode']); ?>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Product</th>
                    <th style="width: 10%;">HSN</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 12%;">Rate</th>
                    <th style="width: 12%;">Amount</th>
                    <th style="width: 8%;">GST%</th>
                    <th style="width: 13%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr_no = 1;
                while ($item = mysqli_fetch_assoc($items_result)): 
                    $tax = $item['cgst_amount'] + $item['sgst_amount'] + $item['igst_amount'];
                ?>
                <tr>
                    <td><?php echo $sr_no++; ?></td>
                    <td>
                        <div class="product-name"><?php echo escape_html($item['product_name']); ?></div>
                        <div class="product-code"><?php echo escape_html($item['product_code']); ?></div>
                    </td>
                    <td><?php echo escape_html($item['hsn_code']); ?></td>
                    <td><?php echo $item['quantity']; ?> <?php echo escape_html($item['unit_of_measure']); ?></td>
                    <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>₹<?php echo number_format($item['taxable_amount'], 2); ?></td>
                    <td><?php echo $item['gst_rate']; ?>%</td>
                    <td><strong>₹<?php echo number_format($item['total_amount'], 2); ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- Summary Section -->
        <div class="summary-section">
            <!-- Tax Breakdown -->
            <div class="tax-breakdown">
                <h4>Tax Breakdown</h4>
                <table>
                    <?php if ($invoice['cgst_amount'] > 0): ?>
                    <tr>
                        <td>CGST:</td>
                        <td style="text-align: right;"><strong>₹<?php echo number_format($invoice['cgst_amount'], 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>SGST:</td>
                        <td style="text-align: right;"><strong>₹<?php echo number_format($invoice['sgst_amount'], 2); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice['igst_amount'] > 0): ?>
                    <tr>
                        <td>IGST:</td>
                        <td style="text-align: right;"><strong>₹<?php echo number_format($invoice['igst_amount'], 2); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="border-top: 1px solid #e5e7eb;">
                        <td><strong>Total Tax:</strong></td>
                        <td style="text-align: right;"><strong>₹<?php echo number_format($invoice['total_tax'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <!-- Totals Box -->
            <div class="totals-box">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;">₹<?php echo number_format($invoice['subtotal'], 2); ?></td>
                    </tr>
                    <?php if ($invoice['discount_amount'] > 0): ?>
                    <tr>
                        <td>Discount:</td>
                        <td style="text-align: right; color: #dc2626;">- ₹<?php echo number_format($invoice['discount_amount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Taxable Amount:</td>
                        <td style="text-align: right;">₹<?php echo number_format($invoice['taxable_amount'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>GST:</td>
                        <td style="text-align: right;">₹<?php echo number_format($invoice['total_tax'], 2); ?></td>
                    </tr>
                    <?php if ($invoice['round_off'] != 0): ?>
                    <tr>
                        <td>Round Off:</td>
                        <td style="text-align: right;">
                            <?php echo $invoice['round_off'] > 0 ? '+' : ''; ?>₹<?php echo number_format($invoice['round_off'], 2); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr class="grand-total">
                        <td>Grand Total:</td>
                        <td style="text-align: right;">₹<?php echo number_format($invoice['total_amount'], 2); ?></td>
                    </tr>
                </table>
                
                <div class="amount-words">
                    <strong>Amount in Words:</strong><br>
                    <?php echo number_to_words($invoice['total_amount']); ?> Rupees Only
                </div>
            </div>
        </div>
        
        <!-- Bank Details -->
        <?php if (!empty($company['bank_name'])): ?>
        <div class="bank-details">
            <h4>Bank Details</h4>
            <table>
                <tr>
                    <td><strong>Bank:</strong></td>
                    <td><?php echo escape_html($company['bank_name']); ?></td>
                    <td><strong>Account No:</strong></td>
                    <td><?php echo escape_html($company['bank_account_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>IFSC:</strong></td>
                    <td><?php echo escape_html($company['bank_ifsc']); ?></td>
                    <td><strong>Branch:</strong></td>
                    <td><?php echo escape_html($company['bank_branch']); ?></td>
                </tr>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Terms & Conditions -->
        <?php if (!empty($company['terms_conditions'])): ?>
        <div class="terms">
            <h4>Terms & Conditions</h4>
            <p><?php echo escape_html($company['terms_conditions']); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Signature -->
        <div class="signature">
            <p><strong>For <?php echo escape_html($company['company_name']); ?></strong></p>
            <div style="height: 50px;"></div>
            <div class="signature-line">Authorized Signatory</div>
        </div>
    </div>
    
    <script>
        // Auto-trigger print dialog if requested
        <?php if (isset($_GET['print']) && $_GET['print'] == '1'): ?>
        window.onload = function() {
            window.print();
        };
        <?php endif; ?>
    </script>
</body>
</html>
