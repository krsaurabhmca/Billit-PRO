<?php
/**
 * ============================================================================
 * GET STOCK DETAILS API
 * ============================================================================
 * Purpose: Fetch available batches or serials for a product
 * ============================================================================
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isset($_GET['product_id']) || !isset($_GET['type'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$product_id = sanitize_sql($connection, $_GET['product_id']);
$type = $_GET['type'];

if ($type === 'batch') {
    $query = "SELECT batch_id, batch_no, expiry_date, quantity_remaining 
              FROM product_batches 
              WHERE product_id = '$product_id' AND quantity_remaining > 0 AND status = 'active' 
              ORDER BY expiry_date ASC"; // FIFO by default
    $batches = db_fetch_all($connection, $query);
    echo json_encode(['type' => 'batch', 'data' => $batches]);
} 
elseif ($type === 'serial') {
    $query = "SELECT serial_id, serial_no 
              FROM product_serials 
              WHERE product_id = '$product_id' 
              AND status = 'available' 
              AND is_sold = 0
              ORDER BY serial_id ASC";
    $serials = db_fetch_all($connection, $query);
    echo json_encode(['type' => 'serial', 'data' => $serials]);
} 
else {
    echo json_encode(['error' => 'Invalid type']);
}
?>
