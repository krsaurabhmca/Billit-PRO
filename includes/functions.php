<?php
/**
 * ============================================================================
 * COMMON FUNCTIONS LIBRARY
 * ============================================================================
 * Purpose: Reusable utility functions for the inventory management system
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// ============================================================================
// SESSION MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Start session if not already started
 * 
 * @return void
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * 
 * @return boolean True if user is logged in, false otherwise
 */
function is_logged_in() {
    init_session();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require user to be logged in, redirect to login page if not
 * 
 * @return void
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = "Please login to access this page.";
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check (admin, manager, staff)
 * @return boolean True if user has the role, false otherwise
 */
function has_role($role) {
    init_session();
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require user to have specific role, redirect if not authorized
 * 
 * @param string|array $allowed_roles Single role or array of allowed roles
 * @return void
 */
function require_role($allowed_roles) {
    require_login();
    
    // Convert single role to array for uniform processing
    if (!is_array($allowed_roles)) {
        $allowed_roles = array($allowed_roles);
    }
    
    // Check if user has any of the allowed roles
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error_message'] = "You don't have permission to access this page.";
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

// ============================================================================
// INPUT SANITIZATION FUNCTIONS
// ============================================================================

/**
 * Sanitize string input to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);                          // Remove whitespace
    $data = stripslashes($data);                  // Remove backslashes
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Convert special chars
    return $data;
}

/**
 * Sanitize data for SQL queries (use with mysqli_real_escape_string)
 * 
 * @param mysqli $connection Database connection
 * @param string $data Data to sanitize
 * @return string Sanitized data safe for SQL queries
 */
function sanitize_sql($connection, $data) {
    return mysqli_real_escape_string($connection, trim($data));
}

/**
 * Validate email address format
 * 
 * @param string $email Email address to validate
 * @return boolean True if valid email, false otherwise
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate numeric input
 * 
 * @param mixed $value Value to validate
 * @return boolean True if numeric, false otherwise
 */
function validate_numeric($value) {
    return is_numeric($value);
}

// ============================================================================
// DATABASE HELPER FUNCTIONS
// ============================================================================

/**
 * Execute SELECT query and return results as associative array
 * 
 * @param mysqli $connection Database connection
 * @param string $query SQL query to execute
 * @return array|false Array of results or false on failure
 */
function db_query($connection, $query) {
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        // Log error (in production, log to file)
        error_log("Database Query Error: " . mysqli_error($connection));
        return false;
    }
    
    return $result;
}

/**
 * Fetch single row from query result
 * 
 * @param mysqli $connection Database connection
 * @param string $query SQL query to execute
 * @return array|null Associative array of row data or null if no results
 */
function db_fetch_one($connection, $query) {
    $result = db_query($connection, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Fetch all rows from query result
 * 
 * @param mysqli $connection Database connection
 * @param string $query SQL query to execute
 * @return array Array of associative arrays
 */
function db_fetch_all($connection, $query) {
    $result = db_query($connection, $query);
    $rows = array();
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    
    return $rows;
}

/**
 * Execute INSERT, UPDATE, or DELETE query
 * 
 * @param mysqli $connection Database connection
 * @param string $query SQL query to execute
 * @return boolean True on success, false on failure
 */
function db_execute($connection, $query) {
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        error_log("Database Execute Error: " . mysqli_error($connection));
        return false;
    }
    
    return true;
}

/**
 * Get last inserted ID
 * 
 * @param mysqli $connection Database connection
 * @return int Last inserted ID
 */
function db_insert_id($connection) {
    return mysqli_insert_id($connection);
}

/**
 * Get number of affected rows from last query
 * 
 * @param mysqli $connection Database connection
 * @return int Number of affected rows
 */
function db_affected_rows($connection) {
    return mysqli_affected_rows($connection);
}

// ============================================================================
// MESSAGE/ALERT FUNCTIONS
// ============================================================================

/**
 * Set success message in session
 * 
 * @param string $message Success message to display
 * @return void
 */
function set_success_message($message) {
    init_session();
    $_SESSION['success_message'] = $message;
}

/**
 * Set error message in session
 * 
 * @param string $message Error message to display
 * @return void
 */
function set_error_message($message) {
    init_session();
    $_SESSION['error_message'] = $message;
}

/**
 * Display and clear success message from session
 * 
 * @return string HTML for success message or empty string
 */
function display_success_message() {
    init_session();
    
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        
        return "<div class='alert alert-success'>
                    <span class='alert-icon'>✓</span>
                    <span class='alert-message'>{$message}</span>
                </div>";
    }
    
    return '';
}

/**
 * Display and clear error message from session
 * 
 * @return string HTML for error message or empty string
 */
function display_error_message() {
    init_session();
    
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        
        return "<div class='alert alert-error'>
                    <span class='alert-icon'>✕</span>
                    <span class='alert-message'>{$message}</span>
                </div>";
    }
    
    return '';
}

/**
 * Display all messages (success and error)
 * 
 * @return string HTML for all messages
 */
function display_messages() {
    return display_success_message() . display_error_message();
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Format currency value
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency symbol (default: $)
 * @return string Formatted currency string
 */
function format_currency($amount, $currency = '₹') {
    return $currency . number_format($amount, 2);
}

/**
 * Format date for display
 * 
 * @param string $date Date string to format
 * @param string $format Date format (default: d-m-Y)
 * @return string Formatted date string
 */
function format_date($date, $format = 'd-m-Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * 
 * @param string $datetime Datetime string to format
 * @param string $format Datetime format (default: d-m-Y H:i:s)
 * @return string Formatted datetime string
 */
function format_datetime($datetime, $format = 'd-m-Y H:i:s') {
    return date($format, strtotime($datetime));
}

/**
 * Redirect to another page
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    if (headers_sent()) {
        // If headers already sent, use JavaScript redirect
        echo "<script>window.location.href = '{$url}';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url={$url}'></noscript>";
    } else {
        header("Location: " . $url);
    }
    exit();
}

/**
 * Generate random string for tokens/codes
 * 
 * @param int $length Length of random string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $random_string;
}

/**
 * Get current page name
 * 
 * @return string Current page filename
 */
function get_current_page() {
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Check if current page matches given page name
 * 
 * @param string $page_name Page name to check
 * @return boolean True if current page matches, false otherwise
 */
function is_current_page($page_name) {
    return get_current_page() === $page_name;
}

/**
 * Escape output for safe HTML display
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Debug function to print variable (use only in development)
 * 
 * @param mixed $var Variable to debug
 * @param boolean $die Whether to stop execution after printing
 * @return void
 */
function debug_print($var, $die = false) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
    
    if ($die) {
        die();
    }
}

// ============================================================================
// GST CALCULATION FUNCTIONS
// ============================================================================

/**
 * Calculate GST amounts based on state codes
 * 
 * @param float $amount Taxable amount
 * @param float $gst_rate GST rate percentage (e.g., 18.00)
 * @param string $company_state_code Company's state code
 * @param string $customer_state_code Customer's state code
 * @return array Array with cgst, sgst, igst amounts
 */
function calculate_gst($amount, $gst_rate, $company_state_code, $customer_state_code) {
    $gst_amount = ($amount * $gst_rate) / 100;
    
    // Check if intra-state (same state) or inter-state (different state)
    if ($company_state_code === $customer_state_code) {
        // Intra-state: CGST + SGST
        return array(
            'cgst_rate' => $gst_rate / 2,
            'cgst_amount' => $gst_amount / 2,
            'sgst_rate' => $gst_rate / 2,
            'sgst_amount' => $gst_amount / 2,
            'igst_rate' => 0,
            'igst_amount' => 0,
            'total_tax' => $gst_amount
        );
    } else {
        // Inter-state: IGST
        return array(
            'cgst_rate' => 0,
            'cgst_amount' => 0,
            'sgst_rate' => 0,
            'sgst_amount' => 0,
            'igst_rate' => $gst_rate,
            'igst_amount' => $gst_amount,
            'total_tax' => $gst_amount
        );
    }
}

/**
 * Get total GST amount
 * 
 * @param float $amount Taxable amount
 * @param float $gst_rate GST rate percentage
 * @return float Total GST amount
 */
function get_gst_amount($amount, $gst_rate) {
    return ($amount * $gst_rate) / 100;
}

/**
 * Calculate invoice totals with GST
 * 
 * @param float $subtotal Subtotal before discount
 * @param float $discount_value Discount value (percentage or amount)
 * @param string $discount_type Type of discount ('percentage' or 'amount')
 * @param float $gst_rate GST rate percentage
 * @param string $company_state_code Company's state code
 * @param string $customer_state_code Customer's state code
 * @return array Array with all calculated amounts
 */
function calculate_invoice_totals($subtotal, $discount_value, $discount_type, $gst_rate, $company_state_code, $customer_state_code) {
    // Calculate discount amount
    if ($discount_type === 'percentage') {
        $discount_amount = ($subtotal * $discount_value) / 100;
    } else {
        $discount_amount = $discount_value;
    }
    
    // Calculate taxable amount
    $taxable_amount = $subtotal - $discount_amount;
    
    // Calculate GST
    $gst_details = calculate_gst($taxable_amount, $gst_rate, $company_state_code, $customer_state_code);
    
    // Calculate total
    $total_amount = $taxable_amount + $gst_details['total_tax'];
    
    // Round off
    $rounded_total = round($total_amount);
    $round_off = $rounded_total - $total_amount;
    
    return array(
        'subtotal' => $subtotal,
        'discount_amount' => $discount_amount,
        'taxable_amount' => $taxable_amount,
        'cgst_rate' => $gst_details['cgst_rate'],
        'cgst_amount' => $gst_details['cgst_amount'],
        'sgst_rate' => $gst_details['sgst_rate'],
        'sgst_amount' => $gst_details['sgst_amount'],
        'igst_rate' => $gst_details['igst_rate'],
        'igst_amount' => $gst_details['igst_amount'],
        'total_tax' => $gst_details['total_tax'],
        'total_amount' => $total_amount,
        'round_off' => $round_off,
        'final_amount' => $rounded_total
    );
}

/**
 * Generate next invoice number
 * 
 * @param mysqli $connection Database connection
 * @param string $prefix Invoice prefix
 * @return string Generated invoice number
 */
function generate_invoice_number($connection, $prefix = 'INV') {
    // Get the last invoice number
    $query = "SELECT invoice_number FROM invoices 
              WHERE invoice_number LIKE '{$prefix}%' 
              ORDER BY invoice_id DESC LIMIT 1";
    
    $result = db_fetch_one($connection, $query);
    
    if ($result) {
        // Extract number from last invoice
        $last_number = (int) str_replace($prefix, '', $result['invoice_number']);
        $next_number = $last_number + 1;
    } else {
        // First invoice
        $next_number = 1;
    }
    
    // Format: INV0001, INV0002, etc.
    return $prefix . str_pad($next_number, 4, '0', STR_PAD_LEFT);
}

/**
 * Get Indian state name from state code
 * 
 * @param string $state_code Two-digit state code
 * @return string State name
 */
function get_state_name($state_code) {
    $states = array(
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh'
    );
    
    return isset($states[$state_code]) ? $states[$state_code] : 'Unknown';
}

/**
 * Get all Indian states with codes
 * 
 * @return array Array of state codes and names
 */
function get_indian_states() {
    return array(
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh'
    );
}

/**
 * Validate GSTIN format
 * 
 * @param string $gstin GSTIN to validate
 * @return boolean True if valid, false otherwise
 */
function validate_gstin($gstin) {
    // GSTIN format: 2 digits state code + 10 chars PAN + 1 char entity + 1 char Z + 1 check digit
    // Example: 27AAAAA0000A1Z5
    $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
    return preg_match($pattern, $gstin) === 1;
}

/**
 * Convert number to words (for invoice amount in words)
 * 
 * @param float $number Number to convert
 * @return string Number in words
 */
function number_to_words($number) {
    $number = (int) $number;
    
    $ones = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen'
    );
    
    $tens = array(
        0 => '', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    );
    
    if ($number == 0) {
        return 'Zero';
    }
    
    $words = '';
    
    // Crores
    if ($number >= 10000000) {
        $words .= number_to_words(floor($number / 10000000)) . ' Crore ';
        $number %= 10000000;
    }
    
    // Lakhs
    if ($number >= 100000) {
        $words .= number_to_words(floor($number / 100000)) . ' Lakh ';
        $number %= 100000;
    }
    
    // Thousands
    if ($number >= 1000) {
        $words .= number_to_words(floor($number / 1000)) . ' Thousand ';
        $number %= 1000;
    }
    
    // Hundreds
    if ($number >= 100) {
        $words .= $ones[floor($number / 100)] . ' Hundred ';
        $number %= 100;
    }
    
    // Tens and ones
    if ($number >= 20) {
        $words .= $tens[floor($number / 10)] . ' ';
        $number %= 10;
    }
    
    if ($number > 0) {
        $words .= $ones[$number] . ' ';
    }
    
    return trim($words);
}

// ============================================================================
// LOGGING & EMAIL FUNCTIONS
// ============================================================================

/**
 * Log user activity
 * 
 * @param mysqli $connection Database connection
 * @param string $action Action performed
 * @param string $details Additional details
 * @return void
 */
function log_activity($connection, $action, $details = '') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_POST['username']) ? $_POST['username'] : 'System');
    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'];
    
    $action = sanitize_sql($connection, $action);
    $details = sanitize_sql($connection, $details);
    $username = sanitize_sql($connection, $username);
    $agent = sanitize_sql($connection, $agent);
    
    $query = "INSERT INTO access_logs (user_id, username, action, details, ip_address, user_agent) 
              VALUES ($user_id, '$username', '$action', '$details', '$ip', '$agent')";
    db_execute($connection, $query);
}

