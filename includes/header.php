<?php
/**
 * ============================================================================
 * COMMON HEADER FILE
 * ============================================================================
 * Purpose: Include common header, navigation, and HTML structure
 * Supports: Top menu and Sidebar menu layouts
 * Author: Billit Pro
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

// Get menu layout preference
$menu_layout = MENU_LAYOUT;
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
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <style>
    /* DataTables Overrides */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_processing, 
    .dataTables_wrapper .dataTables_paginate {
        color: var(--gray-600);
        margin-bottom: 15px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        padding: 5px 10px;
    }
    /* Compact DataTables */
    table.dataTable {
        width: 100% !important;
        margin-top: 5px !important;
        margin-bottom: 5px !important;
        border-collapse: collapse !important;
    }
    table.dataTable thead th {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0 !important;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
        padding: 8px 12px !important; /* Compact Padding */
        vertical-align: middle;
    }
    table.dataTable tbody td {
        padding: 6px 12px !important; /* Compact Padding */
        font-size: 13.5px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    table.dataTable tbody tr:hover {
        background-color: #f1f5f9; /* Row Hover Effect */
        cursor: default;
    }
    
    /* Control Wrapper Spacing */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 10px;
        font-size: 13px;
        color: #64748b;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 4px 8px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #3b82f6; }
    
    .dt-buttons { margin-bottom: 10px; }
    .dt-buttons .dt-button {
        background: #fff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 12px;
        padding: 4px 10px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .dt-buttons .dt-button:hover { background: #f8fafc; color: #1e293b; }
    </style>
</head>
<body class="layout-<?php echo $menu_layout; ?>">
    
    <?php if ($menu_layout === 'sidebar'): ?>
    <!-- ================================================================ -->
    <!-- SIDEBAR LAYOUT -->
    <!-- ================================================================ -->
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-brand">
                <span class="brand-icon">📦</span>
                <span class="brand-text"><?php echo APP_NAME; ?></span>
            </h1>
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>index.php" 
                       class="sidebar-link <?php echo is_current_page('index.php') ? 'active' : ''; ?>">
                        <span class="sidebar-icon">🏠</span>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="sidebar-heading">Sales</li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>invoices/invoices.php" class="sidebar-link">
                        <span class="sidebar-icon">🧾</span>
                        <span class="sidebar-text">All Invoices</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>invoices/create_invoice.php" class="sidebar-link">
                        <span class="sidebar-icon">➕</span>
                        <span class="sidebar-text">Create Invoice</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>customers/customers.php" class="sidebar-link">
                        <span class="sidebar-icon">👥</span>
                        <span class="sidebar-text">Customers</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>invoices/sales_returns_list.php" class="sidebar-link">
                        <span class="sidebar-icon">↩️</span>
                        <span class="sidebar-text">Sales Return</span>
                    </a>
                </li>

                <li class="sidebar-heading">Purchase</li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>purchases/index.php" class="sidebar-link">
                        <span class="sidebar-icon">📋</span>
                        <span class="sidebar-text">Manage Purchases</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>purchases/create_purchase.php" class="sidebar-link">
                        <span class="sidebar-icon">📥</span>
                        <span class="sidebar-text">Record Purchase</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>suppliers/suppliers.php" class="sidebar-link">
                        <span class="sidebar-icon">🏢</span>
                        <span class="sidebar-text">Suppliers</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>purchases/purchase_returns_list.php" class="sidebar-link">
                        <span class="sidebar-icon">📤</span>
                        <span class="sidebar-text">Purchase Return</span>
                    </a>
                </li>
                
                <li class="sidebar-heading">Inventory</li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>products/products.php" class="sidebar-link">
                        <span class="sidebar-icon">📦</span>
                        <span class="sidebar-text">Products</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>categories/categories.php" class="sidebar-link">
                        <span class="sidebar-icon">🏷️</span>
                        <span class="sidebar-text">Categories</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>imports/bulk_import.php" class="sidebar-link">
                        <span class="sidebar-icon">📤</span>
                        <span class="sidebar-text">Bulk Import</span>
                    </a>
                </li>
                
                <li class="sidebar-heading">Reports</li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>reports/index.php" class="sidebar-link">
                        <span class="sidebar-icon">📊</span>
                        <span class="sidebar-text">Analytics Hub</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>reports/batch_report.php" class="sidebar-link">
                        <span class="sidebar-icon">💊</span>
                        <span class="sidebar-text">Batch Report</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>reports/tally_report.php" class="sidebar-link">
                        <span class="sidebar-icon">🔢</span>
                        <span class="sidebar-text">Product Tally</span>
                    </a>
                </li>
                
                <?php if (has_role('admin')): ?>
                <li class="sidebar-heading">Admin</li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>users/users.php" class="sidebar-link">
                        <span class="sidebar-icon">👤</span>
                        <span class="sidebar-text">Users</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>settings/company_settings.php" class="sidebar-link">
                        <span class="sidebar-icon">⚙️</span>
                        <span class="sidebar-text">Settings</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>settings/smtp_settings.php" class="sidebar-link">
                        <span class="sidebar-icon">📧</span>
                        <span class="sidebar-text">SMTP Settings</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>reports/access_log.php" class="sidebar-link">
                        <span class="sidebar-icon">🛡️</span>
                        <span class="sidebar-text">Access Logs</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>backup/backup.php" class="sidebar-link">
                        <span class="sidebar-icon">💾</span>
                        <span class="sidebar-text">Backup & Restore</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="sidebar-heading">Support</li>
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>help.php" class="sidebar-link">
                        <span class="sidebar-icon">❓</span>
                        <span class="sidebar-text">Help Guide</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <button onclick="switchLayout('top')" class="layout-switch" title="Switch to Top Menu">
                ↔️ Top Menu
            </button>
        </div>
    </aside>
    
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Bar for Sidebar Layout -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <h2 class="topbar-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h2>
            </div>
            <div class="topbar-right">
                <a href="<?php echo BASE_URL; ?>help.php" class="header-icon-btn" title="Help / Documentation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </a>
                <div class="user-info">
                    <span class="user-avatar" style="display:flex; align-items:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <span class="user-name"><?php echo escape_html($current_user); ?></span>
                    <span class="user-role badge badge-<?php echo $current_role; ?>"><?php echo ucfirst($current_role); ?></span>
                </div>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </a>
            </div>
        </header>
        
        <!-- Main Content Area -->
        <main class="main-content sidebar-layout">
            <div class="content-container">
                <!-- Display success/error messages -->
                <?php echo display_messages(); ?>
    
    <?php else: ?>
    <!-- ================================================================ -->
    <!-- TOP MENU LAYOUT -->
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
                           class="nav-link <?php echo is_current_page('index.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">🏠</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Sales -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">
                            <span class="nav-icon">🧾</span>
                            <span class="nav-text">Sales</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>invoices/create_invoice.php">➕ Create Invoice</a></li>
                            <li><a href="<?php echo BASE_URL; ?>invoices/invoices.php">📋 All Invoices</a></li>
                            <li><a href="<?php echo BASE_URL; ?>customers/customers.php">👥 Customers</a></li>
                            <li><a href="<?php echo BASE_URL; ?>invoices/sales_returns_list.php">↩️ Sales Return</a></li>
                        </ul>
                    </li>

                    <!-- Purchase -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">
                            <span class="nav-icon">📥</span>
                            <span class="nav-text">Purchase</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>purchases/create_purchase.php">➕ Record Purchase</a></li>
                            <li><a href="<?php echo BASE_URL; ?>purchases/index.php">📋 Purchase List</a></li>
                            <li><a href="<?php echo BASE_URL; ?>suppliers/suppliers.php">🏢 Suppliers</a></li>
                            <li><a href="<?php echo BASE_URL; ?>purchases/purchase_returns_list.php">📤 Purchase Return</a></li>
                        </ul>
                    </li>

                    <!-- Inventory -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">
                            <span class="nav-icon">📦</span>
                            <span class="nav-text">Inventory</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>products/products.php">📦 Products</a></li>
                            <li><a href="<?php echo BASE_URL; ?>categories/categories.php">🏷️ Categories</a></li>
                            <li><a href="<?php echo BASE_URL; ?>imports/bulk_import.php">📥 Bulk Import</a></li>
                        </ul>
                    </li>
                    
                    <!-- Reports -->
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reports/index.php" 
                           class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'active' : ''; ?>">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Reports</span>
                        </a>
                    </li>
                    
                    <!-- Admin -->
                    <?php if (has_role('admin')): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">Admin</span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>users/users.php">👤 Users</a></li>
                            <li><a href="<?php echo BASE_URL; ?>settings/company_settings.php">🏢 Settings</a></li>
                            <li><a href="<?php echo BASE_URL; ?>settings/smtp_settings.php">📧 SMTP</a></li>
                            <li><a href="<?php echo BASE_URL; ?>settings/smtp_settings.php">📧 SMTP</a></li>
                            <li><a href="<?php echo BASE_URL; ?>reports/access_log.php">🛡️ Logs</a></li>
                            <li><a href="<?php echo BASE_URL; ?>backup/backup.php">💾 Backup</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Help -->
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>help.php" class="nav-link">
                            <span class="nav-icon">❓</span>
                            <span class="nav-text">Help</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- User Menu -->
            <div class="header-user">
                <a href="<?php echo BASE_URL; ?>help.php" class="header-icon-btn" title="Help / Documentation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </a>
                
                <div class="user-info">
                    <span class="user-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <span class="user-name"><?php echo escape_html($current_user); ?></span>
                    <span class="user-role" style="font-size:11px; opacity:0.7;">(<?php echo ucfirst($current_role); ?>)</span>
                </div>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout" title="Logout" style="padding: 6px 12px; font-size:13px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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
    <?php endif; ?>

<script>
function switchLayout(layout) {
    document.cookie = "menu_layout=" + layout + ";path=/;max-age=31536000";
    window.location.reload();
}

function toggleSidebar() {
    document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
}

// Restore sidebar state
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
});
</script>
