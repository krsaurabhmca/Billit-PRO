<?php
/**
 * AJAX Handler: Add Customer
 */
require_once 'config/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_sql($connection, $_POST['customer_name']);
    $mobile = sanitize_sql($connection, $_POST['customer_phone']);
    $address = isset($_POST['customer_address']) ? sanitize_sql($connection, $_POST['customer_address']) : '';
    
    // Basic Validation
    if (empty($name) || empty($mobile)) {
        echo json_encode(['success' => false, 'message' => 'Name and Mobile are required.']);
        exit;
    }
    
    // Check duplicates
    $check = db_fetch_one($connection, "SELECT customer_id FROM customers WHERE phone = '$mobile'");
    if ($check) {
        echo json_encode(['success' => false, 'message' => 'Customer with this mobile already exists.']);
        exit;
    }
    
    $query = "INSERT INTO customers (customer_name, phone, billing_address, status) 
              VALUES ('$name', '$mobile', '$address', 'active')";
              
    if (db_execute($connection, $query)) {
        $id = mysqli_insert_id($connection);
        echo json_encode([
            'success' => true, 
            'message' => 'Customer added!',
            'customer' => [
                'id' => $id,
                'name' => $name,
                'phone' => $mobile,
                'text' => "$mobile - $name"
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}
?>
