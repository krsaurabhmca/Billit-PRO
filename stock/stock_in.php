<?php
/**
 * ============================================================================
 * STOCK IN PAGE
 * ============================================================================
 * Purpose: Add stock to inventory (increase quantity)
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Stock In";

// Include header
require_once '../includes/header.php';

// ============================================================================
// PROCESS FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $product_id = sanitize_sql($connection, $_POST['product_id']);
    $quantity = sanitize_sql($connection, $_POST['quantity']);
    $unit_price = sanitize_sql($connection, $_POST['unit_price']);
    $reference_number = sanitize_sql($connection, $_POST['reference_number']);
    $notes = sanitize_sql($connection, $_POST['notes']);
    $transaction_date = sanitize_sql($connection, $_POST['transaction_date']);
    
    // Initialize error flag
    $has_error = false;
    
    // Validate inputs
    if (empty($product_id)) {
        set_error_message("Please select a product.");
        $has_error = true;
    }
    
    if (!validate_numeric($quantity) || $quantity <= 0) {
        set_error_message("Valid quantity is required (must be greater than 0).");
        $has_error = true;
    }
    
    if (!validate_numeric($unit_price) || $unit_price < 0) {
        set_error_message("Valid unit price is required.");
        $has_error = true;
    }
    
    // If no errors, process stock in
    if (!$has_error) {
        // Calculate total amount
        $total_amount = $quantity * $unit_price;
        
        // Start transaction
        mysqli_begin_transaction($connection);
        
        try {
            // Insert stock transaction record
            $transaction_query = "INSERT INTO stock_transactions 
                                 (product_id, transaction_type, quantity, unit_price, total_amount, 
                                  reference_number, notes, transaction_date, created_by) 
                                 VALUES 
                                 ('{$product_id}', 'stock_in', '{$quantity}', '{$unit_price}', 
                                  '{$total_amount}', '{$reference_number}', '{$notes}', 
                                  '{$transaction_date}', '{$_SESSION['user_id']}')";
            
            if (!db_execute($connection, $transaction_query)) {
                throw new Exception("Failed to create transaction record.");
            }
            
            // Update product stock quantity
            $update_stock_query = "UPDATE products 
                                  SET quantity_in_stock = quantity_in_stock + {$quantity}
                                  WHERE product_id = '{$product_id}'";
            
            if (!db_execute($connection, $update_stock_query)) {
                throw new Exception("Failed to update product stock.");
            }
            
            // Commit transaction
            mysqli_commit($connection);
            
            set_success_message("Stock has been successfully added. Quantity: {$quantity}");
            redirect($_SERVER['PHP_SELF']);
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($connection);
            set_error_message("Failed to add stock: " . $e->getMessage());
        }
    }
}

// ============================================================================
// FETCH PRODUCTS FOR DROPDOWN
// ============================================================================

$products_query = "SELECT product_id, product_code, product_name, unit_price, quantity_in_stock, unit_of_measure
                   FROM products 
                   WHERE status = 'active' 
                   ORDER BY product_name";
$products_result = db_query($connection, $products_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📥</span>
        Stock In (Add Stock)
    </h2>
    <div class="page-actions">
        <a href="stock_history.php" class="btn btn-secondary">
            <span class="btn-icon">📊</span>
            View History
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- STOCK IN FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Stock to Inventory</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="stockInForm">
            <div class="form-row">
                <!-- Product Selection -->
                <div class="form-group col-md-6">
                    <label for="product_id" class="form-label">Select Product *</label>
                    <select id="product_id" name="product_id" class="form-control" required onchange="updateProductInfo()">
                        <option value="">-- Select Product --</option>
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                            <option value="<?php echo $product['product_id']; ?>" 
                                    data-price="<?php echo $product['unit_price']; ?>"
                                    data-stock="<?php echo $product['quantity_in_stock']; ?>"
                                    data-unit="<?php echo $product['unit_of_measure']; ?>">
                                <?php echo escape_html($product['product_code'] . ' - ' . $product['product_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Current Stock Display -->
                <div class="form-group col-md-6">
                    <label class="form-label">Current Stock</label>
                    <input 
                        type="text" 
                        id="current_stock_display" 
                        class="form-control" 
                        value="Select a product first"
                        readonly
                        style="background-color: #f5f5f5;"
                    >
                </div>
            </div>
            
            <div class="form-row">
                <!-- Quantity -->
                <div class="form-group col-md-4">
                    <label for="quantity" class="form-label">Quantity to Add *</label>
                    <input 
                        type="number" 
                        id="quantity" 
                        name="quantity" 
                        class="form-control" 
                        min="1"
                        placeholder="Enter quantity"
                        required
                        oninput="calculateTotal()"
                    >
                </div>
                
                <!-- Unit Price -->
                <div class="form-group col-md-4">
                    <label for="unit_price" class="form-label">Unit Price (₹) *</label>
                    <input 
                        type="number" 
                        id="unit_price" 
                        name="unit_price" 
                        class="form-control" 
                        step="0.01" 
                        min="0"
                        placeholder="0.00"
                        required
                        oninput="calculateTotal()"
                    >
                </div>
                
                <!-- Total Amount -->
                <div class="form-group col-md-4">
                    <label for="total_amount_display" class="form-label">Total Amount (₹)</label>
                    <input 
                        type="text" 
                        id="total_amount_display" 
                        class="form-control" 
                        value="0.00"
                        readonly
                        style="background-color: #f5f5f5; font-weight: bold;"
                    >
                </div>
            </div>
            
            <div class="form-row">
                <!-- Reference Number -->
                <div class="form-group col-md-6">
                    <label for="reference_number" class="form-label">Reference Number (PO/Invoice)</label>
                    <input 
                        type="text" 
                        id="reference_number" 
                        name="reference_number" 
                        class="form-control" 
                        placeholder="e.g., PO-2026-001"
                    >
                </div>
                
                <!-- Transaction Date -->
                <div class="form-group col-md-6">
                    <label for="transaction_date" class="form-label">Transaction Date *</label>
                    <input 
                        type="datetime-local" 
                        id="transaction_date" 
                        name="transaction_date" 
                        class="form-control" 
                        value="<?php echo date('Y-m-d\TH:i'); ?>"
                        required
                    >
                </div>
            </div>
            
            <!-- Notes -->
            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    class="form-control" 
                    rows="3"
                    placeholder="Enter any additional notes or comments"
                ></textarea>
            </div>
            
            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    Add Stock
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <span class="btn-icon">↺</span>
                    Reset Form
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/**
 * Update product information when product is selected
 */
function updateProductInfo() {
    const select = document.getElementById('product_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const currentStock = selectedOption.getAttribute('data-stock');
        const unit = selectedOption.getAttribute('data-unit');
        const price = selectedOption.getAttribute('data-price');
        
        document.getElementById('current_stock_display').value = currentStock + ' ' + unit;
        document.getElementById('unit_price').value = price;
        calculateTotal();
    } else {
        document.getElementById('current_stock_display').value = 'Select a product first';
        document.getElementById('unit_price').value = '';
        document.getElementById('total_amount_display').value = '0.00';
    }
}

/**
 * Calculate total amount
 */
function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = quantity * unitPrice;
    
    document.getElementById('total_amount_display').value = total.toFixed(2);
}

/**
 * Reset form
 */
function resetForm() {
    document.getElementById('current_stock_display').value = 'Select a product first';
    document.getElementById('total_amount_display').value = '0.00';
}
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
