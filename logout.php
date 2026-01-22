<?php
/**
 * ============================================================================
 * LOGOUT PAGE
 * ============================================================================
 * Purpose: Destroy user session and redirect to login page
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Include configuration and functions
require_once 'config/config.php';
require_once 'includes/functions.php';

// Start session
init_session();

// ============================================================================
// DESTROY SESSION AND LOGOUT
// ============================================================================

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start a new session to set logout message
session_start();
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to login page
redirect(BASE_URL . 'login.php');
?>
