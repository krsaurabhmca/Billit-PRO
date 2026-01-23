<?php
/**
 * ============================================================================
 * DATABASE CONFIGURATION FILE
 * ============================================================================
 * Purpose: Establish database connection and define application constants
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// ============================================================================
// DATABASE CONNECTION PARAMETERS
// ============================================================================
// Note: Update these values according to your local/production environment

define('DB_HOST', 'localhost');        // Database host (usually 'localhost' for XAMPP)
define('DB_USER', 'root');             // Database username (default 'root' for XAMPP)
define('DB_PASS', '');                 // Database password (default empty for XAMPP)
define('DB_NAME', 'inventory_management'); // Database name

// ============================================================================
// APPLICATION CONSTANTS
// ============================================================================

// Application name
define('APP_NAME', 'Billit 6.0');

// Developer Information
define('DEV_NAME', 'OfferPlant Technologies');
define('DEV_URL', 'https://offerplant.com');
define('DEV_EMAIL', 'ask@offerplant.com');

// Application version
define('APP_VERSION', '6.0');

// Menu Layout: 'top' or 'sidebar'
define('MENU_LAYOUT', isset($_COOKIE['menu_layout']) ? $_COOKIE['menu_layout'] : 'sidebar');

// Base URL (update this to match your installation path)
define('BASE_URL', 'http://localhost/billit/');

// Timezone setting
date_default_timezone_set('Asia/Kolkata');

// Session configuration
define('SESSION_TIMEOUT', 3600); // Session timeout in seconds (1 hour)

// Pagination settings
define('RECORDS_PER_PAGE', 10);

// Low stock alert threshold multiplier
define('LOW_STOCK_MULTIPLIER', 1.0); // Alert when stock <= reorder_level * multiplier

// Company GST Configuration (will be loaded from database)
define('COMPANY_STATE_CODE', '27'); // Default: Maharashtra (27)
define('DEFAULT_GST_RATE', 18.00); // Default GST rate percentage

// Invoice Configuration
define('INVOICE_PREFIX', 'INV'); // Invoice number prefix
define('INVOICE_TERMS', 'Payment due within 30 days'); // Default terms

// ============================================================================
// ESTABLISH DATABASE CONNECTION
// ============================================================================

/**
 * Create mysqli connection using procedural approach
 * This connection will be used throughout the application
 */
$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ============================================================================
// CONNECTION ERROR HANDLING
// ============================================================================

/**
 * Check if connection was successful
 * If connection fails, display error and stop execution
 */
if (!$connection) {
    // Log error for debugging (in production, log to file instead of displaying)
    $error_message = "Database Connection Failed: " . mysqli_connect_error();
    $error_code = mysqli_connect_errno();
    
    // Display user-friendly error message
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Connection Error</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: #f5f5f5; 
                padding: 50px; 
                text-align: center; 
            }
            .error-box {
                background: white;
                border-left: 4px solid #e74c3c;
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h2 { color: #e74c3c; margin-top: 0; }
            .error-code { 
                background: #f8f9fa; 
                padding: 10px; 
                border-radius: 4px; 
                margin: 15px 0;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>⚠️ Database Connection Error</h2>
            <p>Unable to connect to the database server.</p>
            <div class='error-code'>
                <strong>Error Code:</strong> {$error_code}<br>
                <strong>Error Message:</strong> {$error_message}
            </div>
            <p><strong>Troubleshooting Steps:</strong></p>
            <ul style='text-align: left;'>
                <li>Verify that XAMPP MySQL service is running</li>
                <li>Check database credentials in config/config.php</li>
                <li>Ensure database 'inventory_management' exists</li>
                <li>Run database_setup.sql to create the database</li>
            </ul>
        </div>
    </body>
    </html>
    ");
}

// ============================================================================
// SET CHARACTER SET
// ============================================================================

/**
 * Set character set to UTF-8 for proper handling of special characters
 * This prevents issues with international characters and special symbols
 */
if (!mysqli_set_charset($connection, "utf8mb4")) {
    die("Error setting character set utf8mb4: " . mysqli_error($connection));
}

// ============================================================================
// CONNECTION SUCCESS
// ============================================================================

/**
 * If we reach this point, database connection is successful
 * The $connection variable is now available for use throughout the application
 * 
 * Usage in other files:
 * require_once 'config/config.php';
 * $result = mysqli_query($connection, "SELECT * FROM products");
 */

// Optional: Uncomment below for debugging during development
// echo "<!-- Database connection established successfully -->";

?>
