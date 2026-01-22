<?php
/**
 * ============================================================================
 * DASHBOARD / HOME PAGE
 * ============================================================================
 * Purpose: Display inventory statistics, low stock alerts, and recent transactions
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Dashboard";

// Include header
require_once 'includes/header.php';

// ============================================================================
// FETCH DASHBOARD STATISTICS
// ============================================================================

// Total number of products
$total_products_query = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
$total_products_result = db_fetch_one($connection, $total_products_query);
$total_products = $total_products_result['total'];

// Total number of categories
$total_categories_query = "SELECT COUNT(*) as total FROM categories WHERE status = 'active'";
$total_categories_result = db_fetch_one($connection, $total_categories_query);
$total_categories = $total_categories_result['total'];

// Total number of suppliers
$total_suppliers_query = "SELECT COUNT(*) as total FROM suppliers WHERE status = 'active'";
$total_suppliers_result = db_fetch_one($connection, $total_suppliers_query);
$total_suppliers = $total_suppliers_result['total'];

// Total inventory value
$total_value_query = "SELECT SUM(quantity_in_stock * unit_price) as total_value FROM products WHERE status = 'active'";
$total_value_result = db_fetch_one($connection, $total_value_query);
$total_inventory_value = $total_value_result['total_value'] ?? 0;

// Low stock products count
$low_stock_query = "SELECT COUNT(*) as total FROM products WHERE quantity_in_stock <= reorder_level AND status = 'active'";
$low_stock_result = db_fetch_one($connection, $low_stock_query);
$low_stock_count = $low_stock_result['total'];

// ============================================================================
// FETCH LOW STOCK PRODUCTS
// ============================================================================

$low_stock_products_query = "SELECT p.product_id, p.product_code, p.product_name, 
                              p.quantity_in_stock, p.reorder_level, c.category_name
                              FROM products p
                              INNER JOIN categories c ON p.category_id = c.category_id
                              WHERE p.quantity_in_stock <= p.reorder_level 
                              AND p.status = 'active'
                              ORDER BY (p.reorder_level - p.quantity_in_stock) DESC
                              LIMIT 5";
$low_stock_products = db_fetch_all($connection, $low_stock_products_query);

// ============================================================================
// FETCH RECENT STOCK TRANSACTIONS
// ============================================================================

$recent_transactions_query = "SELECT st.transaction_id, st.transaction_type, st.quantity, 
                               st.transaction_date, p.product_name, u.username
                               FROM stock_transactions st
                               INNER JOIN products p ON st.product_id = p.product_id
                               LEFT JOIN users u ON st.created_by = u.user_id
                               ORDER BY st.transaction_date DESC
                               LIMIT 5";
$recent_transactions = db_fetch_all($connection, $recent_transactions_query);

// ============================================================================
// FETCH CATEGORY STATISTICS
// ============================================================================

$category_stats_query = "SELECT c.category_name, COUNT(p.product_id) as product_count,
                          SUM(p.quantity_in_stock) as total_stock
                          FROM categories c
                          LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
                          WHERE c.status = 'active'
                          GROUP BY c.category_id, c.category_name
                          ORDER BY product_count DESC
                          LIMIT 5";
$category_stats = db_fetch_all($connection, $category_stats_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">🏠</span>
        Dashboard
    </h2>
    <p class="page-description">Welcome back, <?php echo escape_html($_SESSION['full_name']); ?>!</p>
</div>

<!-- ================================================================ -->
<!-- STATISTICS CARDS -->
<!-- ================================================================ -->
<div class="stats-grid">
    <!-- Total Products -->
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($total_products); ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    
    <!-- Total Categories -->
    <div class="stat-card stat-card-green">
        <div class="stat-icon">🏷️</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($total_categories); ?></div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
    
    <!-- Total Suppliers -->
    <div class="stat-card stat-card-purple">
        <div class="stat-icon">🏢</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($total_suppliers); ?></div>
            <div class="stat-label">Suppliers</div>
        </div>
    </div>
    
    <!-- Inventory Value -->
    <div class="stat-card stat-card-orange">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo format_currency($total_inventory_value); ?></div>
            <div class="stat-label">Inventory Value</div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="stat-card stat-card-red">
        <div class="stat-icon">⚠️</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($low_stock_count); ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- CONTENT GRID -->
<!-- ================================================================ -->
<div class="dashboard-grid">
    <!-- Low Stock Products -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">⚠️ Low Stock Alert</h3>
            <a href="<?php echo BASE_URL; ?>products/products.php" class="card-link">View All →</a>
        </div>
        <div class="card-body">
            <?php if (!empty($low_stock_products)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Reorder Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_products as $product): ?>
                                <tr>
                                    <td><?php echo escape_html($product['product_code']); ?></td>
                                    <td><?php echo escape_html($product['product_name']); ?></td>
                                    <td><?php echo escape_html($product['category_name']); ?></td>
                                    <td>
                                        <span class="badge badge-danger">
                                            <?php echo $product['quantity_in_stock']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $product['reorder_level']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">✓ All products are adequately stocked!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📊 Recent Transactions</h3>
            <a href="<?php echo BASE_URL; ?>stock/stock_history.php" class="card-link">View All →</a>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_transactions)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Date</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-<?php echo $transaction['transaction_type'] === 'stock_in' ? 'success' : 'warning'; ?>">
                                            <?php echo $transaction['transaction_type'] === 'stock_in' ? '↑ IN' : '↓ OUT'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo escape_html($transaction['product_name']); ?></td>
                                    <td><?php echo $transaction['quantity']; ?></td>
                                    <td><?php echo format_datetime($transaction['transaction_date'], 'd-m-Y H:i'); ?></td>
                                    <td><?php echo escape_html($transaction['username'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No recent transactions.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- CATEGORY STATISTICS -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📈 Category Statistics</h3>
        <a href="<?php echo BASE_URL; ?>categories/categories.php" class="card-link">Manage Categories →</a>
    </div>
    <div class="card-body">
        <?php if (!empty($category_stats)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Number of Products</th>
                            <th>Total Stock Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($category_stats as $stat): ?>
                            <tr>
                                <td><?php echo escape_html($stat['category_name']); ?></td>
                                <td><?php echo number_format($stat['product_count']); ?></td>
                                <td><?php echo number_format($stat['total_stock']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No category data available.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>
