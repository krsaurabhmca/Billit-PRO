<?php
/**
 * ============================================================================
 * DELETE USER
 * ============================================================================
 * Purpose: Delete user account
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require Admin Role
require_role('admin');

if (isset($_GET['id']) && validate_numeric($_GET['id'])) {
    $user_id = sanitize_sql($connection, $_GET['id']);
    
    // Check if trying to delete self
    if ($user_id == $_SESSION['user_id']) {
        set_error_message("You cannot delete your own account.");
        redirect('users.php');
    }
    
    // Check if user exists
    $check = db_fetch_one($connection, "SELECT username FROM users WHERE user_id = '$user_id'");
    if (!$check) {
        set_error_message("User not found.");
        redirect('users.php');
    }
    
    $query = "DELETE FROM users WHERE user_id = '{$user_id}'";
    
    if (db_execute($connection, $query)) {
        set_success_message("User '{$check['username']}' deleted successfully.");
    } else {
        set_error_message("Failed to delete user. They may be linked to other records.");
    }
} else {
    set_error_message("Invalid request.");
}

redirect('users.php');
?>
