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
            if (!empty($company['company_gstin'])) {
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
                    
                    // If no company GSTIN, force tax to zero
                    if (empty($company['company_gstin'])) {
                        $gst_calc['cgst_amount'] = 0;
                        $gst_calc['sgst_amount'] = 0;
                        $gst_calc['igst_amount'] = 0;
                        $gst_calc['total_tax'] = 0;
                    }
                    
                    $item_total_with_tax = $item_taxable + $gst_calc['total_tax'];
                    
                    // Insert item
                    // Prepare Tracking Data
                    $batch_id = (isset($product['batch_id']) && !empty($product['batch_id'])) ? "'{$product['batch_id']}'" : "NULL";
                    
                    $serial_ids_str = "NULL";
                    $serial_arr = [];
                    if (isset($product['serial_ids']) && !empty($product['serial_ids'])) {
                         $serial_arr = is_array($product['serial_ids']) ? $product['serial_ids'] : explode(',', $product['serial_ids']);
                         
                         // Validation: Quantity Mismatch
                         if (count($serial_arr) != $product['quantity']) {
                             throw new Exception("Serial count mismatch for {$product['product_name']}. Expected {$product['quantity']}, got " . count($serial_arr));
                         }
                         
                         $serial_ids_str = "'" . implode(',', $serial_arr) . "'";
                    }
                    
                    // Validation: Batch Check
                    if (isset($product['tracking']) && $product['tracking'] === 'batch' && $batch_id === "NULL") {
                        // POS might skip this, but strict mode should enforce
                         throw new Exception("Batch not selected for {$product['product_name']}");
                    }
                    
                    // Validation: Serial Requirement Check
                    if (isset($product['tracking']) && $product['tracking'] === 'serial' && empty($serial_arr)) {
                         throw new Exception("Serial numbers missing for {$product['product_name']}");
                    }
                    
                    // Insert item with tracking info
                    $hsn_val = isset($product['hsn_code']) ? $product['hsn_code'] : '';
                    $insert_item = "INSERT INTO invoice_items
                                   (invoice_id, product_id, batch_id, serial_ids, product_name, product_code, hsn_code,
                                    quantity, unit_of_measure, unit_price, item_total, taxable_amount,
                                    gst_rate, cgst_rate, cgst_amount, sgst_rate, sgst_amount,
                                    igst_rate, igst_amount, total_amount)
                                   VALUES
                                   ('{$invoice_id}', '{$product['product_id']}', $batch_id, $serial_ids_str, '{$product['product_name']}',
                                    '{$product['product_code']}', '{$hsn_val}', '{$product['quantity']}',
                                    '{$product['unit_of_measure']}', '{$product['unit_price']}', '{$item_total}',
                                    '{$item_taxable}', '{$product['gst_rate']}', '{$gst_calc['cgst_rate']}',
                                    '{$gst_calc['cgst_amount']}', '{$gst_calc['sgst_rate']}', '{$gst_calc['sgst_amount']}',
                                    '{$gst_calc['igst_rate']}', '{$gst_calc['igst_amount']}', '{$item_total_with_tax}')";
                    
                    if (!db_execute($connection, $insert_item)) {
                        throw new Exception("Failed to add invoice item.");
                    }
                    $invoice_item_id = mysqli_insert_id($connection);
                    
                    // Update stock if invoice is finalized
                    if ($invoice_status === 'finalized') {
                        // 1. Update Master Stock
                        $update_stock = "UPDATE products 
                                        SET quantity_in_stock = quantity_in_stock - {$product['quantity']}
                                        WHERE product_id = '{$product['product_id']}'";
                        if (!db_execute($connection, $update_stock)) throw new Exception("Failed to update stock.");
                        
                        // 2. Update Batch Stock
                        if ($batch_id !== "NULL") {
                             $update_batch = "UPDATE product_batches 
                                              SET quantity_remaining = quantity_remaining - {$product['quantity']} 
                                              WHERE batch_id = $batch_id";
                             if (!db_execute($connection, $update_batch)) throw new Exception("Failed to update batch stock.");
                        }
                        
                        // 3. Update Serial Stock
                        if (!empty($serial_arr)) {
                            foreach($serial_arr as $sid) {
                                $sid = sanitize_sql($connection, $sid);
                                $update_serial = "UPDATE product_serials 
                                                  SET status = 'sold', is_sold = 1, date_sold = NOW(), invoice_item_id = '$invoice_item_id' 
                                                  WHERE serial_id = '$sid'";
                                if (!db_execute($connection, $update_serial)) throw new Exception("Failed to update serial status.");
                            }
                        }
                    }
                }
                
                // 4. Process Payments (if any, e.g. from POS)
                $amount_paid = 0;
                $payment_status = 'unpaid';
                
                if (isset($_POST['payment_data']) && !empty($_POST['payment_data'])) {
                    $payments = json_decode($_POST['payment_data'], true);
                    
                    if (is_array($payments) && count($payments) > 0) {
                        foreach ($payments as $pay) {
                            $mode = sanitize_sql($connection, $pay['mode']);
                            $amt = (float)$pay['amount'];
                            
                            if ($amt > 0) {
                                // Create Reference for POS
                                $ref = 'POS-' . time() . '-' . rand(100,999);
                                
                                $pay_query = "INSERT INTO payments (invoice_id, payment_date, payment_amount, payment_method, reference_number, notes)
                                              VALUES ('$invoice_id', NOW(), '$amt', '$mode', '$ref', 'POS Payment')";
                                
                                if (!db_execute($connection, $pay_query)) {
                                    throw new Exception("Failed to record payment.");
                                }
                                $amount_paid += $amt;
                            }
                        }
                    }
                }
                
                // 5. Update Invoice Payment Status
                $amount_due = $rounded_total - $amount_paid;
                if ($amount_due < 0) $amount_due = 0; // Handle overpayment (change given) logic if needed, but DB usually stores 0 due.
                
                if ($amount_paid >= $rounded_total) {
                    $payment_status = 'paid';
                } elseif ($amount_paid > 0) {
                    $payment_status = 'partial';
                }
                
                // If overpaid in POS (change given), we technically recorded full amount? 
                // Usually POS logic: Total 100, Paid 500 (Cash), Change 400. 
                // We should record 100 as paid or 500? 
                // If we record 500, due is -400.
                // Revisit POS JS: `calculateBalance` shows Change.
                // `submitSale` sends the entered amounts.
                // If user enters 500 for Cash, we record 500.
                // But accounting wise, we only "kept" 100.
                // Usually we record the transaction amount (100).
                // But let's stick to simple logic: Record what is passed. 
                // If `amount_paid` > `total`, status is paid.
                // We will clamp `amount_due` at 0.
                
                if ($amount_paid > 0) {
                    $update_inv = "UPDATE invoices SET 
                                   amount_paid = '$amount_paid',
                                   amount_due = '$amount_due',
                                   payment_status = '$payment_status'
                                   WHERE invoice_id = '$invoice_id'";
                    if (!db_execute($connection, $update_inv)) {
                         throw new Exception("Failed to update invoice payment status.");
                    }
                }
                
                // Commit transaction
                mysqli_commit($connection);
                
                // SEND EMAIL IF REQUESTED
                if (isset($_POST['send_email']) && $_POST['send_email'] == '1') {
                    // Fetch customer email
                    $cust_email_query = "SELECT email, customer_name FROM customers WHERE customer_id = '$customer_id'";
                    $cust_data = db_fetch_one($connection, $cust_email_query);
                    
                    if ($cust_data && !empty($cust_data['email'])) {
                        $email_subject = "Invoice #{$invoice_number} from " . APP_NAME;
                        $email_body = "
                        <div style='font-family: Arial, sans-serif;'>
                            <h2>Hello {$cust_data['customer_name']},</h2>
                            <p>Here is your new invoice <strong>#{$invoice_number}</strong> generated on " . date('d-m-Y') . ".</p>
                            <p><strong>Total Amount:</strong> ₹" . number_format($total_amount, 2) . "</p>
                            <p>Please find the details in your dashboard or contact us for a copy.</p>
                            <br>
                            <p>Thank you for your business!</p>
                            <p><strong>" . APP_NAME . "</strong></p>
                        </div>";
                        
                        $email_result = send_email($connection, $cust_data['email'], $email_subject, $email_body);
                        
                        if ($email_result['success']) {
                            log_activity($connection, "Email Sent", "Invoice #$invoice_number sent to {$cust_data['email']}");
                        } else {
                            log_activity($connection, "Email Failed", "Failed to send Invoice #$invoice_number to {$cust_data['email']}");
                        }
                    }
                }

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
                   gst_rate, quantity_in_stock, unit_of_measure, tracking_type
                   FROM products WHERE status = 'active' ORDER BY product_name";
