<?php
/**
 * ============================================================================
 * ADD PAYMENT PAGE
 * ============================================================================
 * Purpose: Record payment against invoice
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Add Payment";

// Include header
require_once '../includes/header.php';

// ============================================================================
// GET INVOICE ID
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid invoice ID.");
    redirect('invoices.php');
}

$invoice_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// FETCH INVOICE DATA
// ============================================================================

$invoice_query = "SELECT i.*, c.customer_name 
                  FROM invoices i
                  INNER JOIN customers c ON i.customer_id = c.customer_id
                  WHERE i.invoice_id = '{$invoice_id}' LIMIT 1";
$invoice = db_fetch_one($connection, $invoice_query);

if (!$invoice) {
    set_error_message("Invoice not found.");
    redirect('invoices.php');
}

// Check if invoice is finalized
if ($invoice['invoice_status'] !== 'finalized') {
    set_error_message("Payments can only be added to finalized invoices.");
    redirect('invoices.php');
}

// ============================================================================
// PROCESS FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_date = sanitize_sql($connection, $_POST['payment_date']);
    $payment_method = sanitize_sql($connection, $_POST['payment_method']);
    $payment_amount = sanitize_sql($connection, $_POST['payment_amount']);
    $reference_number = sanitize_sql($connection, $_POST['reference_number']);
    $notes = sanitize_sql($connection, $_POST['notes']);
    
    $has_error = false;
    
    // Validate
    if (!validate_numeric($payment_amount) || $payment_amount <= 0) {
        set_error_message("Valid payment amount is required.");
        $has_error = true;
    }
    
    if ($payment_amount > $invoice['amount_due']) {
        set_error_message("Payment amount cannot exceed due amount of " . format_currency($invoice['amount_due']));
        $has_error = true;
    }
    
    if (!$has_error) {
        // Start transaction
        mysqli_begin_transaction($connection);
        
        try {
            // Insert payment record
            $insert_payment = "INSERT INTO payments 
                              (invoice_id, payment_date, payment_method, payment_amount, 
                               reference_number, notes, created_by)
                              VALUES
                              ('{$invoice_id}', '{$payment_date}', '{$payment_method}', '{$payment_amount}',
                               '{$reference_number}', '{$notes}', '{$_SESSION['user_id']}')";
            
            if (!db_execute($connection, $insert_payment)) {
                throw new Exception("Failed to record payment.");
            }
            
            // Update invoice payment status
            $new_amount_paid = $invoice['amount_paid'] + $payment_amount;
            $new_amount_due = $invoice['amount_due'] - $payment_amount;
            
            // Determine payment status
            if ($new_amount_due <= 0) {
                $payment_status = 'paid';
            } elseif ($new_amount_paid > 0) {
                $payment_status = 'partial';
            } else {
                $payment_status = 'unpaid';
            }
            
            $update_invoice = "UPDATE invoices SET
                              amount_paid = '{$new_amount_paid}',
                              amount_due = '{$new_amount_due}',
                              payment_status = '{$payment_status}'
                              WHERE invoice_id = '{$invoice_id}'";
            
            if (!db_execute($connection, $update_invoice)) {
                throw new Exception("Failed to update invoice.");
            }
            
            // Commit transaction
            mysqli_commit($connection);
            
            set_success_message("Payment of " . format_currency($payment_amount) . " recorded successfully!");
            redirect("view_invoice.php?id={$invoice_id}");
            
        } catch (Exception $e) {
            mysqli_rollback($connection);
            set_error_message("Failed to record payment: " . $e->getMessage());
        }
    }
}

// ============================================================================
// FETCH EXISTING PAYMENTS
// ============================================================================

$payments_query = "SELECT p.*, u.username 
                   FROM payments p
                   LEFT JOIN users u ON p.created_by = u.user_id
                   WHERE p.invoice_id = '{$invoice_id}'
                   ORDER BY p.payment_date DESC, p.payment_id DESC";
$payments_result = db_query($connection, $payments_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">💰</span>
        Add Payment
    </h2>
    <div class="page-actions">
        <a href="view_invoice.php?id=<?php echo $invoice_id; ?>" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Back to Invoice
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- INVOICE SUMMARY -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoice Summary</h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="form-label">Invoice Number</label>
                <p><strong><?php echo escape_html($invoice['invoice_number']); ?></strong></p>
            </div>
            
            <div class="form-group col-md-3">
                <label class="form-label">Customer</label>
                <p><strong><?php echo escape_html($invoice['customer_name']); ?></strong></p>
            </div>
            
            <div class="form-group col-md-2">
                <label class="form-label">Total Amount</label>
                <p><strong><?php echo format_currency($invoice['total_amount']); ?></strong></p>
            </div>
            
            <div class="form-group col-md-2">
                <label class="form-label">Paid</label>
                <p style="color: #10b981;"><strong><?php echo format_currency($invoice['amount_paid']); ?></strong></p>
            </div>
            
            <div class="form-group col-md-2">
                <label class="form-label">Due</label>
                <p style="color: #ef4444;"><strong><?php echo format_currency($invoice['amount_due']); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- ADD PAYMENT FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Record Payment</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $invoice_id; ?>">
            <div class="form-row">
                <!-- Payment Date -->
                <div class="form-group col-md-3">
                    <label for="payment_date" class="form-label">Payment Date *</label>
                    <input type="date" id="payment_date" name="payment_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <!-- Payment Method -->
                <div class="form-group col-md-3">
                    <label for="payment_method" class="form-label">Payment Method *</label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <!-- Payment Amount -->
                <div class="form-group col-md-3">
                    <label for="payment_amount" class="form-label">Payment Amount (₹) *</label>
                    <input type="number" id="payment_amount" name="payment_amount" class="form-control" 
                           step="0.01" min="0.01" max="<?php echo $invoice['amount_due']; ?>" 
                           value="<?php echo $invoice['amount_due']; ?>" required>
                    <small class="form-text">Max: <?php echo format_currency($invoice['amount_due']); ?></small>
                </div>
                
                <!-- Reference Number -->
                <div class="form-group col-md-3">
                    <label for="reference_number" class="form-label">Reference Number</label>
                    <input type="text" id="reference_number" name="reference_number" class="form-control" 
                           placeholder="e.g., TXN123456">
                </div>
            </div>
            
            <!-- Notes -->
            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="2" 
                          placeholder="Any additional notes about this payment..."></textarea>
            </div>
            
            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    Record Payment
                </button>
                <a href="view_invoice.php?id=<?php echo $invoice_id; ?>" class="btn btn-secondary">
                    <span class="btn-icon">✕</span>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- PAYMENT HISTORY -->
<!-- ================================================================ -->
<?php if (mysqli_num_rows($payments_result) > 0): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Payment History</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                        <tr>
                            <td><?php echo format_date($payment['payment_date']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                </span>
                            </td>
                            <td><strong><?php echo format_currency($payment['payment_amount']); ?></strong></td>
                            <td><?php echo escape_html($payment['reference_number']); ?></td>
                            <td><?php echo escape_html($payment['notes']); ?></td>
                            <td><?php echo escape_html($payment['username'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
require_once '../includes/footer.php';
?>
