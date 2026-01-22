<?php
/**
 * ============================================================================
 * SALES REPORT
 * ============================================================================
 * Purpose: View and export sales data
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $filename = "sales_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Invoice #', 'Customer', 'Taxable Amount', 'Total Tax', 'Total Amount', 'Status']);
    
    // Get params
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
    $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
    
    $query = "SELECT * FROM invoices WHERE invoice_date BETWEEN '$start_date' AND '$end_date'";
    if (!empty($customer_id)) {
        $query .= " AND customer_id = '$customer_id'";
    }
    $query .= " ORDER BY invoice_date DESC";
    
    $result = db_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['invoice_date'],
            $row['invoice_number'],
            $row['customer_name'],
            $row['taxable_amount'],
            $row['total_tax'],
            $row['total_amount'],
            ucfirst($row['payment_status'])
        ]);
    }
    fclose($output);
    exit;
}

$page_title = "Sales Report";
require_once '../includes/header.php';

// Filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';

// Fetch Customers
$customers = db_fetch_all($connection, "SELECT customer_id, customer_name FROM customers ORDER BY customer_name");

// Fetch Data
$query = "SELECT * FROM invoices WHERE invoice_date BETWEEN '$start_date' AND '$end_date'";
if (!empty($customer_id)) {
    $query .= " AND customer_id = '$customer_id'";
}
$query .= " ORDER BY invoice_date DESC";
$invoices = db_fetch_all($connection, $query);

// Calc Totals
$total_revenue = 0;
$total_tax = 0;
foreach ($invoices as $inv) {
    $total_revenue += $inv['total_amount'];
    $total_tax += $inv['total_tax'];
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">💰</span>
            Sales Report
        </h2>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">
            <span class="btn-icon">🖨️</span> Print / PDF
        </button>
        <a href="?export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&customer_id=<?php echo $customer_id; ?>" class="btn btn-primary">
            <span class="btn-icon">⬇️</span> Export CSV
        </a>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="form-group col-md-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="form-group col-md-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Customer</label>
                <select name="customer_id" class="form-control">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?php echo $c['customer_id']; ?>" <?php echo $customer_id == $c['customer_id'] ? 'selected' : ''; ?>>
                            <?php echo escape_html($c['customer_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter Report</button>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card-green">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">🧾</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo count($invoices); ?></div>
            <div class="stat-label">Total Invoices</div>
        </div>
    </div>
    <div class="stat-card stat-card-purple">
        <div class="stat-icon">🏛️</div>
        <div class="stat-content">
            <div class="stat-value">₹<?php echo number_format($total_tax, 2); ?></div>
            <div class="stat-label">Total Tax Collected</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th class="text-right">Taxable</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td>
                                <td><a href="../invoices/view_invoice.php?id=<?php echo $inv['invoice_id']; ?>">#<?php echo $inv['invoice_number']; ?></a></td>
                                <td><?php echo escape_html($inv['customer_name']); ?></td>
                                <td class="text-right">₹<?php echo number_format($inv['taxable_amount'], 2); ?></td>
                                <td class="text-right">₹<?php echo number_format($inv['total_tax'], 2); ?></td>
                                <td class="text-right"><strong>₹<?php echo number_format($inv['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $inv['payment_status'] == 'paid' ? 'success' : ($inv['payment_status'] == 'partial' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($inv['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