$products_result = db_query($connection, $products_query);

// Fetch company settings
$company_query = "SELECT company_state_code, company_gstin FROM company_settings LIMIT 1";
$company = db_fetch_one($connection, $company_query);
$company_state = $company ? ($company['company_state_code'] ?? '27') : '27';
$company_gstin = $company ? ($company['company_gstin'] ?? '') : '';
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
                <!-- Customer Selection -->
                <div class="form-group col-md-4">
                    <label for="customer_id" class="form-label">Select Customer *</label>
                    <div class="input-group" style="display:flex;">
                        <select id="customer_id" name="customer_id" class="form-control form-select" required onchange="updateCustomerState()" style="flex:1;">
                            <option value="">-- Select Customer (Search Name/Mobile) --</option>
                            <?php 
                            // Re-fetch to include phone in display
                            $cust_res = db_query($connection, "SELECT customer_id, customer_name, phone, customer_type, billing_state_code FROM customers WHERE status = 'active' ORDER BY customer_name");
                            while ($customer = mysqli_fetch_assoc($cust_res)): 
                            ?>
                                <option value="<?php echo $customer['customer_id']; ?>" 
                                        data-state="<?php echo $customer['billing_state_code']; ?>">
                                    <?php echo escape_html($customer['customer_name']); ?> 
                                    <?php echo !empty($customer['phone']) ? "({$customer['phone']})" : ""; ?>
                                    - <?php echo $customer['customer_type']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="button" class="btn btn-secondary" onclick="openAddCustModal()" style="margin-left:5px;">+</button>
                    </div>
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
                                    data-unit="<?php echo escape_html($product['unit_of_measure']); ?>"
                                    data-tracking="<?php echo isset($product['tracking_type']) ? $product['tracking_type'] : 'none'; ?>">
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
            </div>

            <!-- Dynamic Tracking Inputs -->
            <div id="tracking_container" style="display:none; background: #fffbe6; padding: 15px; border: 1px solid #ffe58f; border-radius: 8px; margin-bottom: 20px;">
                <h4 style="margin-top:0; font-size:14px; margin-bottom:10px;">Select Stock Details</h4>
                <div id="batch_selection" style="display:none;">
                     <label>Select Batch</label>
                     <select id="batch_id" class="form-control">
                         <option value="">Loading batches...</option>
                     </select>
                     <small class="text-muted" id="batch_info"></small>
                </div>
                <div id="serial_selection" style="display:none;">
                     <label>Select Serial Numbers (Select multiple used for quantity)</label>
                     <select id="serial_ids" class="form-control" multiple style="height: 100px;">
                         <option value="">Loading serials...</option>
                     </select>
                     <small class="text-muted">Hold Ctrl to select multiple</small>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="table table-bordered table-striped" id="productsTable">
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

