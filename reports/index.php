<?php
/**
 * ============================================================================
 * REPORTS DASHBOARD
 * ============================================================================
 * Purpose: Central hub for all system reports
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

$page_title = "Reports Dashboard";
require_once '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📊</span>
            Reports & Analytics
        </h2>
        <p class="page-description">Comprehensive financial and inventory reports.</p>
    </div>
</div>

<div class="dashboard-grid">
    
    <!-- Sales Report -->
    <a href="sales_report.php" class="report-card">
        <div class="report-icon icon-green">💰</div>
        <div class="report-info">
            <h3>Sales Report</h3>
            <p>View sales history, revenue details, and invoice summaries.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <!-- Purchase Report -->
    <a href="purchase_report.php" class="report-card">
        <div class="report-icon icon-blue">📥</div>
        <div class="report-info">
            <h3>Purchase Report</h3>
            <p>Track stock purchases, expenses, and inventory additions.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <!-- Profit & Loss -->
    <a href="profit_loss.php" class="report-card">
        <div class="report-icon icon-gold">📈</div>
        <div class="report-info">
            <h3>Profit & Loss</h3>
            <p>Analyze profitability, margins, and financial health.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <!-- Stock Report -->
    <a href="stock_report.php" class="report-card">
        <div class="report-icon icon-purple">📦</div>
        <div class="report-info">
            <h3>Current Stock</h3>
            <p>Detailed view of current inventory valuations and quantities.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <!-- Supplier Stock -->
    <a href="supplier_stock.php" class="report-card">
        <div class="report-icon icon-indigo">🏢</div>
        <div class="report-info">
            <h3>Supplier Wise Stock</h3>
            <p>Inventory breakdown associated with specific suppliers.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <!-- Stock Alerts -->
    <a href="stock_alert.php" class="report-card">
        <div class="report-icon icon-red">⚠️</div>
        <div class="report-info">
            <h3>Low Stock Alerts</h3>
            <p>Identify products running low and needing reorder.</p>
        </div>
        <div class="report-action">View →</div>
    </a>

     <!-- Purchase Order -->
     <a href="purchase_order.php" class="report-card">
        <div class="report-icon icon-teal">📋</div>
        <div class="report-info">
            <h3>Purchase Orders</h3>
            <p>Manage and generate purchase orders for suppliers.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <!-- GST Report -->
    <a href="gst_report.php" class="report-card">
        <div class="report-icon icon-slate">🏛️</div>
        <div class="report-info">
            <h3>GST Report</h3>
            <p>Tax collection reports for GSTR filing (Input/Output).</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    
    <?php if (has_role('admin')): ?>
    <!-- Access Log -->
    <a href="access_log.php" class="report-card">
        <div class="report-icon icon-gray">🛡️</div>
        <div class="report-info">
            <h3>Access Logs</h3>
            <p>System security and user activity audit trails.</p>
        </div>
        <div class="report-action">View →</div>
    </a>
    <?php endif; ?>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.report-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    text-decoration: none;
    color: inherit;
    border: 1px solid var(--gray-200);
    transition: all 0.2s ease;
    cursor: pointer;
}

.report-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-color);
}

.report-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.report-info h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--gray-800);
}

.report-info p {
    margin: 0;
    font-size: 13px;
    color: var(--gray-500);
    line-height: 1.4;
}

.report-action {
    display: none; /* Hidden by default, nicer UI */
}

/* Icon Colors */
.icon-green { background: #dcfce7; color: #16a34a; }
.icon-blue { background: #dbeafe; color: #2563eb; }
.icon-gold { background: #fef9c3; color: #ca8a04; }
.icon-purple { background: #f3e8ff; color: #9333ea; }
.icon-indigo { background: #e0e7ff; color: #4f46e5; }
.icon-red { background: #fee2e2; color: #dc2626; }
.icon-teal { background: #ccfbf1; color: #0d9488; }
.icon-slate { background: #f1f5f9; color: #475569; }
.icon-gray { background: #f3f4f6; color: #4b5563; }
</style>

<?php require_once '../includes/footer.php'; ?>
