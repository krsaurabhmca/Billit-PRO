<?php
/**
 * ============================================================================
 * DELETE CUSTOMER PAGE
 * ============================================================================
 * Purpose: Delete customer from system
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
// GET CUSTOMER ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid customer ID.");
    redirect('customers.php');
}

$customer_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// CHECK IF CUSTOMER HAS INVOICES
// ============================================================================

$invoice_check_query = "SELECT COUNT(*) as count FROM invoices WHERE customer_id = '{$customer_id}'";
$invoice_check = db_fetch_one($connection, $invoice_check_query);

if ($invoice_check['count'] > 0) {
    set_error_message("Cannot delete customer because they have {$invoice_check['count']} invoice(s).");
    redirect('customers.php');
}

// ============================================================================
// DELETE CUSTOMER
// ============================================================================

$delete_query = "DELETE FROM customers WHERE customer_id = '{$customer_id}'";

if (db_execute($connection, $delete_query)) {
    set_success_message("Customer has been successfully deleted.");
} else {
    set_error_message("Failed to delete customer.");
}

redirect('customers.php');
?>