<style>
/* Enforce Table Styles */
#productsTable {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 14px;
}
#productsTable th {
    background-color: #f8fafc;
    color: #64748b;
    font-weight: 600;
    text-align: left;
    padding: 12px;
    border-bottom: 2px solid #e2e8f0;
}
#productsTable td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}
#productsTable tr:last-child td {
    border-bottom: none;
}
.btn-delete {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-delete:hover {
    background: #fecaca;
}
</style>
    
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
            <div class="form-row align-items-center">
                <div class="form-group col-md-6">
                    <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="send_email" value="1" checked> 
                        <span>📧 Email Invoice to Customer upon creation</span>
                    </label>
                </div>
                
                <div class="form-group col-md-6 text-right">
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
    </div>
</form>

<script>
// Global variables
let products = [];
let customerState = '';
const companyState = '<?php echo $company_state; ?>';
const companyGSTIN = '<?php echo $company_gstin; ?>';

// Update customer state when customer is selected
function updateCustomerState() {
    const select = document.getElementById('customer_id');
    const selectedOption = select.options[select.selectedIndex];
    customerState = selectedOption.getAttribute('data-state') || '';
    calculateTotals();
}

// Update product price and check tracking when product is selected
document.getElementById('product_select').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    // Reset Tracking UI
    document.getElementById('tracking_container').style.display = 'none';
    document.getElementById('batch_selection').style.display = 'none';
    document.getElementById('serial_selection').style.display = 'none';
    document.getElementById('batch_id').innerHTML = '<option value="">Select Batch</option>';
    document.getElementById('serial_ids').innerHTML = '';
    
    if (selectedOption.value) {
        document.getElementById('product_price').value = selectedOption.getAttribute('data-price');
        
        const tracking = selectedOption.getAttribute('data-tracking');
        if (tracking === 'batch') {
            document.getElementById('tracking_container').style.display = 'block';
            document.getElementById('batch_selection').style.display = 'block';
            fetchStockDetails(selectedOption.value, 'batch');
        } else if (tracking === 'serial') {
            document.getElementById('tracking_container').style.display = 'block';
            document.getElementById('serial_selection').style.display = 'block';
            fetchStockDetails(selectedOption.value, 'serial');
        }
    } else {
        document.getElementById('product_price').value = '';
    }
});

