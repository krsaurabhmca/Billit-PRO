<?php
/**
 * ============================================================================
 * PROCESS IMPORT
 * ============================================================================
 * Purpose: Handle CSV file uploads and import data into database
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file']) || !isset($_POST['type'])) {
    set_error_message("Invalid request or no file uploaded.");
    redirect('bulk_import.php');
}

$file = $_FILES['file'];
$type = $_POST['type'];

// Check for errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    set_error_message("File upload error code: " . $file['error']);
    redirect('bulk_import.php');
}

// Check file extension
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (strtolower($extension) !== 'csv') {
    set_error_message("Please upload a CSV file only.");
    redirect('bulk_import.php');
}

// Open file
$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    set_error_message("Could not open file.");
    redirect('bulk_import.php');
}

// Get header row and map columns
$headers = fgetcsv($handle);
if (!$headers) {
    set_error_message("File is empty.");
    redirect('bulk_import.php');
}

// Normalize headers (trim, lowercase)
$headers = array_map(function($h) {
    return strtolower(trim($h));
}, $headers);

$success_count = 0;
$fail_count = 0;
$row_num = 1;

mysqli_begin_transaction($connection);

try {
    while (($data = fgetcsv($handle)) !== FALSE) {
        $row_num++;
        
        // Skip empty rows
        if (empty(implode('', $data))) continue;
        
        // Map data to headers
        $row = array();
        foreach ($headers as $index => $header) {
            if (isset($data[$index])) {
                $row[$header] = trim($data[$index]);
            } else {
                $row[$header] = '';
            }
        }
        
        // Process based on type
        $result = false;
        switch ($type) {
            case 'categories':
                $result = import_category($connection, $row);
                break;
            case 'products':
                $result = import_product($connection, $row);
                break;
            case 'customers':
                $result = import_customer($connection, $row);
                break;
            case 'suppliers':
                $result = import_supplier($connection, $row);
                break;
        }
        
        if ($result) {
            $success_count++;
        } else {
            $fail_count++;
        }
    }
    
    mysqli_commit($connection);
    
    $_SESSION['import_success'] = "Import completed! Successfully imported: {$success_count}. Failed: {$fail_count}.";
    if ($fail_count > 0) {
        $_SESSION['import_error'] = "Some records failed to import. Please check for duplicates or missing required fields.";
    }
    
} catch (Exception $e) {
    mysqli_rollback($connection);
    $_SESSION['import_error'] = "Critical error during import: " . $e->getMessage();
}

fclose($handle);
redirect('bulk_import.php');


/**
 * Import Category
 */
function import_category($conn, $row) {
    if (empty($row['category_name'])) return false;
    
    $name = sanitize_sql($conn, $row['category_name']);
    $desc = isset($row['description']) ? sanitize_sql($conn, $row['description']) : '';
    $status = isset($row['status']) && in_array(strtolower($row['status']), ['active', 'inactive']) ? strtolower($row['status']) : 'active';
    
    // Check duplication
    $check = db_fetch_one($conn, "SELECT category_id FROM categories WHERE category_name = '$name'");
    if ($check) return false; // Skip if exists
    
    $query = "INSERT INTO categories (category_name, description, status) VALUES ('$name', '$desc', '$status')";
    return db_execute($conn, $query);
}

/**
 * Import Supplier
 */
function import_supplier($conn, $row) {
    if (empty($row['supplier_name'])) return false;
    
    $name = sanitize_sql($conn, $row['supplier_name']);
    $contact = isset($row['contact_person']) ? sanitize_sql($conn, $row['contact_person']) : '';
    $email = isset($row['email']) ? sanitize_sql($conn, $row['email']) : '';
    $phone = isset($row['phone']) ? sanitize_sql($conn, $row['phone']) : '';
    $addr = isset($row['address']) ? sanitize_sql($conn, $row['address']) : '';
    $city = isset($row['city']) ? sanitize_sql($conn, $row['city']) : '';
    $country = isset($row['country']) ? sanitize_sql($conn, $row['country']) : '';
    $status = isset($row['status']) ? strtolower($row['status']) : 'active';
    
    // Simple check
    $check = db_fetch_one($conn, "SELECT supplier_id FROM suppliers WHERE supplier_name = '$name'");
    if ($check) return false;
    
    $query = "INSERT INTO suppliers (supplier_name, contact_person, email, phone, address, city, country, status) 
              VALUES ('$name', '$contact', '$email', '$phone', '$addr', '$city', '$country', '$status')";
    return db_execute($conn, $query);
}

