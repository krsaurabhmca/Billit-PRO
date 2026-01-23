<?php
/**
 * ============================================================================
 * SETUP & WIZARD FUNCTIONS
 * ============================================================================
 */

/**
 * Check if the system is fully set up
 * returns array of missing components
 * 
 * @param mysqli $connection Database connection
 * @return array Status array with 'is_complete' (bool) and 'missing' (array)
 */
function check_setup_status($connection) {
    $status = [
        'is_complete' => true,
        'missing' => [],
        'company_configured' => false,
        'has_products' => false,
        'has_customers' => false
    ];
    
    // Check Company Settings
    $company = db_fetch_one($connection, "SELECT company_name FROM company_settings LIMIT 1");
    if ($company && !empty($company['company_name'])) {
        $status['company_configured'] = true;
    } else {
        $status['is_complete'] = false;
        $status['missing'][] = 'company_settings';
    }
    
    // Check Products
    $product = db_fetch_one($connection, "SELECT product_id FROM products WHERE status = 'active' LIMIT 1");
    if ($product) {
        $status['has_products'] = true;
    } else {
        $status['is_complete'] = false;
        $status['missing'][] = 'products';
    }
    
    // Check Customers
    $customer = db_fetch_one($connection, "SELECT customer_id FROM customers WHERE status = 'active' LIMIT 1");
    if ($customer) {
        $status['has_customers'] = true;
    } else {
        // Warning only, not strictly blocking but good for wizard
        $status['is_complete'] = false;
        $status['missing'][] = 'customers';
    }
    
    return $status;
}
