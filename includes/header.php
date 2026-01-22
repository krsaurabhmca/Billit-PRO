<?php
/**
 * ============================================================================
 * COMMON HEADER FILE
 * ============================================================================
 * Purpose: Include common header, navigation, and HTML structure
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Include configuration and functions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Ensure user is logged in
require_login();

// Get current user information
$current_user = $_SESSION['username'];
$current_role = $_SESSION['role'];
$current_user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/images/favicon.ico">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- ================================================================ -->
    <!-- NAVIGATION HEADER -->
    <!-- ================================================================ -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo and App Name -->
            <div class="header-brand">
                <h1 class="app-title">
                    <span class="app-icon">📦</span>
                    <?php echo APP_NAME; ?>
                </h1>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="main-nav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>index.php" 
                           class="nav-link <?php echo is_current_page('index.php') ? 'active' : ''; ?>"
                           title="Dashboard Overview">
                            <span class="nav-icon">🏠</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Inventory Dropdown -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link" title="Inventory Management">
                            <span class="nav-icon">📦</span>
                            <span class="nav-text">Inventory</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>products/products.php">📦 Products</a></li>
                            <li><a href="<?php echo BASE_URL; ?>categories/categories.php">🏷️ Categories</a></li>
                            <li><a href="<?php echo BASE_URL; ?>suppliers/suppliers.php">🏢 Suppliers</a></li>
                        </ul>
                    </li>
                    
                    <!-- Stock Dropdown -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link" title="Stock Transactions">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Stock</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>stock/stock_in.php">📥 Stock In</a></li>
                            <li><a href="<?php echo BASE_URL; ?>stock/stock_out.php">📤 Stock Out</a></li>
                            <li><a href="<?php echo BASE_URL; ?>stock/stock_history.php">📋 History</a></li>
                        </ul>
                    </li>
                    
                    <!-- Billing Section -->
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>customers/customers.php" 
                           class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/customers/') !== false ? 'active' : ''; ?>"
                           title="Customer Management">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Customers</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>invoices/invoices.php" 
                           class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/invoices/') !== false ? 'active' : ''; ?>"
                           title="Invoice Management">
                            <span class="nav-icon">🧾</span>
                            <span class="nav-text">Invoices</span>
                        </a>
                    </li>
                    
                    <!-- Reports Dropdown -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link" title="Reports & Analytics">
                            <span class="nav-icon">📈</span>
                            <span class="nav-text">Reports</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>reports/gst_report.php">📊 GST Report</a></li>
                        </ul>
                    </li>
                    
                    <!-- Admin Dropdown -->
                    <?php if (has_role('admin')): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link" title="Admin Settings">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">Admin</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>register.php">👤 Register User</a></li>
                            <li><a href="<?php echo BASE_URL; ?>settings/company_settings.php">🏢 Company Settings</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <!-- User Menu -->
            <div class="header-user">
                <div class="user-info">
                    <span class="user-icon">👤</span>
                    <span class="user-name"><?php echo escape_html($current_user); ?></span>
                    <span class="user-role">(<?php echo ucfirst($current_role); ?>)</span>
                </div>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout" title="Logout">
                    <span class="logout-icon">🚪</span>
                    Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- ================================================================ -->
    <!-- MAIN CONTENT AREA -->
    <!-- ================================================================ -->
    <main class="main-content">
        <div class="content-container">
            <!-- Display success/error messages -->
            <?php echo display_messages(); ?>
