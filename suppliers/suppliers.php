<?php
/**
 * ============================================================================
 * SUPPLIERS MANAGEMENT PAGE
 * ============================================================================
 * Purpose: Display, add, edit, and delete suppliers
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Suppliers";

// Include header
require_once '../includes/header.php';

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

// Add Supplier
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $supplier_name = sanitize_sql($connection, $_POST['supplier_name']);
    $contact_person = sanitize_sql($connection, $_POST['contact_person']);
    $email = sanitize_sql($connection, $_POST['email']);
    $phone = sanitize_sql($connection, $_POST['phone']);
    $address = sanitize_sql($connection, $_POST['address']);
    $city = sanitize_sql($connection, $_POST['city']);
    $country = sanitize_sql($connection, $_POST['country']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    if (!empty($supplier_name)) {
        $insert_query = "INSERT INTO suppliers 
                        (supplier_name, contact_person, email, phone, address, city, country, status) 
                        VALUES 
                        ('{$supplier_name}', '{$contact_person}', '{$email}', '{$phone}', 
                         '{$address}', '{$city}', '{$country}', '{$status}')";
        
        if (db_execute($connection, $insert_query)) {
            set_success_message("Supplier '{$supplier_name}' has been successfully added.");
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to add supplier.");
        }
    } else {
        set_error_message("Supplier name is required.");
    }
}

// Update Supplier
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $supplier_id = sanitize_sql($connection, $_POST['supplier_id']);
    $supplier_name = sanitize_sql($connection, $_POST['supplier_name']);
    $contact_person = sanitize_sql($connection, $_POST['contact_person']);
    $email = sanitize_sql($connection, $_POST['email']);
    $phone = sanitize_sql($connection, $_POST['phone']);
    $address = sanitize_sql($connection, $_POST['address']);
    $city = sanitize_sql($connection, $_POST['city']);
    $country = sanitize_sql($connection, $_POST['country']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    if (!empty($supplier_name)) {
        $update_query = "UPDATE suppliers SET 
                        supplier_name = '{$supplier_name}',
                        contact_person = '{$contact_person}',
                        email = '{$email}',
                        phone = '{$phone}',
                        address = '{$address}',
                        city = '{$city}',
                        country = '{$country}',
                        status = '{$status}'
                        WHERE supplier_id = '{$supplier_id}'";
        
        if (db_execute($connection, $update_query)) {
            set_success_message("Supplier has been successfully updated.");
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to update supplier.");
        }
    } else {
        set_error_message("Supplier name is required.");
    }
}

// ============================================================================
// FETCH ALL SUPPLIERS
// ============================================================================

$suppliers_query = "SELECT s.*, COUNT(p.product_id) as product_count
                    FROM suppliers s
                    LEFT JOIN products p ON s.supplier_id = p.supplier_id AND p.status = 'active'
                    GROUP BY s.supplier_id
                    ORDER BY s.supplier_name";
$suppliers_result = db_query($connection, $suppliers_query);

// Get supplier for editing if ID is provided
$edit_supplier = null;
if (isset($_GET['edit']) && validate_numeric($_GET['edit'])) {
    $edit_id = sanitize_sql($connection, $_GET['edit']);
    $edit_query = "SELECT * FROM suppliers WHERE supplier_id = '{$edit_id}' LIMIT 1";
    $edit_supplier = db_fetch_one($connection, $edit_query);
}
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">🏢</span>
        Suppliers Management
    </h2>
</div>

<!-- ================================================================ -->
<!-- ADD/EDIT SUPPLIER FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo $edit_supplier ? '✏️ Edit Supplier' : '➕ Add New Supplier'; ?></h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="<?php echo $edit_supplier ? 'edit' : 'add'; ?>">
            <?php if ($edit_supplier): ?>
                <input type="hidden" name="supplier_id" value="<?php echo $edit_supplier['supplier_id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="supplier_name" class="form-label">Supplier Name *</label>
                    <input 
                        type="text" 
                        id="supplier_name" 
                        name="supplier_name" 
                        class="form-control" 
                        placeholder="Enter supplier name"
                        value="<?php echo $edit_supplier ? escape_html($edit_supplier['supplier_name']) : ''; ?>"
                        required
                    >
                </div>
                
                <div class="form-group col-md-6">
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input 
                        type="text" 
                        id="contact_person" 
                        name="contact_person" 
                        class="form-control" 
                        placeholder="Enter contact person name"
                        value="<?php echo $edit_supplier ? escape_html($edit_supplier['contact_person']) : ''; ?>"
                    >
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Enter email address"
                        value="<?php echo $edit_supplier ? escape_html($edit_supplier['email']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        class="form-control" 
                        placeholder="Enter phone number"
                        value="<?php echo $edit_supplier ? escape_html($edit_supplier['phone']) : ''; ?>"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea 
                    id="address" 
                    name="address" 
                    class="form-control" 
                    rows="2"
                    placeholder="Enter address"
                ><?php echo $edit_supplier ? escape_html($edit_supplier['address']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="city" class="form-label">City</label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        class="form-control" 
                        placeholder="Enter city"
                        value="<?php echo $edit_supplier ? escape_html($edit_supplier['city']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group col-md-4">
                    <label for="country" class="form-label">Country</label>
                    <input 
                        type="text" 
                        id="country" 
                        name="country" 
                        class="form-control" 
                        placeholder="Enter country"
                        value="<?php echo $edit_supplier ? escape_html($edit_supplier['country']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group col-md-4">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo ($edit_supplier && $edit_supplier['status'] === 'active') ? 'selected' : 'selected'; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_supplier && $edit_supplier['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    <?php echo $edit_supplier ? 'Update Supplier' : 'Add Supplier'; ?>
                </button>
                <?php if ($edit_supplier): ?>
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
<!-- SUPPLIERS LIST -->
<!-- ================================================================ -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">All Suppliers</h3>
        <span class="card-subtitle">
            Total: <?php echo mysqli_num_rows($suppliers_result); ?> suppliers
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($suppliers_result && mysqli_num_rows($suppliers_result) > 0): ?>
                        <?php while ($supplier = mysqli_fetch_assoc($suppliers_result)): ?>
                            <tr>
                                <td><?php echo $supplier['supplier_id']; ?></td>
                                <td><strong><?php echo escape_html($supplier['supplier_name']); ?></strong></td>
                                <td><?php echo escape_html($supplier['contact_person']); ?></td>
                                <td><?php echo escape_html($supplier['email']); ?></td>
                                <td><?php echo escape_html($supplier['phone']); ?></td>
                                <td><?php echo escape_html($supplier['city']); ?></td>
                                <td><?php echo $supplier['product_count']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $supplier['status']; ?>">
                                        <?php echo ucfirst($supplier['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $supplier['supplier_id']; ?>" 
                                       class="btn-action btn-edit" 
                                       title="Edit">
                                        ✏️
                                    </a>
                                    <a href="delete_supplier.php?id=<?php echo $supplier['supplier_id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this supplier?');">
                                        🗑️
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