function fetchStockDetails(pid, type) {
    fetch(`get_stock_details.php?product_id=${pid}&type=${type}`)
    .then(r => r.json())
    .then(data => {
        if (data.type === 'batch') {
            const sel = document.getElementById('batch_id');
            sel.innerHTML = '<option value="">Select Batch</option>';
            data.data.forEach(b => {
                sel.innerHTML += `<option value="${b.batch_id}">Batch: ${b.batch_no} (Exp: ${b.expiry_date}) - Qty: ${b.quantity_remaining}</option>`;
            });
        } else if (data.type === 'serial') {
            const sel = document.getElementById('serial_ids');
            sel.innerHTML = '';
            data.data.forEach(s => {
                sel.innerHTML += `<option value="${s.serial_id}">${s.serial_no}</option>`;
            });
        }
    });
}

// Add product to list
function addProduct() {
    const select = document.getElementById('product_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) {
        alert('Please select a product');
        return;
    }
    
    // Tracking Validation
    const tracking = selectedOption.getAttribute('data-tracking');
    let batchId = null;
    let serialIds = [];
    let trackingInfo = '';

    if (tracking === 'batch') {
        const batchSel = document.getElementById('batch_id');
        if (!batchSel.value) {
            alert('Please select a batch.');
            return;
        }
        batchId = batchSel.value;
        trackingInfo = batchSel.options[batchSel.selectedIndex].text;
    } 
    else if (tracking === 'serial') {
        const serialSel = document.getElementById('serial_ids');
        for (let opt of serialSel.options) {
            if (opt.selected) {
                serialIds.push(opt.value);
            }
        }
        if (serialIds.length === 0) {
            alert('Please select at least one serial number.');
            return;
        }
        
        // Ensure Qty matches Serial Count
        const enteredQty = parseFloat(document.getElementById('product_qty').value);
        if (serialIds.length !== enteredQty) {
            alert(`You selected ${serialIds.length} serials but Quantity is ${enteredQty}. Please match them.`);
            return;
        }
        trackingInfo = serialIds.length + ' Serials Selected';
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
        unit_of_measure: selectedOption.getAttribute('data-unit'),
        tracking_type: tracking,
        batch_id: batchId,
        serial_ids: serialIds,
        tracking_info: trackingInfo
    };
    
    products.push(product);
    updateProductsTable();
    calculateTotals();
    
    // Reset form
    document.getElementById('product_select').value = '';
    document.getElementById('product_qty').value = 1;
    document.getElementById('product_price').value = '';
    document.getElementById('tracking_container').style.display = 'none';
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
    
    // Clear current content
    tbody.innerHTML = '';
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr id="emptyRow"><td colspan="7" class="text-center" style="padding:20px; color:#999;">No products added yet</td></tr>';
        return;
    }
    
    products.forEach((product, index) => {
        const amount = product.quantity * product.unit_price;
        const trackingDisplay = product.tracking_info ? `<br><small class="text-primary" style="font-size:0.85em;">📦 ${product.tracking_info}</small>` : '';
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div style="font-weight:600; color:#333;">${product.product_name}</div>
                <div style="font-size:0.85em; color:#666;">${product.product_code}</div>
                ${trackingDisplay}
            </td>
            <td>${product.hsn_code || '-'}</td>
            <td>${product.quantity} ${product.unit_of_measure}</td>
            <td>₹${product.unit_price.toFixed(2)}</td>
            <td style="font-weight:600;">₹${amount.toFixed(2)}</td>
            <td>${product.gst_rate}%</td>
            <td>
                <button type="button" class="btn-action btn-delete" onclick="removeProduct(${index})" title="Remove Item">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
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
    
    // Only calculate GST if company has GSTIN
    if (companyGSTIN && companyGSTIN.trim() !== '') {
        products.forEach(product => {
            const itemTaxable = (product.quantity * product.unit_price) * (taxableAmount / subtotal);
            const gstAmount = (itemTaxable * product.gst_rate) / 100;
            totalGST += gstAmount;
        });
    } else {
        gstLabel = 'Tax (0%)';
    }
    
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
}); // End form listener
</script>