/**
 * Send Email using SMTP Settings
 * 
 * @param mysqli $connection Database connection
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @return array ['success' => bool, 'message' => string]
 */
function send_email($connection, $to, $subject, $body) {
    // Fetch SMTP Settings
    $settings = db_fetch_one($connection, "SELECT * FROM smtp_settings WHERE status = 'active' LIMIT 1");
    
    if (!$settings) {
        return ['success' => false, 'message' => 'SMTP settings not configured.'];
    }
    
    // In a real environment with Composer, we would use PHPMailer here.
    // Since we are in a restrained environment, we will use PHP's mail() function 
    // but try to configure it with ini_set if possible, or simulate the send.
    // Ideally, for XAMPP locally, you need to configure sendmail.ini.
    
    // However, to make this "work" as requested per user requirements for "SMTP Setting",
    // we will rely on the standard mail() function but return success to simulate the flow
    // if we cannot actually connect to external SMTP without PHPMailer library.
    
    // Check if PHPMailer class exists (if user installed it manually)
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $settings['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $settings['username'];
            $mail->Password = $settings['password'];
            $mail->SMTPSecure = $settings['encryption'];
            $mail->Port = $settings['port'];
            
            $mail->setFrom($settings['from_email'], $settings['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"];
        }
    } else {
        // Fallback to PHP native mail()
        // Notes: This requires local mail server or sendmail configuration in php.ini
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $settings['from_name'] . ' <' . $settings['from_email'] . '>' . "\r\n";
        
        if (mail($to, $subject, $body, $headers)) {
            return ['success' => true, 'message' => 'Email sent via PHP mail().'];
        } else {
            return ['success' => false, 'message' => 'Failed to send email. Check server mail configuration.'];
        }
    }
}
?>
