<?php
/**
 * ============================================================================
 * PURCHASE LIST PAGE
 * ============================================================================
 * Purpose: View all purchases
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

$page_title = "Purchase Management";
require_once '../includes/header.php';

$query = "SELECT p.*, s.supplier_name, u.full_name 
          FROM purchases p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          LEFT JOIN users u ON p.created_by = u.user_id 
          ORDER BY p.purchase_date DESC";
$purchases = db_fetch_all($connection, $query);
?>

<div class="page-header">
    <div>
        <h2 class="page-title"><span class="page-icon">📥</span> Purchases</h2>
    </div>
    <div class="page-actions">
        <a href="create_purchase.php" class="btn btn-primary">
            <span class="btn-icon">➕</span> Record Purchase
        </a>
    </div>
</div>

<?php echo display_messages(); ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Invoice No</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $p): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($p['purchase_date'])); ?></td>
                        <td><?php echo escape_html($p['supplier_name']); ?></td>
                        <td><?php echo escape_html($p['supplier_invoice_no']); ?></td>
                        <td>₹<?php echo number_format($p['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $p['status'] == 'received' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($p['status']); ?>
                            </span>
                        </td>
                        <td><small><?php echo escape_html($p['full_name']); ?></small></td>
                        <td>
                            <!-- View Detail Functionality -->
                            <a href="view_purchase.php?id=<?php echo $p['purchase_id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
