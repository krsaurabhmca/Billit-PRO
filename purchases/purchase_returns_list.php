<?php
/**
 * ============================================================================
 * PURCHASE RETURN HISTORY
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();
$page_title = "Purchase Return History";
require_once '../includes/header.php';

// Fetch Returns
$query = "SELECT pr.*, s.supplier_name, p.supplier_invoice_no 
          FROM purchase_returns pr 
          JOIN suppliers s ON pr.supplier_id = s.supplier_id 
          LEFT JOIN purchases p ON pr.purchase_id = p.purchase_id 
          ORDER BY pr.return_date DESC, pr.return_id DESC";
$result = db_query($connection, $query);
?>

<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📜</span> Purchase Return History
    </h2>
    <div class="page-actions">
        <a href="purchase_return.php" class="btn btn-primary">
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
                        <th>Purchase Ref</th>
                        <th>Supplier</th>
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
                            <?php echo $row['supplier_invoice_no'] ? $row['supplier_invoice_no'] : 'N/A'; ?>
                        </td>
                        <td><?php echo escape_html($row['supplier_name']); ?></td>
                        <td class="text-right">
                            <span style="color:#dc2626; font-weight:bold;">
                                -<?php echo format_currency($row['total_amount']); ?>
                            </span>
                        </td>
                        <td><?php echo escape_html($row['reason']); ?></td>
                        <td class="actions">
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
