<?php
/**
 * ============================================================================
 * POS SYSTEM (Point of Sale)
 * ============================================================================
 * Purpose: Fast billing screen
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

$page_title = "POS Terminal";
// We might not want the standard header/sidebar for a "Fancy POS". 
// But we need the authentication and DB connection.
require_once '../config/config.php';
require_once '../includes/functions.php';
require_login();

// 1. FETCH DATA (Products, Customers)
// For a fast POS, we might preload active products or use AJAX. Preloading active products is fine for small/medium DB.
$products = db_fetch_all($connection, "SELECT product_id, product_name, product_code, unit_price, gst_rate, quantity_in_stock, unit_of_measure FROM products WHERE status = 'active' ORDER BY product_name");
$customers = db_fetch_all($connection, "SELECT customer_id, customer_name, phone as customer_phone, customer_type FROM customers WHERE status = 'active' ORDER BY customer_name");

// Fetch company for GST Logic
$company = db_fetch_one($connection, "SELECT company_state_code FROM company_settings LIMIT 1");
$company_state = $company ? ($company['company_state_code'] ?? '27') : '27';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - <?php echo APP_NAME; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --accent: #f59e0b;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden; /* App-like feel */
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .pos-header {
            height: 60px;
            background: #1e293b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 10;
        }
        .brand { font-size: 20px; font-weight: 700; display:flex; gap:10px; align-items:center; }
        .pos-actions { display: flex; gap: 15px; }
        .btn-header {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.2s;
        }
        .btn-header:hover { background: rgba(255,255,255,0.2); }

        /* Main Layout */
        .pos-container {
            display: flex;
            height: calc(100vh - 60px);
            overflow: hidden;
        }

        /* LEFT: Product Grid/Selector */
        .pos-left {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
        }

        .search-bar {
            margin-bottom: 20px;
            position: relative;
        }
        .search-bar input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            background: var(--card-bg);
            transition: border-color 0.2s;
        }
        .search-bar input:focus { border-color: var(--primary); }
        .search-icon { position: absolute; left: 12px; top: 14px; color: var(--text-light); }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 15px;
            overflow-y: auto;
            padding-bottom: 20px;
        }
        
        .product-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
            position: relative;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }
        .product-card::before {
             /* Fancy gradient overlay on hover */
             content: '';
             position: absolute;
             top: 0; left: 0; right: 0; height: 4px;
             background: transparent;
             transition: background 0.2s;
        }
        .product-card:hover::before {
             background: var(--primary);
        }

        .prod-name { font-weight: 600; font-size: 14px; margin-bottom: 5px; color: var(--text-main); line-height:1.3; }
        .prod-code { font-size: 11px; color: var(--text-light); margin-bottom: 10px; }
        .prod-price { font-weight: 700; color: var(--primary); font-size: 15px; }
        .prod-stock { font-size: 11px; color: var(--text-light); background: #f1f5f9; padding: 2px 6px; border-radius: 4px; align-self: flex-start; margin-top:5px; }
        .stock-low { color: #dc2626; background: #fef2f2; }

        /* RIGHT: Cart/Billing */
        .pos-right {
            width: 400px;
            background: var(--card-bg);
            display: flex;
            flex-direction: column;
            box-shadow: -2px 0 10px rgba(0,0,0,0.05);
        }
        
        .customer-panel {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            background: #f8fafc;
        }
        .customer-select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin-bottom: 5px;
        }
        .customer-meta { font-size: 12px; color: var(--text-light); display: flex; justify-content: space-between; }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th { text-align: left; font-size: 11px; color: var(--text-light); padding: 5px; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        .cart-table td { padding: 10px 5px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        
        .qty-controls { display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 4px; width: fit-content; }
        .qty-btn { width: 24px; height: 24px; border: none; background: white; cursor: pointer; color: var(--text-main); font-weight: bold; }
        .qty-btn:hover { background: #f1f5f9; }
        .qty-val { width: 30px; text-align: center; font-size: 12px; border: none; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }

        .btn-remove { 
            color: #ef4444; background: none; border: none; cursor: pointer; 
            padding: 4px; border-radius: 4px; 
        }
        .btn-remove:hover { background: #fef2f2; }

        .billing-panel {
            padding: 20px;
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
        }
        .bill-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
        .bill-total { font-size: 18px; font-weight: 700; color: var(--text-main); border-top: 2px solid var(--border-color); padding-top: 10px; margin-top: 10px; }
        
        .btn-checkout {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .btn-checkout:hover { background: var(--primary-dark); }

    </style>
</head>
<body>

    <!-- Header -->
    <header class="pos-header">
        <div class="brand">
            <span>⚡ POS Terminal</span>
        </div>
        <div class="pos-actions">
            <a href="../index.php" class="btn-header">Exit to Dashboard</a>
        </div>
    </header>

    <div class="pos-container">
        <!-- LEFT: Products -->
        <div class="pos-left">
            <div class="search-bar">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchProd" placeholder="Search products by name or code..." onkeyup="filterProducts()">
            </div>
            
            <div class="product-grid" id="productGrid">
                <?php foreach ($products as $p): 
                    $low_stock = $p['quantity_in_stock'] < 10;
                ?>
                <div class="product-card" onclick="addToCart(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                    <div>
                        <div class="prod-name"><?php echo escape_html($p['product_name']); ?></div>
                        <div class="prod-code"><?php echo escape_html($p['product_code']); ?></div>
                    </div>
                    <div>
                        <div class="prod-price">₹<?php echo number_format($p['unit_price'], 2); ?></div>
                        <div class="prod-stock <?php echo $low_stock ? 'stock-low' : ''; ?>">
                            Stock: <?php echo $p['quantity_in_stock']; ?> <?php echo $p['unit_of_measure']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT: Billing -->
        <div class="pos-right">
            <!-- Customer Section -->
            <div class="customer-panel">
                <label style="font-size:12px; font-weight:600; color:#64748b; margin-bottom:5px; display:block;">Customer (Mobile / Name)</label>
                
                <div style="display:flex; gap:10px; margin-bottom:5px;">
                    <div style="position:relative; flex:1;">
                        <input type="text" id="custSearch" class="customer-select" 
                               placeholder="Search Mobile or Name..." 
                               onkeyup="searchCustomer()" 
                               onfocus="searchCustomer()"
                               autocomplete="off"
                               style="background:white;">
                        <input type="hidden" id="customerId">
                        <!-- Dropdown Results -->
                        <div id="custResults" style="display:none; position:absolute; top:100%; left:0; right:0; max-height:200px; overflow-y:auto; background:white; border:1px solid #e2e8f0; border-radius:6px; box-shadow:0 4px 10px rgba(0,0,0,0.1); z-index:100;">
                            <!-- Items injected by JS -->
                        </div>
                    </div>
                    <button class="btn-header" style="background:var(--primary); color:white; width:40px; display:flex; align-items:center; justify-content:center;" onclick="openAddModal()">+</button>
                </div>
                
                <!-- Selected Customer Info -->
                <div id="selectedCustInfo" style="display:none; margin-top:5px; font-size:13px; color:var(--primary); background:#e0e7ff; padding:8px; border-radius:4px;">
                    <span id="selCustName"></span> <span id="selCustPhone" style="font-size:11px; opacity:0.8;"></span>
                    <button onclick="clearCustomer()" style="border:none; background:none; float:right; cursor:pointer;">×</button>
                </div>

                <!-- Fallback hidden form inputs -->
                <input type="hidden" id="invDate" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <!-- Add Customer Modal -->
            <div id="addCustModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:white; padding:25px; border-radius:10px; width:350px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
                    <h3 style="margin-top:0;">Add New Customer</h3>
                    
                    <div style="margin-top:15px;">
                        <label style="display:block; font-size:12px; margin-bottom:5px;">Mobile Number *</label>
                        <input type="text" id="newCustMobile" class="customer-select" style="width:100%;">
                    </div>
                    <div style="margin-top:10px;">
                        <label style="display:block; font-size:12px; margin-bottom:5px;">Customer Name *</label>
                        <input type="text" id="newCustName" class="customer-select" style="width:100%;">
                    </div>
                    <div style="margin-top:10px;">
                        <label style="display:block; font-size:12px; margin-bottom:5px;">Address</label>
                        <textarea id="newCustAddr" class="customer-select" rows="2" style="width:100%;"></textarea>
                    </div>
                    
                    <div style="margin-top:20px; text-align:right;">
                        <button onclick="closeAddModal()" style="padding:8px 15px; border:none; background:#f1f5f9; border-radius:4px; cursor:pointer; margin-right:10px;">Cancel</button>
                        <button onclick="saveNewCustomer()" style="padding:8px 15px; border:none; background:var(--primary); color:white; border-radius:4px; cursor:pointer;">Save</button>
                    </div>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="cart-items">
                <table class="cart-table" id="cartTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:right;">Tax</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        <!-- JS Insert -->
                        <tr id="emptyCartRow"><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">Cart is empty</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="billing-panel">
                <div class="bill-row">
                    <span>Subtotal</span>
                    <span id="lblSubtotal">₹0.00</span>
                </div>
                <div class="bill-row" style="align-items: center;">
                    <span>Discount (₹)</span>
                    <input type="number" id="inputDiscount" value="0" min="0" step="1" 
                           style="width: 80px; text-align: right; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px;"
                           onchange="updateTotals()">
                </div>
                <div class="bill-row">
                    <span>Tax (GST)</span>
                    <span id="lblTax">₹0.00</span>
                </div>
                <div class="bill-total bill-row">
                    <span>TOTAL</span>
                    <span id="lblTotal" style="color: var(--primary);">₹0.00</span>
                </div>
                
                <button class="btn-checkout" onclick="openPaymentModal()">
                    <span>💳</span> Proceed to Pay
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden Form -->
    <form id="hiddenPosForm" method="POST" action="create_invoice.php" style="display:none;">
         <input type="hidden" name="customer_id" id="h_cust_id">
         <input type="hidden" name="invoice_date" id="h_date">
         <input type="hidden" name="discount_type" value="amount">
         <input type="hidden" name="discount_value" id="h_discount_value" value="0">
         <input type="hidden" name="notes" value="POS Sale">
         <input type="hidden" name="invoice_status" value="finalized">
         <input type="hidden" name="products_data" id="h_products">
         <input type="hidden" name="payment_data" id="h_payment_data"> <!-- JSON of payments -->
         <input type="hidden" name="send_email" value="0">
    </form>

    <!-- Payment Modal -->
    <div id="paymentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:white; padding:25px; border-radius:12px; width:500px; max-width:95%; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0; font-size:20px;">Payment</h2>
                <button onclick="closePaymentModal()" style="border:none; background:none; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            
            <div style="background:#f8fafc; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">
                <div style="font-size:13px; color:#64748b;">Total Payable</div>
                <div id="modalTotalPayable" style="font-size:28px; font-weight:700; color:#1e293b;">₹0.00</div>
            </div>
            
            <div id="paymentRows" style="max-height:200px; overflow-y:auto; margin-bottom:15px;">
                <!-- Dynamic Pay Rows -->
                <div class="pay-row" style="display:flex; gap:10px; margin-bottom:10px;">
                    <select class="pay-mode form-select" style="flex:1; padding:8px; border:1px solid #e2e8f0; border-radius:6px;">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                    <input type="number" class="pay-amount" placeholder="Amount" style="flex:1; padding:8px; border:1px solid #e2e8f0; border-radius:6px;" onkeyup="calculateBalance()">
                    <button onclick="removePayRow(this)" style="background:#fee2e2; color:#ef4444; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">×</button>
                </div>
            </div>
            
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <button onclick="addPayRow()" style="background:#f1f5f9; color:#475569; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; font-size:13px;">+ Add Payment Mode</button>
                <div style="text-align:right;">
                     <span style="font-size:13px; color:#64748b;">Balance:</span> <span id="modalBalance" style="font-weight:600; color:#dc2626;">₹0.00</span>
                </div>
            </div>
            
            <button class="btn-checkout" onclick="submitSale()">
                Inventory & Print Invoice
            </button>
        </div>
    </div>

<script>
    // State
    const cart = [];
    const companyState = '<?php echo $company_state; ?>'; 
    let allCustomers = <?php echo json_encode($customers); ?>;
    const isAdmin = <?php echo has_role('admin') ? 'true' : 'false'; ?>;
    
    // Customer UI Logic
    function searchCustomer() {
        const input = document.getElementById('custSearch');
        const results = document.getElementById('custResults');
        const term = input.value.toLowerCase().trim();
        
        if (term.length === 0) {
            results.style.display = 'none';
            return;
        }
        
        const matches = allCustomers.filter(c => 
            (c.customer_name.toLowerCase().includes(term) || (c.customer_phone && c.customer_phone.includes(term)))
        ).slice(0, 10); // Limit to 10
        
        results.innerHTML = '';
        if (matches.length > 0) {
            matches.forEach(c => {
                const div = document.createElement('div');
                div.style.padding = '8px 10px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #f1f5f9';
                div.onmouseover = function() { this.style.backgroundColor = '#f8fafc'; };
                div.onmouseout = function() { this.style.backgroundColor = 'white'; };
                div.onclick = function() { selectCustomer(c); };
                // Highlight Mobile logic
                const phone = c.customer_phone || 'No Phone';
                div.innerHTML = `<div><strong>${c.customer_name}</strong></div><div style="font-size:11px; color:#64748b;">${phone}</div>`;
                results.appendChild(div);
            });
            results.style.display = 'block';
        } else {
             results.innerHTML = '<div style="padding:10px; color:#94a3b8; font-size:12px;">No match</div>';
             results.style.display = 'block';
        }
    }
    
    function selectCustomer(c) {
        document.getElementById('customerId').value = c.customer_id;
        document.getElementById('custSearch').value = ''; // Clean input
        document.getElementById('custResults').style.display = 'none';
        
        // Show selection
        document.getElementById('selectedCustInfo').style.display = 'block';
        document.getElementById('selCustName').innerText = c.customer_name;
        document.getElementById('selCustPhone').innerText = c.customer_phone || '';
        
        // Hide entry
        document.getElementById('custSearch').parentNode.style.display = 'none';
    }
    
    function clearCustomer() {
        document.getElementById('customerId').value = '';
        document.getElementById('selectedCustInfo').style.display = 'none';
        document.getElementById('custSearch').parentNode.style.display = 'block';
        document.getElementById('custSearch').focus();
    }
    
    // Clicking outside closes search results
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#custSearch') && !e.target.closest('#custResults')) {
             document.getElementById('custResults').style.display = 'none';
        }
    });

    // Add Customer Logic
    function openAddModal() {
        document.getElementById('addCustModal').style.display = 'flex';
        document.getElementById('newCustMobile').focus();
    }
    
    function closeAddModal() {
        document.getElementById('addCustModal').style.display = 'none';
        document.getElementById('newCustName').value = '';
        document.getElementById('newCustMobile').value = '';
        document.getElementById('newCustAddr').value = '';
    }
    
    function saveNewCustomer() {
        const name = document.getElementById('newCustName').value;
        const mobile = document.getElementById('newCustMobile').value;
        const addr = document.getElementById('newCustAddr').value;
        
        if (!name || !mobile) { alert('Name and Mobile are required'); return; }
        
        // Setup data
        const formData = new FormData();
        formData.append('customer_name', name);
        formData.append('customer_phone', mobile);
        formData.append('customer_address', addr);
        
        fetch('../ajax_add_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Add to list and select
                const newC = {
                    customer_id: data.customer.id,
                    customer_name: data.customer.name,
                    customer_phone: data.customer.phone
                };
                allCustomers.push(newC);
                selectCustomer(newC);
                closeAddModal();
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error creating customer');
        });
    }

    function addToCart(product) {
        // Stock Check
        const stock = parseInt(product.quantity_in_stock) || 0;
        
        // Check if exists
        const existing = cart.find(i => i.product_id == product.product_id);
        
        let currentQty = existing ? existing.quantity : 0;
        if (currentQty + 1 > stock) {
            alert('Out of Stock! Available: ' + stock);
            return;
        }

        if (existing) {
            existing.quantity++;
        } else {
            cart.push({
                product_id: product.product_id,
                product_name: product.product_name,
                product_code: product.product_code,
                unit_price: parseFloat(product.unit_price),
                quantity: 1,
                gst_rate: parseFloat(product.gst_rate),
                unit_of_measure: product.unit_of_measure || 'Units',
                quantity_in_stock: stock // Store for later checks
            });
        }
        renderCart();
    }
    
    function renderCart() {
        const tbody = document.getElementById('cartBody');
        tbody.innerHTML = '';
        
        if (cart.length === 0) {
            tbody.innerHTML = '<tr id="emptyCartRow"><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">Cart is empty</td></tr>';
            updateTotals();
            return;
        }

        cart.forEach((item, index) => {
            const row = document.createElement('tr');
            const subtotal = item.quantity * item.unit_price;
            
            const priceHtml = isAdmin 
                ? `<input type="number" value="${item.unit_price}" step="0.01" style="width:70px; text-align:right; padding:4px; border:1px solid #ccc; border-radius:4px;" onchange="updatePrice(${index}, this.value)">` 
                : item.unit_price.toFixed(2);
                
            row.innerHTML = `
                <td>
                    <div style="font-weight:600;">${item.product_name}</div>
                    <div style="font-size:10px; color:#94a3b8;">${item.product_code}</div>
                </td>
                <td>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                        <input class="qty-val" value="${item.quantity}" readonly>
                        <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td style="text-align:right;">${priceHtml}</td>
                <td style="text-align:right; font-size:11px; color:#64748b;">
                    ${item.gst_rate}%<br>
                    ₹${((item.quantity * item.unit_price * item.gst_rate)/100).toFixed(2)}
                </td>
                <td style="text-align:right;">${subtotal.toFixed(2)}</td>
                <td style="text-align:center;">
                    <button class="btn-remove" onclick="removeItem(${index})">×</button>
                </td>
            `;
            tbody.appendChild(row);
        });
        updateTotals();
    }
    
    function updatePrice(index, val) {
        let newPrice = parseFloat(val);
        if (isNaN(newPrice) || newPrice < 0) newPrice = 0;
        cart[index].unit_price = newPrice;
        renderCart();
    }

    function updateQty(index, change) {
        if (cart[index].quantity + change > 0) {
            cart[index].quantity += change;
            renderCart();
        }
    }

    function removeItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function updateTotals() {
        let subtotal = 0;
        let originalTax = 0;
        
        // Sum items
        cart.forEach(item => {
            const lineTotal = item.quantity * item.unit_price;
            subtotal += lineTotal;
            // Rough tax calc per item
            const itemTax = (lineTotal * item.gst_rate) / 100;
            originalTax += itemTax;
        });
        
        // Get discount
        let discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
        if (discount < 0) discount = 0;
        if (discount > subtotal) discount = subtotal; // Cap at subtotal
        
        // Calculate factor
        const factor = subtotal > 0 ? (subtotal - discount) / subtotal : 0;
        
        // Adjusted Tax
        const finalTax = originalTax * factor;
        const total = (subtotal - discount) + finalTax;

        document.getElementById('lblSubtotal').innerText = '₹' + subtotal.toFixed(2);
        document.getElementById('lblTax').innerText = '₹' + finalTax.toFixed(2);
        document.getElementById('lblTotal').innerText = '₹' + total.toFixed(2);
        
        return total;
    }

    function filterProducts() {
        const term = document.getElementById('searchProd').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(term) ? 'flex' : 'none';
        });
    }

    // Payment Modal Logic
    function openPaymentModal() {
        if (cart.length === 0) { alert('Cart is empty!'); return; }
        
        const custId = document.getElementById('customerId').value;
        if (!custId) { 
            alert('Please select a customer!'); 
            document.getElementById('custSearch').focus();
            document.getElementById('custSearch').style.borderColor = 'red';
            return; 
        }
        document.getElementById('custSearch').style.borderColor = '#e2e8f0'; // Reset
        
        const total = updateTotals();
        document.getElementById('modalTotalPayable').innerText = '₹' + total.toFixed(2);
        document.getElementById('paymentModal').style.display = 'flex';
        
        // Reset rows to single row with full amount
        const container = document.getElementById('paymentRows');
        container.innerHTML = '';
        addPayRow(total); // Add one row with total
        calculateBalance();
    }
    
    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }
    
    function addPayRow(amount = '') {
        const container = document.getElementById('paymentRows');
        const div = document.createElement('div');
        div.className = 'pay-row';
        div.style = "display:flex; gap:10px; margin-bottom:10px;";
        div.innerHTML = `
            <select class="pay-mode form-select" style="flex:1; padding:8px; border:1px solid #e2e8f0; border-radius:6px;">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cheque">Cheque</option>
            </select>
            <input type="number" class="pay-amount" placeholder="Amount" value="${amount}" style="flex:1; padding:8px; border:1px solid #e2e8f0; border-radius:6px;" onkeyup="calculateBalance()">
            <button onclick="removePayRow(this)" style="background:#fee2e2; color:#ef4444; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">×</button>
        `;
        container.appendChild(div);
        calculateBalance();
    }
    
    function removePayRow(btn) {
        btn.parentNode.remove();
        calculateBalance();
    }
    
    function calculateBalance() {
        const totalStr = document.getElementById('modalTotalPayable').innerText.replace('₹', '');
        const total = parseFloat(totalStr) || 0;
        
        let paid = 0;
        document.querySelectorAll('.pay-amount').forEach(inp => {
            paid += parseFloat(inp.value) || 0;
        });
        
        const balance = total - paid;
        const balEl = document.getElementById('modalBalance');
        balEl.innerText = '₹' + balance.toFixed(2);
        
        if (balance > 0.01) {
            balEl.style.color = '#dc2626'; // Red if due
        } else if (balance < -0.01) {
             balEl.style.color = '#2563eb'; // Blue if change due
             balEl.innerText = 'Change: ₹' + Math.abs(balance).toFixed(2);
        } else {
            balEl.style.color = '#10b981'; // Green if matched
        }
        
        return balance;
    }
    
    function submitSale() {
        const balance = calculateBalance();
        if (balance > 1) { // Allow small rounding diff
             if(!confirm('Payment is incomplete (Due: ₹'+balance.toFixed(2)+'). Proceed properly?')) return;
        }
        
        // Gather Payment Data
        const payments = [];
        document.querySelectorAll('.pay-row').forEach(row => {
            const mode = row.querySelector('.pay-mode').value;
            const amt = parseFloat(row.querySelector('.pay-amount').value) || 0;
            if (amt > 0) {
                payments.push({ mode: mode, amount: amt });
            }
        });
        
        if (payments.length === 0) {
            // If nothing paid, maybe it's credit?
             if(!confirm('No payment recorded. Mark as Credit Sale?')) return;
        }
        
        // Prepare Form
        document.getElementById('h_cust_id').value = document.getElementById('customerId').value;
        document.getElementById('h_date').value = document.getElementById('invDate').value;
        document.getElementById('h_products').value = JSON.stringify(cart);
        document.getElementById('h_discount_value').value = document.getElementById('inputDiscount').value || 0;
        document.getElementById('h_payment_data').value = JSON.stringify(payments);
        
        // Submit
        document.getElementById('hiddenPosForm').submit();
    }
</script>

</body>
</html>
