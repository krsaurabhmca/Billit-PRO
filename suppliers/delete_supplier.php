<?php
/**
 * ============================================================================
 * DELETE SUPPLIER PAGE
 * ============================================================================
 * Purpose: Delete supplier from system
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
// GET SUPPLIER ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid supplier ID.");
    redirect('suppliers.php');
}

$supplier_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// CHECK IF SUPPLIER HAS PRODUCTS
// ============================================================================

$product_check_query = "SELECT COUNT(*) as count FROM products WHERE supplier_id = '{$supplier_id}'";
$product_check = db_fetch_one($connection, $product_check_query);

if ($product_check['count'] > 0) {
    set_error_message("Cannot delete supplier because it has {$product_check['count']} product(s) assigned to it.");
    redirect('suppliers.php');
}

// ============================================================================
// DELETE SUPPLIER
// ============================================================================

$delete_query = "DELETE FROM suppliers WHERE supplier_id = '{$supplier_id}'";

if (db_execute($connection, $delete_query)) {
    set_success_message("Supplier has been successfully deleted.");
} else {
    set_error_message("Failed to delete supplier.");
}

redirect('suppliers.php');
?>
