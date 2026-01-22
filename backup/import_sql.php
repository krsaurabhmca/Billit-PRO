<?php
/**
 * ============================================================================
 * IMPORT/RESTORE DATABASE FROM SQL
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_role('admin');

// Increase limits
set_time_limit(600);
ini_set('memory_limit', '512M');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    
    $file = $_FILES['backup_file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        set_error_message("File upload failed with error code: " . $file['error']);
        redirect('backup.php');
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        set_error_message("Invalid file type. Please upload a .sql file.");
        redirect('backup.php');
    }
    
    $filename = $file['tmp_name'];
    
    // Disable Foreign Key Checks to allow dropping tables out of order
    db_execute($connection, "SET FOREIGN_KEY_CHECKS = 0");
    
    // Read file line by line
    $fp = fopen($filename, 'r');
    $templine = '';
    $success_count = 0;
    $error_count = 0;
    
    while (($line = fgets($fp)) !== false) {
        // Skip comments and empty lines
        if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 1) == '#') {
            continue;
        }
        
        $templine .= $line;
        
        // If line ends with semicolon, execute query
        if (substr(trim($line), -1, 1) == ';') {
            if (mysqli_query($connection, $templine)) {
                $success_count++;
            } else {
                $error_count++;
                // Option: Stop on error? Or Continue?
                // For restore, maybe better to continue and try to restore as much as possible, 
                // but usually one error cascades.
                // We'll log it.
                error_log("Import Error: " . mysqli_error($connection) . " - Query: " . substr($templine, 0, 100));
            }
            $templine = ''; // Reset buffer
        }
    }
    
    fclose($fp);
    
    // Re-enable Foreign Key Checks
    db_execute($connection, "SET FOREIGN_KEY_CHECKS = 1");
    
    if ($error_count == 0) {
        log_activity($connection, "Database Restore", "Restored database from file: " . $file['name']);
        set_success_message("Database restored successfully! ($success_count queries executed)");
    } else {
        log_activity($connection, "Database Restore Failed", "Errors: $error_count");
        set_error_message("Restore completed with $error_count errors. Check logs.");
    }
    
    redirect('backup.php');
} else {
    redirect('backup.php');
}
?>
