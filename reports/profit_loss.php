<?php
/**
 * ============================================================================
 * PROFIT & LOSS REPORT
 * ============================================================================
 * Purpose: Financial overview of income vs expenses
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

$page_title = "Profit & Loss";
require_once '../includes/header.php';

// Date Filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-01-01'); // Year to date default
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// 1. Calculate Income (Sales)
// We use 'finalized' invoices or 'paid' depending on accounting method.
// Let's use Total Sales (Accrual basis)
$sales_query = "SELECT SUM(total_amount) as total_sales, COUNT(*) as invoice_count 
                FROM invoices 
                WHERE invoice_date BETWEEN '$start_date' AND '$end_date'";
$sales_data = db_fetch_one($connection, $sales_query);
$total_income = $sales_data['total_sales'] ?? 0;

// 2. Calculate Expenses (Purchases)
$expense_query = "SELECT SUM(total_amount) as total_purchases 
                  FROM stock_transactions 
                  WHERE transaction_type = 'stock_in' 
                  AND DATE(transaction_date) BETWEEN '$start_date' AND '$end_date'";
$expense_data = db_fetch_one($connection, $expense_query);
$total_expense = $expense_data['total_purchases'] ?? 0;

// 3. Profit
$net_profit = $total_income - $total_expense;
$margin = $total_income > 0 ? ($net_profit / $total_income) * 100 : 0;
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📈</span>
            Profit & Loss Statement
        </h2>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">
            <span class="btn-icon">🖨️</span> Print Report
        </button>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="form-group col-md-4">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="form-group col-md-4">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="form-group col-md-4">
                <button type="submit" class="btn btn-primary w-100">Calculate P&L</button>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Income Card -->
    <div class="stat-card stat-card-green">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-label">Total Revenue (Sales)</div>
            <div class="stat-value">₹<?php echo number_format($total_income, 2); ?></div>
            <small><?php echo number_format($sales_data['invoice_count']); ?> Invoices</small>
        </div>
    </div>
    
    <!-- Expense Card -->
    <div class="stat-card stat-card-red">
        <div class="stat-icon">💸</div>
        <div class="stat-content">
            <div class="stat-label">Total Expenses (Purchases)</div>
            <div class="stat-value">₹<?php echo number_format($total_expense, 2); ?></div>
        </div>
    </div>
    
    <!-- Profit Card -->
    <div class="stat-card <?php echo $net_profit >= 0 ? 'stat-card-blue' : 'stat-card-orange'; ?>">
        <div class="stat-icon"><?php echo $net_profit >= 0 ? '💹' : '📉'; ?></div>
        <div class="stat-content">
            <div class="stat-label">Net Profit / (Loss)</div>
            <div class="stat-value">₹<?php echo number_format($net_profit, 2); ?></div>
            <small>Net Margin: <?php echo number_format($margin, 1); ?>%</small>
        </div>
    </div>
</div>

<!-- Detailed Breakdown -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Income Breakdown</h3>
            </div>
            <div class="card-body">
                <table class="table" style="width: 100%;">
                    <tr>
                        <td>Sales Revenue</td>
                        <td class="text-right">₹<?php echo number_format($total_income, 2); ?></td>
                    </tr>
                    <tr style="border-top: 2px solid var(--gray-200); font-weight: bold;">
                        <td>Total Income</td>
                        <td class="text-right">₹<?php echo number_format($total_income, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Expense Breakdown</h3>
            </div>
            <div class="card-body">
                <table class="table" style="width: 100%;">
                    <tr>
                        <td>Inventory Purchases</td>
                        <td class="text-right">₹<?php echo number_format($total_expense, 2); ?></td>
                    </tr>
                     <tr style="border-top: 2px solid var(--gray-200); font-weight: bold;">
                        <td>Total Expenses</td>
                        <td class="text-right">₹<?php echo number_format($total_expense, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
