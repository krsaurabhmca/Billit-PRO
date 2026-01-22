<?php
/**
 * ============================================================================
 * ADD PRODUCT PAGE
 * ============================================================================
 * Purpose: Form to add new product to inventory
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Add Product";

// Include header
require_once '../includes/header.php';

// ============================================================================
// PROCESS FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $product_code = sanitize_sql($connection, $_POST['product_code']);
    $product_name = sanitize_sql($connection, $_POST['product_name']);
    $description = sanitize_sql($connection, $_POST['description']);
    $category_id = sanitize_sql($connection, $_POST['category_id']);
    $supplier_id = !empty($_POST['supplier_id']) ? sanitize_sql($connection, $_POST['supplier_id']) : 'NULL';
    $unit_price = sanitize_sql($connection, $_POST['unit_price']);
    $quantity_in_stock = sanitize_sql($connection, $_POST['quantity_in_stock']);
    $reorder_level = sanitize_sql($connection, $_POST['reorder_level']);
    $unit_of_measure = sanitize_sql($connection, $_POST['unit_of_measure']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    // Initialize error flag
    $has_error = false;
    
    // Validate inputs
    if (empty($product_code)) {
        set_error_message("Product code is required.");
        $has_error = true;
    }
    
    if (empty($product_name)) {
        set_error_message("Product name is required.");
        $has_error = true;
    }
    
    if (empty($category_id)) {
        set_error_message("Category is required.");
        $has_error = true;
    }
    
    if (!validate_numeric($unit_price) || $unit_price < 0) {
        set_error_message("Valid unit price is required.");
        $has_error = true;
    }
    
    if (!validate_numeric($quantity_in_stock) || $quantity_in_stock < 0) {
        set_error_message("Valid quantity is required.");
        $has_error = true;
    }
    
    if (!validate_numeric($reorder_level) || $reorder_level < 0) {
        set_error_message("Valid reorder level is required.");
        $has_error = true;
    }
    
    // Check if product code already exists
    if (!$has_error) {
        $check_query = "SELECT product_id FROM products WHERE product_code = '{$product_code}' LIMIT 1";
        $check_result = db_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            set_error_message("Product code already exists. Please use a different code.");
            $has_error = true;
        }
    }
    
    // If no errors, insert product
    if (!$has_error) {
        $supplier_value = ($supplier_id === 'NULL') ? 'NULL' : "'{$supplier_id}'";
        
        $insert_query = "INSERT INTO products 
                        (product_code, product_name, description, category_id, supplier_id, 
                         unit_price, quantity_in_stock, reorder_level, unit_of_measure, status) 
                        VALUES 
                        ('{$product_code}', '{$product_name}', '{$description}', '{$category_id}', 
                         {$supplier_value}, '{$unit_price}', '{$quantity_in_stock}', 
                         '{$reorder_level}', '{$unit_of_measure}', '{$status}')";
        
        if (db_execute($connection, $insert_query)) {
            set_success_message("Product '{$product_name}' has been successfully added.");
            redirect('products.php');
        } else {
            set_error_message("Failed to add product. Please try again.");
        }
    }
}

// ============================================================================
// FETCH CATEGORIES AND SUPPLIERS FOR DROPDOWNS
// ============================================================================

$categories_query = "SELECT category_id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = db_query($connection, $categories_query);

$suppliers_query = "SELECT supplier_id, supplier_name FROM suppliers WHERE status = 'active' ORDER BY supplier_name";
$suppliers_result = db_query($connection, $suppliers_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">➕</span>
        Add New Product
    </h2>
    <div class="page-actions">
        <a href="products.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Back to Products
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- ADD PRODUCT FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Product Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <!-- Product Code and Name -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="product_code" class="form-label">Product Code *</label>
                    <input 
                        type="text" 
                        id="product_code" 
                        name="product_code" 
                        class="form-control" 
                        placeholder="e.g., PROD001"
                        value="<?php echo isset($_POST['product_code']) ? escape_html($_POST['product_code']) : ''; ?>"
                        required
                    >
                </div>
                
                <div class="form-group col-md-6">
                    <label for="product_name" class="form-label">Product Name *</label>
                    <input 
                        type="text" 
                        id="product_name" 
                        name="product_name" 
                        class="form-control" 
                        placeholder="Enter product name"
                        value="<?php echo isset($_POST['product_name']) ? escape_html($_POST['product_name']) : ''; ?>"
                        required
                    >
                </div>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Enter product description"
                ><?php echo isset($_POST['description']) ? escape_html($_POST['description']) : ''; ?></textarea>
            </div>
            
            <!-- Category and Supplier -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo escape_html($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-control">
                        <option value="">Select Supplier (Optional)</option>
                        <?php while ($supplier = mysqli_fetch_assoc($suppliers_result)): ?>
                            <option value="<?php echo $supplier['supplier_id']; ?>"
                                    <?php echo (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['supplier_id']) ? 'selected' : ''; ?>>
                                <?php echo escape_html($supplier['supplier_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <!-- Price and Stock -->
            <div class="form-row">
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
                        value="<?php echo isset($_POST['unit_price']) ? escape_html($_POST['unit_price']) : ''; ?>"
                        required
                    >
                </div>
                
                <div class="form-group col-md-4">
                    <label for="quantity_in_stock" class="form-label">Initial Stock Quantity *</label>
                    <input 
                        type="number" 
                        id="quantity_in_stock" 
                        name="quantity_in_stock" 
                        class="form-control" 
                        min="0"
                        placeholder="0"
                        value="<?php echo isset($_POST['quantity_in_stock']) ? escape_html($_POST['quantity_in_stock']) : '0'; ?>"
                        required
                    >
                </div>
                
                <div class="form-group col-md-4">
                    <label for="reorder_level" class="form-label">Reorder Level *</label>
                    <input 
                        type="number" 
                        id="reorder_level" 
                        name="reorder_level" 
                        class="form-control" 
                        min="0"
                        placeholder="10"
                        value="<?php echo isset($_POST['reorder_level']) ? escape_html($_POST['reorder_level']) : '10'; ?>"
                        required
                    >
                </div>
            </div>
            
            <!-- Unit of Measure and Status -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                    <select id="unit_of_measure" name="unit_of_measure" class="form-control" required>
                        <option value="pcs" <?php echo (isset($_POST['unit_of_measure']) && $_POST['unit_of_measure'] === 'pcs') ? 'selected' : 'selected'; ?>>Pieces (pcs)</option>
                        <option value="box" <?php echo (isset($_POST['unit_of_measure']) && $_POST['unit_of_measure'] === 'box') ? 'selected' : ''; ?>>Box</option>
                        <option value="kg" <?php echo (isset($_POST['unit_of_measure']) && $_POST['unit_of_measure'] === 'kg') ? 'selected' : ''; ?>>Kilogram (kg)</option>
                        <option value="ltr" <?php echo (isset($_POST['unit_of_measure']) && $_POST['unit_of_measure'] === 'ltr') ? 'selected' : ''; ?>>Liter (ltr)</option>
                        <option value="set" <?php echo (isset($_POST['unit_of_measure']) && $_POST['unit_of_measure'] === 'set') ? 'selected' : ''; ?>>Set</option>
                        <option value="ream" <?php echo (isset($_POST['unit_of_measure']) && $_POST['unit_of_measure'] === 'ream') ? 'selected' : ''; ?>>Ream</option>
                    </select>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : 'selected'; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="discontinued" <?php echo (isset($_POST['status']) && $_POST['status'] === 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                    </select>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    Add Product
                </button>
                <a href="products.php" class="btn btn-secondary">
                    <span class="btn-icon">✕</span>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
