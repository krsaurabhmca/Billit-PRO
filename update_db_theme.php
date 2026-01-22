<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Add columns if they don't exist
// Note: "ADD COLUMN IF NOT EXISTS" is MariaDB 10.2+ / MySQL 8.0+.
// To be safe regarding version, we can check logic or suppress error.
// But lets try simple logic.

$check = db_query($connection, "SHOW COLUMNS FROM company_settings LIKE 'company_logo'");
if(mysqli_num_rows($check) == 0) {
    db_execute($connection, "ALTER TABLE company_settings ADD COLUMN company_logo VARCHAR(255) DEFAULT NULL");
    echo "Added company_logo.\n";
}

$check2 = db_query($connection, "SHOW COLUMNS FROM company_settings LIKE 'invoice_color'");
if(mysqli_num_rows($check2) == 0) {
    db_execute($connection, "ALTER TABLE company_settings ADD COLUMN invoice_color VARCHAR(20) DEFAULT '#2563eb'");
    echo "Added invoice_color.\n";
}

echo "Database updated.\n";
?>
