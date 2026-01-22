<?php
/**
 * ============================================================================
 * HELP & DOCUMENTATION
 * ============================================================================
 * Purpose: User guide for the application
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

$page_title = "Help & User Guide";
require_once 'includes/header.php';
?>

<style>
    .help-nav { position: sticky; top: 20px; }
    .help-nav a { display: block; padding: 10px 15px; text-decoration: none; color: #475569; border-left: 3px solid transparent; transition: all 0.2s; }
    .help-nav a:hover, .help-nav a.active { background: #f1f5f9; border-left-color: #2563eb; color: #1e293b; font-weight: 500; }
    
    .help-section { background: white; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); scroll-margin-top: 20px; }
    .help-section h2 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
    .help-section h3 { margin-top: 25px; color: #334155; font-size: 18px; }
    .help-section p, .help-section li { color: #475569; line-height: 1.7; font-size: 15px; }
    
    .feature-badge { background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-left: 10px; }
    
    .screenshot-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; color: #64748b; font-style: italic; }
</style>

<div class="page-header">
    <div>
        <h2 class="page-title"><span class="page-icon">📚</span> User Manual</h2>
        <p class="page-description">Complete guide to using Billit Pro Invoice & Inventory System.</p>
    </div>
</div>

<div class="row">
    <!-- Navigation Sidebar -->
    <div class="col-md-3">
        <div class="card help-nav">
            <div class="card-body p-0 py-3">
                <a href="#quick-start">🚀 Quick Start</a>
                <a href="#dashboard">📊 Dashboard Overview</a>
                <a href="#inventory">📦 Inventory & Products</a>
                <a href="#tracking">🔢 Batch & Serial Tracking</a>
                <a href="#sales">🧾 Sales & Invoicing</a>
                <a href="#purchases">📥 Purchases & Stock In</a>
                <a href="#reports">📈 Reports & Analytics</a>
                <a href="#settings">⚙️ Settings & Branding</a>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="col-md-9">
        
        <!-- 1. Quick Start -->
        <div id="quick-start" class="help-section">
            <h2>🚀 Quick Start Guide</h2>
            <p>Welcome to <strong>Billit Pro</strong>! Follow these steps to get started in 5 minutes:</p>
            <ol>
                <li><strong>Setup Company Info:</strong> Go to <em>Settings > Company Settings</em> to add your Logo, GSTIN, and Bank Details.</li>
                <li><strong>Add Products:</strong> Go to <em>Inventory > Products</em> and add your items. Enable "Batch" or "Serial" tracking if needed.</li>
                <li><strong>Record Purchase:</strong> Go to <em>Purchases > Record Purchase</em> to add initial stock.</li>
                <li><strong>Create Invoice:</strong> Go to <em>Invoices > Create Invoice</em> to make your first sale!</li>
            </ol>
        </div>

        <!-- 2. Dashboard -->
        <div id="dashboard" class="help-section">
            <h2>📊 Dashboard</h2>
            <p>The dashboard gives you a bird's-eye view of your business performance.</p>
            <ul>
                <li><strong>KPI Cards:</strong> See Monthly Sales, Total Purchases, Pending Payments, and Customer Count at a glance.</li>
                <li><strong>Sales Trend:</strong> A visual chart showing revenue over the last 7 days.</li>
                <li><strong>Stock Value:</strong> Total value of your inventory currently in hand.</li>
                <li><strong>Low Stock Alerts:</strong> Shows count of items below reorder level. Click the alert to see which items need restocking.</li>
                <li><strong>Quick Actions:</strong> Use the pill buttons to quickly jump to common tasks.</li>
            </ul>
        </div>

        <!-- 3. Inventory -->
        <div id="inventory" class="help-section">
            <h2>📦 Inventory Management</h2>
            <h3>Adding Products</h3>
            <p>Navigate to <strong>Products > Add Product</strong>. You can specify:</p>
            <ul>
                <li><strong>General Info:</strong> Name, Code (SKU), and Description.</li>
                <li><strong>Pricing:</strong> Selling Price and Purchase Cost.</li>
                <li><strong>Tracking Type:</strong> 
                    <ul>
                        <li><em>None:</em> Simple quantity tracking (e.g. Pens, Loose items).</li>
                        <li><em>Batch Tracking:</em> For items with Expiry Dates (e.g. Medicines, Food).</li>
                        <li><em>Serial Tracking:</em> For unique items (e.g. Electronics, IMEIs).</li>
                    </ul>
                </li>
            </ul>
            <h3>Bulk Import</h3>
            <p>Use the <strong>Import</strong> tool to upload a CSV file with hundreds of products at once.</p>
        </div>

        <!-- 4. Batch & Serial Tracking -->
        <div id="tracking" class="help-section">
            <h2>🔢 Advanced Tracking <span class="feature-badge">PREMIUM</span></h2>
            <p>For businesses dealing with Electronics or Pharma, detailed tracking is essential.</p>
            
            <h3>Managing Batches (Expiry)</h3>
            <p>When selling a batch-tracked item, you will be asked to select the specific Batch Number. The system automatically suggests the oldest batch (FIFO - First In First Out) to prevent expiry loss.</p>
            
            <h3>Managing Serials (IMEI)</h3>
            <p>When selling a serial-tracked item (like a Phone), you must select the specific unique Serial/IMEI from the dropdown. Once sold, that Serial Number is marked as 'Sold' and cannot be sold again unless returned.</p>
        </div>

        <!-- 5. Sales & Invoicing -->
        <div id="sales" class="help-section">
            <h2>🧾 Creating Invoices</h2>
            <p>Go to <strong>Invoices > Create Invoice</strong>.</p>
            <ol>
                <li><strong>Select Customer:</strong> Choose an existing customer or quick-add a new one from the customers page.</li>
                <li><strong>Add Items:</strong> Select products. If the product has tracking, a popup/dropdown will ask for Batch or Serial details.</li>
                <li><strong>Discounts:</strong> Apply a flat amount or percentage discount on the subtotal.</li>
                <li><strong>Status - Draft vs Finalized:</strong>
                    <ul>
                        <li><em>Draft:</em> Saves the invoice but <strong>does not deduct stock</strong>. Useful for quotes or work-in-progress.</li>
                        <li><em>Finalized:</em> Deducts stock immediately and generates financial entries.</li>
                    </ul>
                </li>
            </ol>
            <h3>Payments</h3>
            <p>After creating an invoice, click "Add Payment" to record cash/bank receipts. Invoices can be "Partial", "Paid", or "Unpaid".</p>
        </div>

        <!-- 6. Purchases -->
        <div id="purchases" class="help-section">
            <h2>📥 Purchases (Stock In)</h2>
            <p>To increase stock, always use the <strong>Purchase</strong> module.</p>
            <p>When you record a purchase:</p>
            <ul>
                <li><strong>Received Status:</strong> Immediately adds quantity to your inventory.</li>
                <li><strong>Tracking Entry:</strong> This is where you enter new Batch Numbers (with Expiry) or new Serial Numbers for the incoming stock.</li>
            </ul>
        </div>
        
        <!-- 7. Reports -->
        <div id="reports" class="help-section">
            <h2>📈 Reports</h2>
            <p>Analyze your growth with built-in reports:</p>
            <ul>
                <li><strong>Sales Report:</strong> Date-wise breakdown of sales revenue.</li>
                <li><strong>Purchase Report:</strong> Track your spending.</li>
                <li><strong>Low Stock:</strong> List of items that need reordering.</li>
                <li><strong>GST Report:</strong> Detailed tax breakdown (CGST/SGST/IGST) for filing returns.</li>
                <li><strong>Profit & Loss:</strong> Estimated profit based on Purchase Cost vs Selling Price.</li>
            </ul>
        </div>

        <!-- 8. Settings -->
        <div id="settings" class="help-section">
            <h2>⚙️ Settings & Branding</h2>
            <p>Make the invoices your own!</p>
            <ul>
                <li><strong>Company Info:</strong> Update your address and tax details.</li>
                <li><strong>Logo:</strong> Upload your company logo. It will appear on the top-left of PDF invoices.</li>
                <li><strong>Theme Color:</strong> Choose a brand color (e.g., Blue, Red, Green). This color will clearly highlight headers and totals on your invoices.</li>
                <li><strong>Bank Details:</strong> Add your NEFT/UPI details to be printed on the invoice footer.</li>
            </ul>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
