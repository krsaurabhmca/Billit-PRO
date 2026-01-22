<?php
/**
 * ============================================================================
 * DELETE PRODUCT PAGE
 * ============================================================================
 * Purpose: Delete product from inventory
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Include configuration and functions
require_once '../config/config.php';
require_once '../includes/functions.php';

// Require login
require_login();

// ============================================================================
// GET PRODUCT ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid product ID.");
    redirect('products.php');
}

$product_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// FETCH PRODUCT DATA
// ============================================================================

$product_query = "SELECT product_name FROM products WHERE product_id = '{$product_id}' LIMIT 1";
$product = db_fetch_one($connection, $product_query);

if (!$product) {
    set_error_message("Product not found.");
    redirect('products.php');
}

// ============================================================================
// CHECK IF PRODUCT HAS STOCK TRANSACTIONS
// ============================================================================

$transaction_check_query = "SELECT COUNT(*) as count FROM stock_transactions WHERE product_id = '{$product_id}'";
$transaction_check = db_fetch_one($connection, $transaction_check_query);

if ($transaction_check['count'] > 0) {
    // Product has transactions, cannot delete
    set_error_message("Cannot delete product '{$product['product_name']}' because it has stock transaction history. Consider marking it as 'Discontinued' instead.");
    redirect('products.php');
}

// ============================================================================
// DELETE PRODUCT
// ============================================================================

$delete_query = "DELETE FROM products WHERE product_id = '{$product_id}'";

if (db_execute($connection, $delete_query)) {
    set_success_message("Product '{$product['product_name']}' has been successfully deleted.");
} else {
    set_error_message("Failed to delete product. Please try again.");
}

// Redirect back to products page
redirect('products.php');
?>
