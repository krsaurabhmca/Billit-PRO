<?php
/**
 * ============================================================================
 * CREATE INVOICE PAGE
 * ============================================================================
 * Purpose: Create new invoice with GST calculation and stock management
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Create Invoice";

// Include header
require_once '../includes/header.php';

// ============================================================================
// PROCESS FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $customer_id = sanitize_sql($connection, $_POST['customer_id']);
    $invoice_date = sanitize_sql($connection, $_POST['invoice_date']);
    $discount_type = sanitize_sql($connection, $_POST['discount_type']);
    $discount_value = sanitize_sql($connection, $_POST['discount_value']);
    $notes = sanitize_sql($connection, $_POST['notes']);
    $invoice_status = sanitize_sql($connection, $_POST['invoice_status']);
    
    // Get products data (JSON from hidden field)
    $products_json = $_POST['products_data'];
    $products = json_decode($products_json, true);
    
    $has_error = false;
    
    // Validate
    if (empty($customer_id)) {
        set_error_message("Please select a customer.");
        $has_error = true;
    }
    
    if (empty($products) || count($products) == 0) {
        set_error_message("Please add at least one product.");
        $has_error = true;
    }
    
    if (!$has_error) {
        // Fetch customer details
        $customer_query = "SELECT * FROM customers WHERE customer_id = '{$customer_id}' LIMIT 1";
        $customer = db_fetch_one($connection, $customer_query);
        
        // Fetch company settings
        $company_query = "SELECT * FROM company_settings LIMIT 1";
        $company = db_fetch_one($connection, $company_query);
        
        if (!$company) {
            set_error_message("Please configure company settings first (Admin → Company Settings).");
        } else {
            // Generate invoice number
            $invoice_number = generate_invoice_number($connection, $company['invoice_prefix'] ?? 'INV');
        
            // Calculate totals
            $subtotal = 0;
            $total_cgst = 0;
            $total_sgst = 0;
            $total_igst = 0;
            
            foreach ($products as $product) {
                $subtotal += $product['quantity'] * $product['unit_price'];
            }
            
            // Calculate discount
            if ($discount_type === 'percentage') {
                $discount_amount = ($subtotal * $discount_value) / 100;
            } else {
                $discount_amount = $discount_value;
            }
            
            $taxable_amount = $subtotal - $discount_amount;
            
            // Calculate GST for each product and sum up
            foreach ($products as $product) {
                $item_taxable = ($product['quantity'] * $product['unit_price']) * ($taxable_amount / $subtotal);
                
                $gst_calc = calculate_gst(
                    $item_taxable, 
                    $product['gst_rate'], 
                    $company['company_state_code'] ?? '27', 
                    $customer['billing_state_code'] ?? '27'
                );
                
                $total_cgst += $gst_calc['cgst_amount'];
                $total_sgst += $gst_calc['sgst_amount'];
                $total_igst += $gst_calc['igst_amount'];
            }
            
            $total_tax = $total_cgst + $total_sgst + $total_igst;
            $total_amount = $taxable_amount + $total_tax;
            $rounded_total = round($total_amount);
            $round_off = $rounded_total - $total_amount;
            
            // Start transaction
            mysqli_begin_transaction($connection);
            
            try {
                // Insert invoice
                $insert_invoice = "INSERT INTO invoices 
                                  (invoice_number, invoice_date, customer_id, customer_name, customer_gstin,
                                   customer_address, customer_state_code, subtotal, discount_type, discount_value,
                                   discount_amount, taxable_amount, cgst_amount, sgst_amount, igst_amount,
                                   total_tax, round_off, total_amount, amount_due, payment_status, invoice_status,
                                   notes, terms_conditions, created_by)
                                  VALUES
                                  ('{$invoice_number}', '{$invoice_date}', '{$customer_id}', '{$customer['customer_name']}',
                                   '{$customer['gstin']}', '{$customer['billing_address']}', '{$customer['billing_state_code']}',
                                   '{$subtotal}', '{$discount_type}', '{$discount_value}', '{$discount_amount}',
                                   '{$taxable_amount}', '{$total_cgst}', '{$total_sgst}', '{$total_igst}',
                                   '{$total_tax}', '{$round_off}', '{$rounded_total}', '{$rounded_total}',
                                   'unpaid', '{$invoice_status}', '{$notes}', '{$company['terms_conditions']}',
                                   '{$_SESSION['user_id']}')";
                
                if (!db_execute($connection, $insert_invoice)) {
                    throw new Exception("Failed to create invoice.");
                }
                
                $invoice_id = mysqli_insert_id($connection);
                
                // Insert invoice items and update stock
                foreach ($products as $product) {
                    $item_total = $product['quantity'] * $product['unit_price'];
                    $item_taxable = $item_total * ($taxable_amount / $subtotal);
                    
                    $gst_calc = calculate_gst(
                        $item_taxable,
                        $product['gst_rate'],
                        $company['company_state_code'] ?? '27',
                        $customer['billing_state_code'] ?? '27'
                    );
                    
                    $item_total_with_tax = $item_taxable + $gst_calc['total_tax'];
                    
                    // Insert item
                    $insert_item = "INSERT INTO invoice_items
                                   (invoice_id, product_id, product_name, product_code, hsn_code,
                                    quantity, unit_of_measure, unit_price, item_total, taxable_amount,
                                    gst_rate, cgst_rate, cgst_amount, sgst_rate, sgst_amount,
                                    igst_rate, igst_amount, total_amount)
                                   VALUES
                                   ('{$invoice_id}', '{$product['product_id']}', '{$product['product_name']}',
                                    '{$product['product_code']}', '{$product['hsn_code']}', '{$product['quantity']}',
                                    '{$product['unit_of_measure']}', '{$product['unit_price']}', '{$item_total}',
                                    '{$item_taxable}', '{$product['gst_rate']}', '{$gst_calc['cgst_rate']}',
                                    '{$gst_calc['cgst_amount']}', '{$gst_calc['sgst_rate']}', '{$gst_calc['sgst_amount']}',
                                    '{$gst_calc['igst_rate']}', '{$gst_calc['igst_amount']}', '{$item_total_with_tax}')";
                    
                    if (!db_execute($connection, $insert_item)) {
                        throw new Exception("Failed to add invoice item.");
                    }
                    
                    // Update stock if invoice is finalized
                    if ($invoice_status === 'finalized') {
                        $update_stock = "UPDATE products 
                                        SET quantity_in_stock = quantity_in_stock - {$product['quantity']}
                                        WHERE product_id = '{$product['product_id']}'";
                        
                        if (!db_execute($connection, $update_stock)) {
                            throw new Exception("Failed to update stock.");
                        }
                    }
                }
                
                // Commit transaction
                mysqli_commit($connection);
                
                set_success_message("Invoice {$invoice_number} created successfully!");
                redirect("view_invoice.php?id={$invoice_id}");
                
            } catch (Exception $e) {
                mysqli_rollback($connection);
                set_error_message("Failed to create invoice: " . $e->getMessage());
            }
        }
    }
}

// ============================================================================
// FETCH DATA FOR FORM
// ============================================================================

// Fetch customers
$customers_query = "SELECT customer_id, customer_name, customer_type, billing_state_code 
                    FROM customers WHERE status = 'active' ORDER BY customer_name";
$customers_result = db_query($connection, $customers_query);

// Fetch products
$products_query = "SELECT product_id, product_code, product_name, hsn_code, unit_price, 
                   gst_rate, quantity_in_stock, unit_of_measure
                   FROM products WHERE status = 'active' ORDER BY product_name";
$products_result = db_query($connection, $products_query);

// Fetch company settings
$company_query = "SELECT company_state_code FROM company_settings LIMIT 1";
$company = db_fetch_one($connection, $company_query);
$company_state = $company ? ($company['company_state_code'] ?? '27') : '27';
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">➕</span>
        Create New Invoice
    </h2>
    <div class="page-actions">
        <a href="invoices.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Back to Invoices
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- CREATE INVOICE FORM -->
<!-- ================================================================ -->
<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="invoiceForm">
    <input type="hidden" name="products_data" id="products_data" value="[]">
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Invoice Details</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <!-- Customer Selection -->
                <div class="form-group col-md-4">
                    <label for="customer_id" class="form-label">Select Customer *</label>
                    <select id="customer_id" name="customer_id" class="form-control form-select" required onchange="updateCustomerState()">
                        <option value="">-- Select Customer --</option>
                        <?php while ($customer = mysqli_fetch_assoc($customers_result)): ?>
                            <option value="<?php echo $customer['customer_id']; ?>" 
                                    data-state="<?php echo $customer['billing_state_code']; ?>">
                                <?php echo escape_html($customer['customer_name']); ?> 
                                (<?php echo $customer['customer_type']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Invoice Date -->
                <div class="form-group col-md-4">
                    <label for="invoice_date" class="form-label">Invoice Date *</label>
                    <input type="date" id="invoice_date" name="invoice_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Selection -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Products</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="product_select" class="form-label">Select Product</label>
                    <select id="product_select" class="form-control">
                        <option value="">-- Select Product --</option>
                        <?php 
                        mysqli_data_seek($products_result, 0);
                        while ($product = mysqli_fetch_assoc($products_result)): 
                        ?>
                            <option value="<?php echo $product['product_id']; ?>"
                                    data-code="<?php echo escape_html($product['product_code']); ?>"
                                    data-name="<?php echo escape_html($product['product_name']); ?>"
                                    data-hsn="<?php echo escape_html($product['hsn_code']); ?>"
                                    data-price="<?php echo $product['unit_price']; ?>"
                                    data-gst="<?php echo $product['gst_rate']; ?>"
                                    data-stock="<?php echo $product['quantity_in_stock']; ?>"
                                    data-unit="<?php echo escape_html($product['unit_of_measure']); ?>">
                                <?php echo escape_html($product['product_code'] . ' - ' . $product['product_name']); ?>
                                (Stock: <?php echo $product['quantity_in_stock']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group col-md-2">
                    <label for="product_qty" class="form-label">Quantity</label>
                    <input type="number" id="product_qty" class="form-control" min="1" value="1">
                </div>
                
                <div class="form-group col-md-2">
                    <label for="product_price" class="form-label">Unit Price</label>
                    <input type="number" id="product_price" class="form-control" step="0.01" min="0" readonly>
                </div>
                
                <div class="form-group col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" onclick="addProduct()">
                        <span class="btn-icon">+</span> Add
                    </button>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="data-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>HSN</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>GST%</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center">No products added yet</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Invoice Summary -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Invoice Summary</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <!-- Discount -->
                <div class="form-group col-md-3">
                    <label for="discount_type" class="form-label">Discount Type</label>
                    <select id="discount_type" name="discount_type" class="form-control" onchange="calculateTotals()">
                        <option value="percentage">Percentage (%)</option>
                        <option value="amount">Amount (₹)</option>
                    </select>
                </div>
                
                <div class="form-group col-md-3">
                    <label for="discount_value" class="form-label">Discount Value</label>
                    <input type="number" id="discount_value" name="discount_value" class="form-control" 
                           step="0.01" min="0" value="0" onchange="calculateTotals()">
                </div>
                
                <!-- Summary Display -->
                <div class="form-group col-md-6">
                    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
                        <table style="width: 100%; font-size: 14px;">
                            <tr>
                                <td>Subtotal:</td>
                                <td style="text-align: right;"><strong id="display_subtotal">₹0.00</strong></td>
                            </tr>
                            <tr>
                                <td>Discount:</td>
                                <td style="text-align: right; color: #ef4444;"><strong id="display_discount">₹0.00</strong></td>
                            </tr>
                            <tr>
                                <td>Taxable:</td>
                                <td style="text-align: right;"><strong id="display_taxable">₹0.00</strong></td>
                            </tr>
                            <tr>
                                <td><span id="gst_label">GST:</span></td>
                                <td style="text-align: right;"><strong id="display_gst">₹0.00</strong></td>
                            </tr>
                            <tr style="border-top: 2px solid #667eea; font-size: 16px;">
                                <td style="padding-top: 5px;">Total:</td>
                                <td style="text-align: right; padding-top: 5px; color: #667eea;">
                                    <strong id="display_total">₹0.00</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Notes and Status -->
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2" 
                              placeholder="Any additional notes..."></textarea>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="invoice_status" class="form-label">Invoice Status *</label>
                    <select id="invoice_status" name="invoice_status" class="form-control" required>
                        <option value="draft">Save as Draft</option>
                        <option value="finalized">Finalize (Deduct Stock)</option>
                    </select>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span class="btn-icon">✓</span>
                    Create Invoice
                </button>
                <a href="invoices.php" class="btn btn-secondary">
                    <span class="btn-icon">✕</span>
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
// Global variables
let products = [];
let customerState = '';
const companyState = '<?php echo $company_state; ?>';

// Update customer state when customer is selected
function updateCustomerState() {
    const select = document.getElementById('customer_id');
    const selectedOption = select.options[select.selectedIndex];
    customerState = selectedOption.getAttribute('data-state') || '';
    calculateTotals();
}

// Update product price when product is selected
document.getElementById('product_select').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('product_price').value = selectedOption.getAttribute('data-price');
    } else {
        document.getElementById('product_price').value = '';
    }
});

// Add product to list
function addProduct() {
    const select = document.getElementById('product_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) {
        alert('Please select a product');
        return;
    }
    
    const qty = parseFloat(document.getElementById('product_qty').value);
    const stock = parseFloat(selectedOption.getAttribute('data-stock'));
    
    if (qty <= 0) {
        alert('Quantity must be greater than 0');
        return;
    }
    
    if (qty > stock) {
        alert('Insufficient stock! Available: ' + stock);
        return;
    }
    
    const product = {
        product_id: selectedOption.value,
        product_code: selectedOption.getAttribute('data-code'),
        product_name: selectedOption.getAttribute('data-name'),
        hsn_code: selectedOption.getAttribute('data-hsn'),
        unit_price: parseFloat(selectedOption.getAttribute('data-price')),
        gst_rate: parseFloat(selectedOption.getAttribute('data-gst')),
        quantity: qty,
        unit_of_measure: selectedOption.getAttribute('data-unit')
    };
    
    products.push(product);
    updateProductsTable();
    calculateTotals();
    
    // Reset form
    document.getElementById('product_select').value = '';
    document.getElementById('product_qty').value = 1;
    document.getElementById('product_price').value = '';
}

// Remove product from list
function removeProduct(index) {
    products.splice(index, 1);
    updateProductsTable();
    calculateTotals();
}

// Update products table
function updateProductsTable() {
    const tbody = document.getElementById('productsTableBody');
    const emptyRow = document.getElementById('emptyRow');
    
    if (products.length === 0) {
        emptyRow.style.display = 'table-row';
        return;
    }
    
    emptyRow.style.display = 'none';
    
    let html = '';
    products.forEach((product, index) => {
        const amount = product.quantity * product.unit_price;
        html += `
            <tr>
                <td><strong>${product.product_name}</strong><br><small>${product.product_code}</small></td>
                <td>${product.hsn_code}</td>
                <td>${product.quantity} ${product.unit_of_measure}</td>
                <td>₹${product.unit_price.toFixed(2)}</td>
                <td>₹${amount.toFixed(2)}</td>
                <td>${product.gst_rate}%</td>
                <td>
                    <button type="button" class="btn-action btn-delete" onclick="removeProduct(${index})">🗑️</button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html + emptyRow.outerHTML;
}

// Calculate totals
function calculateTotals() {
    if (products.length === 0) {
        document.getElementById('display_subtotal').textContent = '₹0.00';
        document.getElementById('display_discount').textContent = '₹0.00';
        document.getElementById('display_taxable').textContent = '₹0.00';
        document.getElementById('display_gst').textContent = '₹0.00';
        document.getElementById('display_total').textContent = '₹0.00';
        return;
    }
    
    // Calculate subtotal
    let subtotal = 0;
    products.forEach(product => {
        subtotal += product.quantity * product.unit_price;
    });
    
    // Calculate discount
    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
    let discountAmount = 0;
    
    if (discountType === 'percentage') {
        discountAmount = (subtotal * discountValue) / 100;
    } else {
        discountAmount = discountValue;
    }
    
    const taxableAmount = subtotal - discountAmount;
    
    // Calculate GST
    let totalGST = 0;
    let gstLabel = 'GST';
    
    products.forEach(product => {
        const itemTaxable = (product.quantity * product.unit_price) * (taxableAmount / subtotal);
        const gstAmount = (itemTaxable * product.gst_rate) / 100;
        totalGST += gstAmount;
    });
    
    // Determine GST label
    if (customerState && companyState) {
        if (customerState === companyState) {
            gstLabel = 'GST (CGST + SGST)';
        } else {
            gstLabel = 'GST (IGST)';
        }
    }
    
    const total = taxableAmount + totalGST;
    const roundedTotal = Math.round(total);
    
    // Update display
    document.getElementById('display_subtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('display_discount').textContent = '₹' + discountAmount.toFixed(2);
    document.getElementById('display_taxable').textContent = '₹' + taxableAmount.toFixed(2);
    document.getElementById('display_gst').textContent = '₹' + totalGST.toFixed(2);
    document.getElementById('display_total').textContent = '₹' + roundedTotal.toFixed(2);
    document.getElementById('gst_label').textContent = gstLabel + ':';
    
    // Update hidden field
    document.getElementById('products_data').value = JSON.stringify(products);
}

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    if (products.length === 0) {
        e.preventDefault();
        alert('Please add at least one product to the invoice');
        return false;
    }
    
    if (!document.getElementById('customer_id').value) {
        e.preventDefault();
        alert('Please select a customer');
        return false;
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
