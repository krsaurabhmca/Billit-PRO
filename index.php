<?php
/**
 * ============================================================================
 * PREMIUM DASHBOARD
 * ============================================================================
 * Purpose: Central analytical hub for business operations
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

$page_title = "Dashboard";
require_once 'includes/header.php';

// ============================================================================
// DATA AGGREGATION
// ============================================================================

// --- 1. SALES METRICS ---
$today_sales = db_fetch_one($connection, "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE DATE(invoice_date) = CURDATE() AND invoice_status = 'finalized'")['total'];
$month_sales = db_fetch_one($connection, "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE MONTH(invoice_date) = MONTH(CURDATE()) AND YEAR(invoice_date) = YEAR(CURDATE()) AND invoice_status = 'finalized'")['total'];
$total_due   = db_fetch_one($connection, "SELECT COALESCE(SUM(amount_due), 0) as total FROM invoices WHERE invoice_status = 'finalized'")['total'];

// --- 2. PURCHASE METRICS ---
$month_purchases = db_fetch_one($connection, "SELECT COALESCE(SUM(total_amount), 0) as total FROM purchases WHERE MONTH(purchase_date) = MONTH(CURDATE()) AND YEAR(purchase_date) = YEAR(CURDATE())")['total'];

// --- 3. INVENTORY METRICS ---
$low_stock_count = db_fetch_one($connection, "SELECT COUNT(*) as total FROM products WHERE quantity_in_stock <= reorder_level AND status = 'active'")['total'];
$total_products  = db_fetch_one($connection, "SELECT COUNT(*) as total FROM products WHERE status = 'active'")['total'];
$inventory_val   = db_fetch_one($connection, "SELECT SUM(quantity_in_stock * unit_price) as val FROM products WHERE status = 'active'")['val'];

// --- 4. COUNTS ---
$total_customers = db_fetch_one($connection, "SELECT COUNT(*) as total FROM customers WHERE status = 'active'")['total'];
$total_invoices  = db_fetch_one($connection, "SELECT COUNT(*) as total FROM invoices WHERE invoice_status = 'finalized'")['total'];

// --- 5. GRAPHS DATA (Last 7 Days Sales) ---
$sales_labels = [];
$sales_data   = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $sales_labels[] = date('D', strtotime($d));
    $val = db_fetch_one($connection, "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE DATE(invoice_date) = '$d' AND invoice_status = 'finalized'")['total'];
    $sales_data[] = $val;
}

// --- 6. RECENT ACTIVITY ---
$recent_invoices = db_fetch_all($connection, "SELECT i.*, c.customer_name FROM invoices i LEFT JOIN customers c ON i.customer_id = c.customer_id ORDER BY i.invoice_id DESC LIMIT 5");
$recent_purchases = db_fetch_all($connection, "SELECT p.*, s.supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id ORDER BY p.purchase_id DESC LIMIT 5");
?>

<style>
/* Dashboard Specific Styles */
:root {
    --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
    --hover-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.welcome-banner {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    color: white;
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
}

.welcome-text h1 { margin: 0; font-size: 24px; font-weight: 700; }
.welcome-text p { margin: 5px 0 0; opacity: 0.9; }

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--card-shadow);
    transition: transform 0.3s ease;
    border: 1px solid rgba(0,0,0,0.02);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.kpi-card:hover { transform: translateY(-5px); box-shadow: var(--hover-shadow); }

