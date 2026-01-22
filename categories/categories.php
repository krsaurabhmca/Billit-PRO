<?php
/**
 * ============================================================================
 * CATEGORIES MANAGEMENT PAGE
 * ============================================================================
 * Purpose: Display, add, edit, and delete categories
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Categories";

// Include header
require_once '../includes/header.php';

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

// Add Category
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $category_name = sanitize_sql($connection, $_POST['category_name']);
    $description = sanitize_sql($connection, $_POST['description']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    if (!empty($category_name)) {
        $insert_query = "INSERT INTO categories (category_name, description, status) 
                        VALUES ('{$category_name}', '{$description}', '{$status}')";
        
        if (db_execute($connection, $insert_query)) {
            set_success_message("Category '{$category_name}' has been successfully added.");
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to add category.");
        }
    } else {
        set_error_message("Category name is required.");
    }
}

// Update Category
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $category_id = sanitize_sql($connection, $_POST['category_id']);
    $category_name = sanitize_sql($connection, $_POST['category_name']);
    $description = sanitize_sql($connection, $_POST['description']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    if (!empty($category_name)) {
        $update_query = "UPDATE categories SET 
                        category_name = '{$category_name}',
                        description = '{$description}',
                        status = '{$status}'
                        WHERE category_id = '{$category_id}'";
        
        if (db_execute($connection, $update_query)) {
            set_success_message("Category has been successfully updated.");
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to update category.");
        }
    } else {
        set_error_message("Category name is required.");
    }
}

// ============================================================================
// FETCH ALL CATEGORIES
// ============================================================================

$categories_query = "SELECT c.*, COUNT(p.product_id) as product_count
                     FROM categories c
                     LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
                     GROUP BY c.category_id
                     ORDER BY c.category_name";
$categories_result = db_query($connection, $categories_query);

// Get category for editing if ID is provided
$edit_category = null;
if (isset($_GET['edit']) && validate_numeric($_GET['edit'])) {
    $edit_id = sanitize_sql($connection, $_GET['edit']);
    $edit_query = "SELECT * FROM categories WHERE category_id = '{$edit_id}' LIMIT 1";
    $edit_category = db_fetch_one($connection, $edit_query);
}
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">🏷️</span>
        Categories Management
    </h2>
</div>

<div class="dashboard-grid">
    <!-- Add/Edit Category Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo $edit_category ? '✏️ Edit Category' : '➕ Add New Category'; ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="category_name" class="form-label">Category Name *</label>
                    <input 
                        type="text" 
                        id="category_name" 
                        name="category_name" 
                        class="form-control" 
                        placeholder="Enter category name"
                        value="<?php echo $edit_category ? escape_html($edit_category['category_name']) : ''; ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        rows="3"
                        placeholder="Enter category description"
                    ><?php echo $edit_category ? escape_html($edit_category['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo ($edit_category && $edit_category['status'] === 'active') ? 'selected' : 'selected'; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_category && $edit_category['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-icon">✓</span>
                        <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                    </button>
                    <?php if ($edit_category): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                            <span class="btn-icon">✕</span>
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Categories</h3>
            <span class="card-subtitle">
                Total: <?php echo mysqli_num_rows($categories_result); ?> categories
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <tr>
                                    <td><?php echo $category['category_id']; ?></td>
                                    <td><strong><?php echo escape_html($category['category_name']); ?></strong></td>
                                    <td><?php echo escape_html($category['description']); ?></td>
                                    <td><?php echo $category['product_count']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $category['status']; ?>">
                                            <?php echo ucfirst($category['status']); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $category['category_id']; ?>" 
                                           class="btn-action btn-edit" 
                                           title="Edit">
                                            ✏️
                                        </a>
                                        <a href="delete_category.php?id=<?php echo $category['category_id']; ?>" 
                                           class="btn-action btn-delete" 
                                           title="Delete"
                                           onclick="return confirmDelete('Are you sure you want to delete this category?');">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
