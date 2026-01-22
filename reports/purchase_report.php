<?php
/**
 * ============================================================================
 * PURCHASE REPORT
 * ============================================================================
 * Purpose: View purchase history (Stock In)
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $filename = "purchase_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Ref #', 'Product', 'Quantity', 'Unit Cost', 'Total Cost', 'Notes']);
    
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
    
    $query = "SELECT t.*, p.product_name 
              FROM stock_transactions t 
              LEFT JOIN products p ON t.product_id = p.product_id
              WHERE t.transaction_type = 'stock_in' 
              AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
              ORDER BY t.transaction_date DESC";
              
    $result = db_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['transaction_date'],
            $row['reference_number'],
            $row['product_name'],
            $row['quantity'],
            $row['unit_price'],
            $row['total_amount'],
            $row['notes']
        ]);
    }
    fclose($output);
    exit;
}

$page_title = "Purchase Report";
require_once '../includes/header.php';

// Filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Data
$query = "SELECT t.*, p.product_name 
          FROM stock_transactions t 
          LEFT JOIN products p ON t.product_id = p.product_id
          WHERE t.transaction_type = 'stock_in' 
          AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
          ORDER BY t.transaction_date DESC";
$transactions = db_fetch_all($connection, $query);

$total_spend = 0;
foreach ($transactions as $t) {
    $total_spend += $t['total_amount'];
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📥</span>
            Purchase Report
        </h2>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">
            <span class="btn-icon">🖨️</span> Print / PDF
        </button>
        <a href="?export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-primary">
            <span class="btn-icon">⬇️</span> Export CSV
        </a>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="form-group col-md-4">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="form-group col-md-4">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="form-group col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filter Report</button>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value">₹<?php echo number_format($total_spend, 2); ?></div>
            <div class="stat-label">Total Purchases</div>
        </div>
    </div>
    <div class="stat-card stat-card-purple">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo count($transactions); ?></div>
            <div class="stat-label">Transactions</div>
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
                        <th>Ref #</th>
                        <th>Product</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Total Cost</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($t['transaction_date'])); ?></td>
                                <td><?php echo escape_html($t['reference_number']); ?></td>
                                <td><?php echo escape_html($t['product_name']); ?></td>
                                <td class="text-right"><?php echo $t['quantity']; ?></td>
                                <td class="text-right">₹<?php echo number_format($t['unit_price'], 2); ?></td>
                                <td class="text-right"><strong>₹<?php echo number_format($t['total_amount'], 2); ?></strong></td>
                                <td><?php echo escape_html($t['notes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No purchases found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
