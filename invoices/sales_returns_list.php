<?php
/**
 * ============================================================================
 * SALES RETURN HISTORY
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();
$page_title = "Sales Return History";
require_once '../includes/header.php';

// Fetch Returns
$query = "SELECT sr.*, i.invoice_number, c.customer_name 
          FROM sale_returns sr 
          JOIN invoices i ON sr.invoice_id = i.invoice_id 
          JOIN customers c ON sr.customer_id = c.customer_id 
          ORDER BY sr.return_date DESC, sr.return_id DESC";
$result = db_query($connection, $query);
?>

<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📜</span> Sales Return History
    </h2>
    <div class="page-actions">
        <a href="sales_return.php" class="btn btn-primary">
            <span class="btn-icon">+</span> New Return
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Date</th>
                        <th>Invoice Ref</th>
                        <th>Customer</th>
                        <th class="text-right">Amount</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong><?php echo $row['return_number']; ?></strong></td>
                        <td><?php echo format_date($row['return_date']); ?></td>
                        <td>
                            <a href="view_invoice.php?id=<?php echo $row['invoice_id']; ?>">
                                <?php echo $row['invoice_number']; ?>
                            </a>
                        </td>
                        <td><?php echo escape_html($row['customer_name']); ?></td>
                        <td class="text-right">
                            <span style="color:#dc2626; font-weight:bold;">
                                -<?php echo format_currency($row['total_amount']); ?>
                            </span>
                        </td>
                        <td><?php echo escape_html($row['reason']); ?></td>
                        <td class="actions">
                            <!-- View Details Button (could link to a print view) -->
                            <a href="view_return.php?id=<?php echo $row['return_id']; ?>" class="btn-action btn-edit" title="View Details">
                                👁️
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
