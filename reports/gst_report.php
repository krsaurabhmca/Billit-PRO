<?php
/**
 * ============================================================================
 * GST REPORT PAGE
 * ============================================================================
 * Purpose: Display GST summary report with CGST, SGST, IGST breakdown
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "GST Report";

// Include header
require_once '../includes/header.php';

// ============================================================================
// FILTER PROCESSING
// ============================================================================

$date_from = isset($_GET['date_from']) ? sanitize_sql($connection, $_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? sanitize_sql($connection, $_GET['date_to']) : date('Y-m-t');

// ============================================================================
// FETCH GST SUMMARY
// ============================================================================

$gst_query = "SELECT 
              COUNT(*) as invoice_count,
              SUM(subtotal) as total_subtotal,
              SUM(discount_amount) as total_discount,
              SUM(taxable_amount) as total_taxable,
              SUM(cgst_amount) as total_cgst,
              SUM(sgst_amount) as total_sgst,
              SUM(igst_amount) as total_igst,
              SUM(total_tax) as total_gst,
              SUM(total_amount) as total_invoice_value
              FROM invoices
              WHERE invoice_status = 'finalized'
              AND invoice_date BETWEEN '{$date_from}' AND '{$date_to}'";

$gst_summary = db_fetch_one($connection, $gst_query);

// ============================================================================
// FETCH DETAILED INVOICES
// ============================================================================

$invoices_query = "SELECT i.invoice_number, i.invoice_date, c.customer_name, c.customer_type,
                   i.taxable_amount, i.cgst_amount, i.sgst_amount, i.igst_amount, 
                   i.total_tax, i.total_amount
                   FROM invoices i
                   INNER JOIN customers c ON i.customer_id = c.customer_id
                   WHERE i.invoice_status = 'finalized'
                   AND i.invoice_date BETWEEN '{$date_from}' AND '{$date_to}'
                   ORDER BY i.invoice_date DESC";

$invoices_result = db_query($connection, $invoices_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📊</span>
        GST Report
    </h2>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-primary">
            <span class="btn-icon">🖨️</span>
            Print Report
        </button>
    </div>
</div>

<!-- ================================================================ -->
<!-- DATE FILTER -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="<?php echo $date_from; ?>" required>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="<?php echo $date_to; ?>" required>
                </div>
                
                <div class="form-group col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Generate Report</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- GST SUMMARY CARDS -->
<!-- ================================================================ -->
<div class="stats-grid">
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">📋</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($gst_summary['invoice_count']); ?></div>
            <div class="stat-label">Total Invoices</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-green">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($gst_summary['total_taxable']); ?></div>
            <div class="stat-label">Taxable Amount</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-orange">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($gst_summary['total_gst']); ?></div>
            <div class="stat-label">Total GST Collected</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-purple">
        <div class="stat-icon">💵</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($gst_summary['total_invoice_value']); ?></div>
            <div class="stat-label">Total Invoice Value</div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- GST BREAKDOWN -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">GST Breakdown</h3>
        <span class="card-subtitle">
            Period: <?php echo format_date($date_from); ?> to <?php echo format_date($date_to); ?>
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tax Component</th>
                        <th style="text-align: right;">Amount (₹)</th>
                        <th style="text-align: right;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>CGST (Central GST)</strong></td>
                        <td style="text-align: right;"><strong><?php echo format_currency($gst_summary['total_cgst']); ?></strong></td>
                        <td style="text-align: right;">
                            <?php 
                            $cgst_percent = $gst_summary['total_gst'] > 0 ? 
                                ($gst_summary['total_cgst'] / $gst_summary['total_gst'] * 100) : 0;
                            echo number_format($cgst_percent, 2); 
                            ?>%
                        </td>
                    </tr>
                    <tr>
                        <td><strong>SGST (State GST)</strong></td>
                        <td style="text-align: right;"><strong><?php echo format_currency($gst_summary['total_sgst']); ?></strong></td>
                        <td style="text-align: right;">
                            <?php 
                            $sgst_percent = $gst_summary['total_gst'] > 0 ? 
                                ($gst_summary['total_sgst'] / $gst_summary['total_gst'] * 100) : 0;
                            echo number_format($sgst_percent, 2); 
                            ?>%
                        </td>
                    </tr>
                    <tr>
                        <td><strong>IGST (Integrated GST)</strong></td>
                        <td style="text-align: right;"><strong><?php echo format_currency($gst_summary['total_igst']); ?></strong></td>
                        <td style="text-align: right;">
                            <?php 
                            $igst_percent = $gst_summary['total_gst'] > 0 ? 
                                ($gst_summary['total_igst'] / $gst_summary['total_gst'] * 100) : 0;
                            echo number_format($igst_percent, 2); 
                            ?>%
                        </td>
                    </tr>
                    <tr style="background: #f3f4f6; font-weight: bold; font-size: 16px;">
                        <td>TOTAL GST</td>
                        <td style="text-align: right; color: #667eea;"><?php echo format_currency($gst_summary['total_gst']); ?></td>
                        <td style="text-align: right;">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- DETAILED INVOICE LIST -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoice-wise GST Details</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th style="text-align: right;">Taxable</th>
                        <th style="text-align: right;">CGST</th>
                        <th style="text-align: right;">SGST</th>
                        <th style="text-align: right;">IGST</th>
                        <th style="text-align: right;">Total Tax</th>
                        <th style="text-align: right;">Invoice Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($invoices_result && mysqli_num_rows($invoices_result) > 0): ?>
                        <?php while ($invoice = mysqli_fetch_assoc($invoices_result)): ?>
                            <tr>
                                <td><?php echo escape_html($invoice['invoice_number']); ?></td>
                                <td><?php echo format_date($invoice['invoice_date']); ?></td>
                                <td><?php echo escape_html($invoice['customer_name']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $invoice['customer_type'] === 'B2B' ? 'info' : 'success'; ?>">
                                        <?php echo $invoice['customer_type']; ?>
                                    </span>
                                </td>
                                <td style="text-align: right;"><?php echo format_currency($invoice['taxable_amount']); ?></td>
                                <td style="text-align: right;"><?php echo format_currency($invoice['cgst_amount']); ?></td>
                                <td style="text-align: right;"><?php echo format_currency($invoice['sgst_amount']); ?></td>
                                <td style="text-align: right;"><?php echo format_currency($invoice['igst_amount']); ?></td>
                                <td style="text-align: right;"><strong><?php echo format_currency($invoice['total_tax']); ?></strong></td>
                                <td style="text-align: right;"><strong><?php echo format_currency($invoice['total_amount']); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No invoices found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .main-header, .page-actions, .main-footer, .btn, .filter-form {
        display: none !important;
    }
}
</style>

<?php
// Include footer
require_once '../includes/footer.php';
?>
