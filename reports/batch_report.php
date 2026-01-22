<?php
/**
 * ============================================================================
 * BATCH / EXPIRY REPORT
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();
$page_title = "Batch & Expiry Report";
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">💊</span> Batch & Expiry Tracking
    </h2>
    <div class="page-actions">
        <!-- Export Button -->
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch No</th>
                        <th>Mfg Date</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th class="text-right">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT pb.*, p.product_name, p.unit_of_measure 
                              FROM product_batches pb 
                              JOIN products p ON pb.product_id = p.product_id 
                              WHERE pb.quantity_remaining > 0 
                              ORDER BY pb.expiry_date ASC";
                    $result = db_query($connection, $query);
                    
                    while($row = mysqli_fetch_assoc($result)):
                        $days_left = ceil((strtotime($row['expiry_date']) - time()) / 86400);
                        $status_class = 'success';
                        $status_text = $days_left . ' Days Left';
                        
                        if ($days_left < 0) {
                            $status_class = 'danger';
                            $status_text = 'EXPIRED';
                        } elseif ($days_left < 30) {
                            $status_class = 'warning';
                            $status_text = 'Expiring Soon (' . $days_left . ' days)';
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo escape_html($row['product_name']); ?></strong>
                        </td>
                        <td><?php echo escape_html($row['batch_no']); ?></td>
                        <td><?php echo format_date($row['mfg_date']); ?></td>
                        <td>
                             <span style="font-weight:600;"><?php echo format_date($row['expiry_date']); ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <strong><?php echo $row['quantity_remaining']; ?></strong> <?php echo $row['unit_of_measure']; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
