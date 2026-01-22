<?php
/**
 * ============================================================================
 * PRODUCTS LISTING PAGE
 * ============================================================================
 * Purpose: Display all products with search and filter functionality
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Products";

// Include header
require_once '../includes/header.php';

// ============================================================================
// SEARCH AND FILTER PROCESSING
// ============================================================================

$search_term = '';
$category_filter = '';
$status_filter = '';

if (isset($_GET['search'])) {
    $search_term = sanitize_sql($connection, $_GET['search']);
}

if (isset($_GET['category'])) {
    $category_filter = sanitize_sql($connection, $_GET['category']);
}

if (isset($_GET['status'])) {
    $status_filter = sanitize_sql($connection, $_GET['status']);
}

// ============================================================================
// BUILD QUERY WITH FILTERS
// ============================================================================

$query = "SELECT p.*, c.category_name, s.supplier_name
          FROM products p
          INNER JOIN categories c ON p.category_id = c.category_id
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
          WHERE 1=1";

// Add search condition
if (!empty($search_term)) {
    $query .= " AND (p.product_code LIKE '%{$search_term}%' 
                OR p.product_name LIKE '%{$search_term}%' 
                OR p.description LIKE '%{$search_term}%')";
}

// Add category filter
if (!empty($category_filter)) {
    $query .= " AND p.category_id = '{$category_filter}'";
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND p.status = '{$status_filter}'";
}

$query .= " ORDER BY p.product_name ASC";

// Execute query
$products_result = db_query($connection, $query);

// ============================================================================
// FETCH CATEGORIES FOR FILTER DROPDOWN
// ============================================================================

$categories_query = "SELECT category_id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = db_query($connection, $categories_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">📦</span>
        Products Management
    </h2>
    <div class="page-actions">
        <a href="add_product.php" class="btn btn-primary">
            <span class="btn-icon">+</span>
            Add New Product
        </a>
    </div>
</div>

<!-- ================================================================ -->
<!-- SEARCH AND FILTER FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <!-- Search Input -->
                <div class="form-group col-md-4">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="🔍 Search by code, name, or description..."
                        value="<?php echo escape_html($search_term); ?>"
                    >
                </div>
                
                <!-- Category Filter -->
                <div class="form-group col-md-3">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($category_filter == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo escape_html($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="form-group col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($status_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($status_filter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="discontinued" <?php echo ($status_filter === 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                    </select>
                </div>
                
                <!-- Filter Buttons -->
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- PRODUCTS TABLE -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Product List</h3>
        <span class="card-subtitle">
            Total: <?php echo mysqli_num_rows($products_result); ?> products
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                            <?php 
                            // Determine stock status
                            $stock_class = 'success';
                            if ($product['quantity_in_stock'] <= $product['reorder_level']) {
                                $stock_class = 'danger';
                            } elseif ($product['quantity_in_stock'] <= ($product['reorder_level'] * 1.5)) {
                                $stock_class = 'warning';
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo escape_html($product['product_code']); ?></strong></td>
                                <td><?php echo escape_html($product['product_name']); ?></td>
                                <td><?php echo escape_html($product['category_name']); ?></td>
                                <td><?php echo escape_html($product['supplier_name'] ?? 'N/A'); ?></td>
                                <td><?php echo format_currency($product['unit_price']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $stock_class; ?>">
                                        <?php echo $product['quantity_in_stock']; ?> <?php echo escape_html($product['unit_of_measure']); ?>
                                    </span>
                                </td>
                                <td><?php echo $product['reorder_level']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $product['status']; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" 
                                       class="btn-action btn-edit" 
                                       title="Edit">
                                        ✏️
                                    </a>
                                    <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this product?');">
                                        🗑️
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No products found.</td>
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
