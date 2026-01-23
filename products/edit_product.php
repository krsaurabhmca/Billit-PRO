<?php
/**
 * ============================================================================
 * EDIT PRODUCT PAGE
 * ============================================================================
 * Purpose: Form to edit existing product information
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Edit Product";

// Include header
require_once '../includes/header.php';

// ============================================================================
// GET PRODUCT ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid product ID.");
    redirect('products.php');
}

$product_id = sanitize_sql($connection, $_GET['id']);

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
    $gst_rate = sanitize_sql($connection, $_POST['gst_rate']);
    $hsn_code = sanitize_sql($connection, $_POST['hsn_code']);
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
    
    if (!validate_numeric($gst_rate) || $gst_rate < 0) {
        set_error_message("Valid GST Rate is required.");
        $has_error = true;
    }
    
    if (!validate_numeric($reorder_level) || $reorder_level < 0) {
        set_error_message("Valid reorder level is required.");
        $has_error = true;
    }
    
    // Check if product code already exists for other products
    if (!$has_error) {
        $check_query = "SELECT product_id FROM products 
                       WHERE product_code = '{$product_code}' 
                       AND product_id != '{$product_id}' 
                       LIMIT 1";
        $check_result = db_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            set_error_message("Product code already exists. Please use a different code.");
            $has_error = true;
        }
    }
    
    // If no errors, update product
    if (!$has_error) {
        $supplier_value = ($supplier_id === 'NULL') ? 'NULL' : "'{$supplier_id}'";
        
        $update_query = "UPDATE products SET 
                        product_code = '{$product_code}',
                        product_name = '{$product_name}',
                        description = '{$description}',
                        category_id = '{$category_id}',
                        supplier_id = {$supplier_value},
                        unit_price = '{$unit_price}',
                        gst_rate = '{$gst_rate}',
                        hsn_code = '{$hsn_code}',
                        reorder_level = '{$reorder_level}',
                        unit_of_measure = '{$unit_of_measure}',
                        status = '{$status}'
                        WHERE product_id = '{$product_id}'";
        
        if (db_execute($connection, $update_query)) {
            set_success_message("Product '{$product_name}' has been successfully updated.");
            redirect('products.php');
        } else {
            set_error_message("Failed to update product. Please try again.");
        }
    }
}

// ============================================================================
// FETCH PRODUCT DATA
// ============================================================================

$product_query = "SELECT * FROM products WHERE product_id = '{$product_id}' LIMIT 1";
$product = db_fetch_one($connection, $product_query);

if (!$product) {
    set_error_message("Product not found.");
    redirect('products.php');
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
        <span class="page-icon">✏️</span>
        Edit Product
    </h2>
    <div class="page-actions">
        <a href="products.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Back to Products
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- EDIT PRODUCT FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Product Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $product_id; ?>">
            <!-- Product Code and Name -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="product_code" class="form-label">Product Code *</label>
                    <input 
                        type="text" 
                        id="product_code" 
                        name="product_code" 
                        class="form-control" 
                        value="<?php echo escape_html($product['product_code']); ?>"
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
                        value="<?php echo escape_html($product['product_name']); ?>"
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
                ><?php echo escape_html($product['description']); ?></textarea>
            </div>
            
            <!-- Category and Supplier -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo ($product['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
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
                                    <?php echo ($product['supplier_id'] == $supplier['supplier_id']) ? 'selected' : ''; ?>>
                                <?php echo escape_html($supplier['supplier_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <!-- Price and Stock Info -->
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="unit_price" class="form-label">Unit Price (₹) *</label>
                    <input 
                        type="number" 
                        id="unit_price" 
                        name="unit_price" 
                        class="form-control" 
                        step="0.01" 
                        min="0"
                        value="<?php echo $product['unit_price']; ?>"
                        required
                    >
                </div>
                
                <div class="form-group col-md-3">
                    <label for="gst_rate" class="form-label">GST Rate (%)</label>
                    <select id="gst_rate" name="gst_rate" class="form-control">
                        <option value="0" <?php echo ($product['gst_rate'] == 0) ? 'selected' : ''; ?>>0% (Exempt)</option>
                        <option value="5" <?php echo ($product['gst_rate'] == 5) ? 'selected' : ''; ?>>5%</option>
                        <option value="12" <?php echo ($product['gst_rate'] == 12) ? 'selected' : ''; ?>>12%</option>
                        <option value="18" <?php echo ($product['gst_rate'] == 18) ? 'selected' : ''; ?>>18% (Standard)</option>
                        <option value="28" <?php echo ($product['gst_rate'] == 28) ? 'selected' : ''; ?>>28%</option>
                    </select>
                </div>
                
                <div class="form-group col-md-2">
                    <label for="hsn_code" class="form-label">HSN Code</label>
                    <input 
                        type="text" 
                        id="hsn_code" 
                        name="hsn_code" 
                        class="form-control" 
                        value="<?php echo escape_html($product['hsn_code'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group col-md-2">
                    <label for="current_stock" class="form-label">Stock</label>
                    <input 
                        type="text" 
                        id="current_stock" 
                        class="form-control" 
                        value="<?php echo $product['quantity_in_stock']; ?>"
                        readonly
                        style="background-color: #f5f5f5;"
                    >
                </div>
                
                <div class="form-group col-md-2">
                    <label for="reorder_level" class="form-label">Reorder Lvl</label>
                    <input 
                        type="number" 
                        id="reorder_level" 
                        name="reorder_level" 
                        class="form-control" 
                        min="0"
                        value="<?php echo $product['reorder_level']; ?>"
                        required
                    >
                </div>
            </div>
            
            <!-- Unit of Measure and Status -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                    <select id="unit_of_measure" name="unit_of_measure" class="form-control" required>
                        <option value="pcs" <?php echo ($product['unit_of_measure'] === 'pcs') ? 'selected' : ''; ?>>Pieces (pcs)</option>
                        <option value="box" <?php echo ($product['unit_of_measure'] === 'box') ? 'selected' : ''; ?>>Box</option>
                        <option value="kg" <?php echo ($product['unit_of_measure'] === 'kg') ? 'selected' : ''; ?>>Kilogram (kg)</option>
                        <option value="ltr" <?php echo ($product['unit_of_measure'] === 'ltr') ? 'selected' : ''; ?>>Liter (ltr)</option>
                        <option value="set" <?php echo ($product['unit_of_measure'] === 'set') ? 'selected' : ''; ?>>Set</option>
                        <option value="ream" <?php echo ($product['unit_of_measure'] === 'ream') ? 'selected' : ''; ?>>Ream</option>
                    </select>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo ($product['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($product['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="discontinued" <?php echo ($product['status'] === 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                    </select>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    Update Product
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