.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.kpi-info h3 { margin: 0; font-size: 28px; font-weight: 700; color: #1e293b; }
.kpi-info p { margin: 0; color: #64748b; font-size: 14px; font-weight: 500; }
.kpi-trend { font-size: 12px; margin-top: 5px; display: inline-block; padding: 2px 8px; border-radius: 20px; }
.trend-up { background: #dcfce7; color: #166534; }
.trend-down { background: #fee2e2; color: #991b1b; }

/* Colors */
.bg-purple-light { background: #f3e8ff; color: #7e22ce; }
.bg-blue-light { background: #dbeafe; color: #1d4ed8; }
.bg-green-light { background: #dcfce7; color: #15803d; }
.bg-orange-light { background: #ffedd5; color: #c2410c; }

.main-charts {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 991px) { .main-charts { grid-template-columns: 1fr; } }

.chart-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--card-shadow);
}

.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.section-title { font-size: 18px; font-weight: 600; color: #1e293b; margin: 0; }

.quick-actions-row {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 10px;
    margin-bottom: 30px;
}

.action-pill {
    background: white;
    padding: 12px 20px;
    border-radius: 50px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #334155;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    border: 1px solid #f1f5f9;
    transition: all 0.2s;
}

.action-pill:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-2px);
}

.tables-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) { .tables-grid { grid-template-columns: 1fr; } }

/* Simple Table for Dashboard */
.simple-table { width: 100%; border-collapse: collapse; }
.simple-table th { text-align: left; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 12px; text-transform:uppercase; font-weight:600; }
.simple-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 14px; }
.simple-table tr:last-child td { border-bottom: none; }
</style>

<!-- Welcome Section -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h1>Dashboard Overview</h1>
        <p>Monitor your performance and manage your business efficiently.</p>
    </div>
    <div class="welcome-date">
        <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; font-weight: 500;">
            📅 <?php echo date('d M Y'); ?>
        </span>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <!-- Monthly Sales -->
    <div class="kpi-card">
        <div class="kpi-info">
            <p>Monthly Sales</p>
            <h3><?php echo format_currency($month_sales); ?></h3>
            <span class="kpi-trend trend-up">Current Month</span>
        </div>
        <div class="kpi-icon bg-blue-light">💰</div>
    </div>
    
    <!-- Purchases -->
    <div class="kpi-card">
        <div class="kpi-info">
            <p>Monthly Purchases</p>
            <h3><?php echo format_currency($month_purchases); ?></h3>
            <span class="kpi-trend trend-down">Outgoing</span>
        </div>
        <div class="kpi-icon bg-orange-light">📥</div>
    </div>
    
    <!-- Pending Payments -->
    <div class="kpi-card">
        <div class="kpi-info">
            <p>Amount Due</p>
            <h3><?php echo format_currency($total_due); ?></h3>
            <span class="kpi-trend trend-down">Collect Pending</span>
        </div>
        <div class="kpi-icon bg-purple-light">⏳</div>
    </div>
    
    <!-- Customers -->
    <div class="kpi-card">
        <div class="kpi-info">
            <p>Total Customers</p>
            <h3><?php echo number_format($total_customers); ?></h3>
            <span class="kpi-trend trend-up">Active</span>
        </div>
        <div class="kpi-icon bg-green-light">👥</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions-row">
    <a href="invoices/pos.php" class="action-pill"><span style="color:#2563eb">⚡</span> POS Terminal</a>
    <a href="invoices/create_invoice.php" class="action-pill"><span style="color:#2563eb">➕</span> Create Invoice</a>
    <a href="purchases/create_purchase.php" class="action-pill"><span style="color:#ca8a04">📥</span> Record Purchase</a>
    <a href="products/add_product.php" class="action-pill"><span style="color:#059669">📦</span> Add Product</a>
    <a href="customers/customers.php" class="action-pill"><span style="color:#7c3aed">👥</span> Add Customer</a>
    <a href="reports/stock_alert.php" class="action-pill"><span style="color:#dc2626">⚠️</span> Stock Alerts <?php if($low_stock_count>0) echo "($low_stock_count)"; ?></a>
</div>

<!-- Charts Section -->
<div class="main-charts">
    <div class="chart-card">
        <div class="section-head">
            <h3 class="section-title">📊 Sales Trend (Last 7 Days)</h3>
        </div>
        <canvas id="salesChart" height="250"></canvas>
    </div>
    
    <div class="chart-card">
        <div class="section-head">
            <h3 class="section-title">📦 Inventory Status</h3>
        </div>
        <div class="text-center" style="margin-bottom: 20px;">
            <h2 style="margin:0; font-size:32px; color:#333;"><?php echo format_currency($inventory_val); ?></h2>
            <small class="text-muted">Total Stock Value</small>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 10px; background:#f8fafc; border-radius:8px; margin-bottom:10px;">
            <span>Total Products</span>
            <strong><?php echo $total_products; ?></strong>
        </div>
         <div style="display:flex; justify-content:space-between; padding: 10px; background:#fef2f2; border-radius:8px; color:#b91c1c;">
            <span>Low Stock Items</span>
            <strong><?php echo $low_stock_count; ?></strong>
        </div>
    </div>
</div>

<!-- Recent Tables -->
<div class="tables-grid">
    <!-- Recent Invoices -->
    <div class="chart-card">
        <div class="section-head">
            <h3 class="section-title">🧾 Recent Sales</h3>
            <a href="invoices/invoices.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_invoices as $inv): ?>
                    <tr>
                        <td>
                            <strong><?php echo $inv['invoice_number']; ?></strong><br>
                            <small class="text-muted"><?php echo $inv['customer_name']; ?></small>
                        </td>
                        <td><?php echo format_currency($inv['total_amount']); ?></td>
                        <td><span class="badge badge-<?php echo $inv['payment_status']=='paid'?'success':'warning'; ?>"><?php echo ucfirst($inv['payment_status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Purchases -->
    <div class="chart-card">
         <div class="section-head">
            <h3 class="section-title">📥 Recent Purchases</h3>
            <a href="purchases/index.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_purchases as $pur): ?>
                    <tr>
                        <td>
                            <strong><?php echo $pur['supplier_name']; ?></strong><br>
                            <small class="text-muted"><?php echo $pur['supplier_invoice_no']; ?></small>
                        </td>
                        <td><?php echo date('d M', strtotime($pur['purchase_date'])); ?></td>
                        <td><?php echo format_currency($pur['total_amount']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($sales_labels); ?>,
        datasets: [{
            label: 'Sales Revenue',
            data: <?php echo json_encode($sales_data); ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
