<?php
/**
 * ============================================================================
 * CUSTOMERS MANAGEMENT PAGE
 * ============================================================================
 * Purpose: Display, add, edit, and delete customers with GST details
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Customers";

// Include header
require_once '../includes/header.php';

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

// Add Customer
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $customer_name = sanitize_sql($connection, $_POST['customer_name']);
    $customer_type = sanitize_sql($connection, $_POST['customer_type']);
    $contact_person = sanitize_sql($connection, $_POST['contact_person']);
    $email = sanitize_sql($connection, $_POST['email']);
    $phone = sanitize_sql($connection, $_POST['phone']);
    $gstin = sanitize_sql($connection, $_POST['gstin']);
    $billing_address = sanitize_sql($connection, $_POST['billing_address']);
    $billing_city = sanitize_sql($connection, $_POST['billing_city']);
    $billing_state = sanitize_sql($connection, $_POST['billing_state']);
    $billing_state_code = sanitize_sql($connection, $_POST['billing_state_code']);
    $billing_pincode = sanitize_sql($connection, $_POST['billing_pincode']);
    
    $has_error = false;
    
    if (empty($customer_name)) {
        set_error_message("Customer name is required.");
        $has_error = true;
    }
    
    // Validate GSTIN for B2B customers
    if ($customer_type === 'B2B' && !empty($gstin)) {
        if (!validate_gstin($gstin)) {
            set_error_message("Invalid GSTIN format. Example: 27AAAAA0000A1Z5");
            $has_error = true;
        }
    }
    
    if (!$has_error) {
        $insert_query = "INSERT INTO customers 
                        (customer_name, customer_type, contact_person, email, phone, gstin,
                         billing_address, billing_city, billing_state, billing_state_code, billing_pincode, status) 
                        VALUES 
                        ('{$customer_name}', '{$customer_type}', '{$contact_person}', '{$email}', '{$phone}', '{$gstin}',
                         '{$billing_address}', '{$billing_city}', '{$billing_state}', '{$billing_state_code}', '{$billing_pincode}', 'active')";
        
        if (db_execute($connection, $insert_query)) {
            set_success_message("Customer '{$customer_name}' has been successfully added.");
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to add customer.");
        }
    }
}

// Update Customer
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $customer_id = sanitize_sql($connection, $_POST['customer_id']);
    $customer_name = sanitize_sql($connection, $_POST['customer_name']);
    $customer_type = sanitize_sql($connection, $_POST['customer_type']);
    $contact_person = sanitize_sql($connection, $_POST['contact_person']);
    $email = sanitize_sql($connection, $_POST['email']);
    $phone = sanitize_sql($connection, $_POST['phone']);
    $gstin = sanitize_sql($connection, $_POST['gstin']);
    $billing_address = sanitize_sql($connection, $_POST['billing_address']);
    $billing_city = sanitize_sql($connection, $_POST['billing_city']);
    $billing_state = sanitize_sql($connection, $_POST['billing_state']);
    $billing_state_code = sanitize_sql($connection, $_POST['billing_state_code']);
    $billing_pincode = sanitize_sql($connection, $_POST['billing_pincode']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    if (!empty($customer_name)) {
        $update_query = "UPDATE customers SET 
                        customer_name = '{$customer_name}',
                        customer_type = '{$customer_type}',
                        contact_person = '{$contact_person}',
                        email = '{$email}',
                        phone = '{$phone}',
                        gstin = '{$gstin}',
                        billing_address = '{$billing_address}',
                        billing_city = '{$billing_city}',
                        billing_state = '{$billing_state}',
                        billing_state_code = '{$billing_state_code}',
                        billing_pincode = '{$billing_pincode}',
                        status = '{$status}'
                        WHERE customer_id = '{$customer_id}'";
        
        if (db_execute($connection, $update_query)) {
            set_success_message("Customer has been successfully updated.");
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to update customer.");
        }
    }
}

// ============================================================================
// FETCH ALL CUSTOMERS
// ============================================================================

$customers_query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM invoices WHERE customer_id = c.customer_id) as invoice_count
                    FROM customers c
                    ORDER BY c.customer_name";
$customers_result = db_query($connection, $customers_query);

// Get customer for editing
$edit_customer = null;
if (isset($_GET['edit']) && validate_numeric($_GET['edit'])) {
    $edit_id = sanitize_sql($connection, $_GET['edit']);
    $edit_query = "SELECT * FROM customers WHERE customer_id = '{$edit_id}' LIMIT 1";
    $edit_customer = db_fetch_one($connection, $edit_query);
}

// Get Indian states for dropdown
$indian_states = get_indian_states();
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">👥</span>
        Customer Management
    </h2>
</div>

<!-- ================================================================ -->
<!-- ADD/EDIT CUSTOMER FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo $edit_customer ? '✏️ Edit Customer' : '➕ Add New Customer'; ?></h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="customerForm">
            <input type="hidden" name="action" value="<?php echo $edit_customer ? 'edit' : 'add'; ?>">
            <?php if ($edit_customer): ?>
                <input type="hidden" name="customer_id" value="<?php echo $edit_customer['customer_id']; ?>">
            <?php endif; ?>
            
            <!-- Customer Type and Name -->
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="customer_type" class="form-label">Customer Type *</label>
                    <select id="customer_type" name="customer_type" class="form-control" required onchange="toggleGSTIN()">
                        <option value="B2C" <?php echo ($edit_customer && $edit_customer['customer_type'] === 'B2C') ? 'selected' : 'selected'; ?>>B2C (Retail)</option>
                        <option value="B2B" <?php echo ($edit_customer && $edit_customer['customer_type'] === 'B2B') ? 'selected' : ''; ?>>B2B (Business)</option>
                    </select>
                </div>
                
                <div class="form-group col-md-9">
                    <label for="customer_name" class="form-label">Customer Name *</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" 
                           placeholder="Enter customer name"
                           value="<?php echo $edit_customer ? escape_html($edit_customer['customer_name']) : ''; ?>" required>
                </div>
            </div>
            
            <!-- Contact Details -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" 
                           placeholder="Enter contact person"
                           value="<?php echo $edit_customer ? escape_html($edit_customer['contact_person']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Enter email"
                           value="<?php echo $edit_customer ? escape_html($edit_customer['email']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" 
                           placeholder="Enter phone number"
                           value="<?php echo $edit_customer ? escape_html($edit_customer['phone']) : ''; ?>">
                </div>
            </div>
            
            <!-- GSTIN (for B2B) -->
            <div class="form-group" id="gstin_field" style="display: none;">
                <label for="gstin" class="form-label">GSTIN (15 characters)</label>
                <input type="text" id="gstin" name="gstin" class="form-control" 
                       placeholder="e.g., 27AAAAA0000A1Z5" maxlength="15"
                       value="<?php echo $edit_customer ? escape_html($edit_customer['gstin']) : ''; ?>">
                <small class="form-text">Format: 2-digit state code + 10-char PAN + 3 chars</small>
            </div>
            
            <!-- Billing Address -->
            <div class="form-group">
                <label for="billing_address" class="form-label">Billing Address</label>
                <textarea id="billing_address" name="billing_address" class="form-control" rows="2"
                          placeholder="Enter billing address"><?php echo $edit_customer ? escape_html($edit_customer['billing_address']) : ''; ?></textarea>
            </div>
            
            <!-- City, State, Pincode -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="billing_city" class="form-label">City</label>
                    <input type="text" id="billing_city" name="billing_city" class="form-control" 
                           placeholder="Enter city"
                           value="<?php echo $edit_customer ? escape_html($edit_customer['billing_city']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="billing_state_code" class="form-label">State</label>
                    <select id="billing_state_code" name="billing_state_code" class="form-control" onchange="updateStateName()">
                        <option value="">Select State</option>
                        <?php foreach ($indian_states as $code => $name): ?>
                            <option value="<?php echo $code; ?>" 
                                    <?php echo ($edit_customer && $edit_customer['billing_state_code'] === $code) ? 'selected' : ''; ?>>
                                <?php echo $code . ' - ' . $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="billing_state" name="billing_state" 
                           value="<?php echo $edit_customer ? escape_html($edit_customer['billing_state']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="billing_pincode" class="form-label">Pincode</label>
                    <input type="text" id="billing_pincode" name="billing_pincode" class="form-control" 
                           placeholder="Enter pincode"
                           value="<?php echo $edit_customer ? escape_html($edit_customer['billing_pincode']) : ''; ?>">
                </div>
            </div>
            
            <?php if ($edit_customer): ?>
            <!-- Status (only for edit) -->
            <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo ($edit_customer['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($edit_customer['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <?php endif; ?>
            
            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    <?php echo $edit_customer ? 'Update Customer' : 'Add Customer'; ?>
                </button>
                <?php if ($edit_customer): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                        <span class="btn-icon">✕</span>
                        Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- CUSTOMERS LIST -->
<!-- ================================================================ -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">All Customers</h3>
        <span class="card-subtitle">
            Total: <?php echo mysqli_num_rows($customers_result); ?> customers
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>GSTIN</th>
                        <th>State</th>
                        <th>Invoices</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers_result && mysqli_num_rows($customers_result) > 0): ?>
                        <?php while ($customer = mysqli_fetch_assoc($customers_result)): ?>
                            <tr>
                                <td><?php echo $customer['customer_id']; ?></td>
                                <td><strong><?php echo escape_html($customer['customer_name']); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $customer['customer_type'] === 'B2B' ? 'info' : 'success'; ?>">
                                        <?php echo $customer['customer_type']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo escape_html($customer['phone']); ?><br>
                                    <small><?php echo escape_html($customer['email']); ?></small>
                                </td>
                                <td><?php echo escape_html($customer['gstin']); ?></td>
                                <td><?php echo escape_html($customer['billing_state']); ?></td>
                                <td><?php echo $customer['invoice_count']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $customer['status']; ?>">
                                        <?php echo ucfirst($customer['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $customer['customer_id']; ?>" 
                                       class="btn-action btn-edit" title="Edit">✏️</a>
                                    <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>" 
                                       class="btn-action btn-delete" title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this customer?');">🗑️</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Toggle GSTIN field based on customer type
function toggleGSTIN() {
    const customerType = document.getElementById('customer_type').value;
    const gstinField = document.getElementById('gstin_field');
    
    if (customerType === 'B2B') {
        gstinField.style.display = 'block';
    } else {
        gstinField.style.display = 'none';
        document.getElementById('gstin').value = '';
    }
}

// Update state name when state code is selected
function updateStateName() {
    const stateCode = document.getElementById('billing_state_code').value;
    const stateSelect = document.getElementById('billing_state_code');
    const selectedOption = stateSelect.options[stateSelect.selectedIndex];
    const stateName = selectedOption.text.split(' - ')[1] || '';
    
    document.getElementById('billing_state').value = stateName;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleGSTIN();
    if (document.getElementById('billing_state_code').value) {
        updateStateName();
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