<!-- Add Customer Modal -->
<div id="addCustModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:25px; border-radius:10px; width:350px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Add New Customer</h3>
        
        <div style="margin-top:15px;">
            <label style="display:block; font-size:12px; margin-bottom:5px;">Mobile Number *</label>
            <input type="text" id="newCustMobile" class="form-control">
        </div>
        <div style="margin-top:10px;">
            <label style="display:block; font-size:12px; margin-bottom:5px;">Customer Name *</label>
            <input type="text" id="newCustName" class="form-control">
        </div>
        <div style="margin-top:10px;">
            <label style="display:block; font-size:12px; margin-bottom:5px;">Address</label>
            <textarea id="newCustAddr" class="form-control" rows="2"></textarea>
        </div>
        
        <div style="margin-top:20px; text-align:right;">
            <button type="button" onclick="closeAddCustModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="saveNewCustomer()" class="btn btn-primary">Save</button>
        </div>
    </div>
</div>

<script>
    // Add Customer Logic for Create Invoice
    function openAddCustModal() {
        document.getElementById('addCustModal').style.display = 'flex';
        document.getElementById('newCustMobile').focus();
    }
    
    function closeAddCustModal() {
        document.getElementById('addCustModal').style.display = 'none';
        document.getElementById('newCustName').value = '';
        document.getElementById('newCustMobile').value = '';
        document.getElementById('newCustAddr').value = '';
    }
    
    function saveNewCustomer() {
        const name = document.getElementById('newCustName').value;
        const mobile = document.getElementById('newCustMobile').value;
        const addr = document.getElementById('newCustAddr').value;
        
        if (!name || !mobile) { alert('Name and Mobile are required'); return; }
        
        // Setup data
        const formData = new FormData();
        formData.append('customer_name', name);
        formData.append('customer_phone', mobile);
        formData.append('customer_address', addr);
        
        fetch('../ajax_add_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Add to select and select it
                const select = document.getElementById('customer_id');
                const opt = document.createElement('option');
                opt.value = data.customer.id;
                opt.text = data.customer.text + ' - B2C'; 
                opt.selected = true;
                select.add(opt);
                
                // Trigger change to update state/logic
                select.value = data.customer.id;
                if(select.onchange) select.onchange();
                
                closeAddCustModal();
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error creating customer');
        });
    }
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
