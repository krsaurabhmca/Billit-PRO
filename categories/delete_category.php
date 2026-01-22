<?php
/**
 * ============================================================================
 * DELETE CATEGORY PAGE
 * ============================================================================
 * Purpose: Delete category from system
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
// GET CATEGORY ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid category ID.");
    redirect('categories.php');
}

$category_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// CHECK IF CATEGORY HAS PRODUCTS
// ============================================================================

$product_check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = '{$category_id}'";
$product_check = db_fetch_one($connection, $product_check_query);

if ($product_check['count'] > 0) {
    set_error_message("Cannot delete category because it has {$product_check['count']} product(s) assigned to it.");
    redirect('categories.php');
}

// ============================================================================
// DELETE CATEGORY
// ============================================================================

$delete_query = "DELETE FROM categories WHERE category_id = '{$category_id}'";

if (db_execute($connection, $delete_query)) {
    set_success_message("Category has been successfully deleted.");
} else {
    set_error_message("Failed to delete category.");
}

redirect('categories.php');
?>
