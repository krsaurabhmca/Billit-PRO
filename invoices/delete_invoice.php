<?php
/**
 * ============================================================================
 * DELETE/CANCEL INVOICE PAGE
 * ============================================================================
 * Purpose: Cancel invoice and reverse stock
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
// GET INVOICE ID FROM URL
// ============================================================================

if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    set_error_message("Invalid invoice ID.");
    redirect('invoices.php');
}

$invoice_id = sanitize_sql($connection, $_GET['id']);

// ============================================================================
// FETCH INVOICE DATA
// ============================================================================

$invoice_query = "SELECT * FROM invoices WHERE invoice_id = '{$invoice_id}' LIMIT 1";
$invoice = db_fetch_one($connection, $invoice_query);

if (!$invoice) {
    set_error_message("Invoice not found.");
    redirect('invoices.php');
}

// Check if already cancelled
if ($invoice['invoice_status'] === 'cancelled') {
    set_error_message("Invoice is already cancelled.");
    redirect('invoices.php');
}

// ============================================================================
// CANCEL INVOICE AND REVERSE STOCK
// ============================================================================

// Start transaction
mysqli_begin_transaction($connection);

try {
    // If invoice was finalized, reverse stock
    if ($invoice['invoice_status'] === 'finalized') {
        // Get all invoice items
        $items_query = "SELECT product_id, quantity FROM invoice_items WHERE invoice_id = '{$invoice_id}'";
        $items_result = db_query($connection, $items_query);
        
        // Reverse stock for each item
        while ($item = mysqli_fetch_assoc($items_result)) {
            $update_stock_query = "UPDATE products 
                                  SET quantity_in_stock = quantity_in_stock + {$item['quantity']}
                                  WHERE product_id = '{$item['product_id']}'";
            
            if (!db_execute($connection, $update_stock_query)) {
                throw new Exception("Failed to reverse stock.");
            }
        }
    }
    
    // Update invoice status to cancelled
    $cancel_query = "UPDATE invoices 
                    SET invoice_status = 'cancelled',
                        payment_status = 'unpaid',
                        amount_paid = 0,
                        amount_due = 0
                    WHERE invoice_id = '{$invoice_id}'";
    
    if (!db_execute($connection, $cancel_query)) {
        throw new Exception("Failed to cancel invoice.");
    }
    
    // Commit transaction
    mysqli_commit($connection);
    
    set_success_message("Invoice {$invoice['invoice_number']} has been cancelled and stock reversed.");
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($connection);
    set_error_message("Failed to cancel invoice: " . $e->getMessage());
}

redirect('invoices.php');
?>