/**
 * Import Customer
 */
function import_customer($conn, $row) {
    if (empty($row['customer_name'])) return false;
    
    $name = sanitize_sql($conn, $row['customer_name']);
    $type = isset($row['customer_type']) ? strtoupper($row['customer_type']) : 'B2C';
    $contact = isset($row['contact_person']) ? sanitize_sql($conn, $row['contact_person']) : '';
    $email = isset($row['email']) ? sanitize_sql($conn, $row['email']) : '';
    $phone = isset($row['phone']) ? sanitize_sql($conn, $row['phone']) : '';
    $gstin = isset($row['gstin']) ? sanitize_sql($conn, $row['gstin']) : '';
    $pan = isset($row['pan']) ? sanitize_sql($conn, $row['pan']) : '';
    $addr = isset($row['billing_address']) ? sanitize_sql($conn, $row['billing_address']) : '';
    $city = isset($row['billing_city']) ? sanitize_sql($conn, $row['billing_city']) : '';
    $state = isset($row['billing_state']) ? sanitize_sql($conn, $row['billing_state']) : '';
    $pin = isset($row['billing_pincode']) ? sanitize_sql($conn, $row['billing_pincode']) : '';
    $status = isset($row['status']) ? strtolower($row['status']) : 'active';
    
    $query = "INSERT INTO customers (customer_name, customer_type, contact_person, email, phone, gstin, pan, 
                                     billing_address, billing_city, billing_state, billing_pincode, status) 
              VALUES ('$name', '$type', '$contact', '$email', '$phone', '$gstin', '$pan', 
                      '$addr', '$city', '$state', '$pin', '$status')";
    return db_execute($conn, $query);
}

/**
 * Import Product
 */
function import_product($conn, $row) {
    if (empty($row['product_code']) || empty($row['product_name'])) return false;
    
    $code = sanitize_sql($conn, $row['product_code']);
    
    // Check if product code exists
    $check = db_fetch_one($conn, "SELECT product_id FROM products WHERE product_code = '$code'");
    if ($check) return false; // Skip existing
    
    $name = sanitize_sql($conn, $row['product_name']);
    $desc = isset($row['description']) ? sanitize_sql($conn, $row['description']) : '';
    $price = isset($row['unit_price']) ? floatval($row['unit_price']) : 0;
    $qty = isset($row['quantity']) ? intval($row['quantity']) : 0;
    $reorder = isset($row['reorder_level']) ? intval($row['reorder_level']) : 10;
    $unit = isset($row['unit_of_measure']) ? sanitize_sql($conn, $row['unit_of_measure']) : 'pcs';
    $hsn = isset($row['hsn_code']) ? sanitize_sql($conn, $row['hsn_code']) : '';
    $gst = isset($row['gst_rate']) ? floatval($row['gst_rate']) : 0;
    
    // Resolve Category
    $cat_id = 0;
    if (!empty($row['category_name'])) {
        $cat_name = sanitize_sql($conn, $row['category_name']);
        $cat = db_fetch_one($conn, "SELECT category_id FROM categories WHERE category_name = '$cat_name'");
        if ($cat) {
            $cat_id = $cat['category_id'];
        } else {
            // Create New Category
            db_execute($conn, "INSERT INTO categories (category_name, status) VALUES ('$cat_name', 'active')");
            $cat_id = mysqli_insert_id($conn);
        }
    }
    
    // Resolve Supplier
    $sup_id = 'NULL';
    if (!empty($row['supplier_name'])) {
        $sup_name = sanitize_sql($conn, $row['supplier_name']);
        $sup = db_fetch_one($conn, "SELECT supplier_id FROM suppliers WHERE supplier_name = '$sup_name'");
        if ($sup) {
            $sup_id = "'" . $sup['supplier_id'] . "'";
        }
    }
    
    // Insert Product
    $query = "INSERT INTO products (product_code, product_name, description, category_id, supplier_id, 
                                    unit_price, quantity_in_stock, reorder_level, unit_of_measure, hsn_code, gst_rate, status) 
              VALUES ('$code', '$name', '$desc', '$cat_id', $sup_id, 
                      '$price', '$qty', '$reorder', '$unit', '$hsn', '$gst', 'active')";
                      
    return db_execute($conn, $query);
}
?>
