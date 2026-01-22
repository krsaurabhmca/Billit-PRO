<?php
/**
 * ============================================================================
 * CREATE PURCHASE PAGE
 * ============================================================================
 * Purpose: Record new stock purchases with batch/serial support
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        mysqli_begin_transaction($connection);

        $supplier_id = sanitize_sql($connection, $_POST['supplier_id']);
        $invoice_no = sanitize_sql($connection, $_POST['supplier_invoice_no']);
        $purchase_date = sanitize_sql($connection, $_POST['purchase_date']);
        $status = sanitize_sql($connection, $_POST['status']);
        $notes = sanitize_sql($connection, $_POST['notes']);
        
        $items_json = $_POST['items_data']; 
        $items = json_decode($items_json, true);

        if (empty($items)) {
            throw new Exception("No items in purchase.");
        }

        // Calculate Total
        $total_amount = 0;
        foreach ($items as $item) {
            $total_amount += ($item['quantity'] * $item['unit_cost']);
        }

        // Create Purchase Record
        $query = "INSERT INTO purchases (supplier_id, supplier_invoice_no, purchase_date, total_amount, status, created_by, notes) 
                  VALUES ('$supplier_id', '$invoice_no', '$purchase_date', '$total_amount', '$status', '{$_SESSION['user_id']}', '$notes')";
        
        if (!db_execute($connection, $query)) {
            throw new Exception("Failed to create purchase record.");
        }
        
        $purchase_id = mysqli_insert_id($connection);

        // Process Items
        foreach ($items as $item) {
            $product_id = $item['product_id'];
            $qty = $item['quantity'];
            $cost = $item['unit_cost'];
            $line_total = $qty * $cost;
            
            // Extract tracking info
            $batch_no = isset($item['batch_no']) ? $item['batch_no'] : null;
            $expiry = isset($item['expiry_date']) ? $item['expiry_date'] : null;
            $serials = isset($item['serial_numbers']) ? $item['serial_numbers'] : null; 

            // 1. Insert Purchase Item
            $item_sql = "INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, total_cost, batch_no, expiry_date, serial_numbers) 
                         VALUES ('$purchase_id', '$product_id', '$qty', '$cost', '$line_total', " . 
                         ($batch_no ? "'$batch_no'" : "NULL") . ", " . 
                         ($expiry ? "'$expiry'" : "NULL") . ", " . 
                         ($serials ? "'$serials'" : "NULL") . ")";
            
            if (!db_execute($connection, $item_sql)) {
                 throw new Exception("Failed to insert purchase item.");
            }
            $purchase_item_id = mysqli_insert_id($connection);

            // 2. Update Stock if 'received'
            if ($status === 'received') {
                // Update master inventory
                db_execute($connection, "UPDATE products SET quantity_in_stock = quantity_in_stock + $qty WHERE product_id = '$product_id'");
                
                // Handle Tracking
                if ($item['tracking_type'] === 'batch' && $batch_no) {
                    $check_batch = db_fetch_one($connection, "SELECT batch_id FROM product_batches WHERE product_id='$product_id' AND batch_no='$batch_no'");
                    if ($check_batch) {
                         db_execute($connection, "UPDATE product_batches SET quantity_received = quantity_received + $qty, quantity_remaining = quantity_remaining + $qty WHERE batch_id={$check_batch['batch_id']}");
                    } else {
                        $expiry_sql = $expiry ? "'$expiry'" : "NULL";
                        db_execute($connection, "INSERT INTO product_batches (product_id, batch_no, expiry_date, purchase_item_id, quantity_received, quantity_remaining) 
                                                 VALUES ('$product_id', '$batch_no', $expiry_sql, '$purchase_item_id', '$qty', '$qty')");
                    }
                } 
                elseif ($item['tracking_type'] === 'serial' && !empty($serials)) {
                    $serial_list = array_map('trim', explode(',', $serials));
                    foreach ($serial_list as $sn) {
                        if (empty($sn)) continue;
                        $sn = sanitize_sql($connection, $sn);
                        // Insert Serial
                        db_execute($connection, "INSERT INTO product_serials (product_id, serial_no, purchase_item_id, status) 
                                                 VALUES ('$product_id', '$sn', '$purchase_item_id', 'available')");
                    }
                }

                // Log Transaction
                db_execute($connection, "INSERT INTO stock_transactions (product_id, transaction_type, quantity, unit_price, total_amount, reference_number, transaction_date, notes) 
                                         VALUES ('$product_id', 'stock_in', '$qty', '$cost', '$line_total', 'PUR-$purchase_id', '$purchase_date', 'Purchase received')");
            }
        }

        mysqli_commit($connection);
        set_success_message("Purchase recorded successfully.");
        redirect('index.php');

    } catch (Exception $e) {
        mysqli_rollback($connection);
        set_error_message("Error: " . $e->getMessage());
    }
}

$page_title = "Record Purchase";
require_once '../includes/header.php';

$suppliers = db_fetch_all($connection, "SELECT * FROM suppliers WHERE status='active' ORDER BY supplier_name");
$products = db_fetch_all($connection, "SELECT product_id, product_name, product_code, tracking_type FROM products WHERE status!='discontinued' ORDER BY product_name");
?>

<style>
/* Custom Layout Styles */
.tracking-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-top: -10px;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}
.tracking-panel::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 40px;
    width: 16px;
    height: 16px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-left: 1px solid #e2e8f0;
    transform: rotate(45deg);
}
.purchase-total-card {
    background: #1e293b;
    color: white;
    padding: 20px;
    border-radius: 8px;
    text-align: right;
}
.purchase-total-card h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}
.purchase-total-card small {
    color: #94a3b8;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 1px;
}
</style>

<div class="page-header">
    <div>
        <h2 class="page-title"><span class="page-icon">📥</span> New Purchase Record</h2>
        <p class="page-description">Enter details of stock received from supplier.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span> Back
        </a>
    </div>
</div>

<form method="POST" id="purchaseForm">
    <input type="hidden" name="items_data" id="items_data">
    
    <!-- 1. HEADER INFO -->
    <div class="card mb-20">
        <div class="card-header">
            <h3 class="card-title">Supplier & Invoice Details</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="form-label">Supplier *</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Select Supplier --</option>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?php echo $s['supplier_id']; ?>"><?php echo escape_html($s['supplier_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Supplier Invoice No</label>
                    <input type="text" name="supplier_invoice_no" class="form-control" placeholder="e.g. INV-2024-001">
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Purchase Date *</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="1" placeholder="Optional reference notes..."></textarea>
            </div>
        </div>
    </div>

    <!-- 2. ITEMS ENTRY -->
    <div class="card mb-20">
        <div class="card-header">
            <h3 class="card-title">Add Items</h3>
        </div>
        <div class="card-body">
            <!-- Add Item Row -->
            <div class="form-row align-items-end" style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div class="form-group col-md-4">
                    <label class="form-label">Select Product</label>
                    <select id="prod_select" class="form-control">
                        <option value="">-- Choose Product --</option>
                        <?php foreach($products as $p): ?>
                            <option value="<?php echo $p['product_id']; ?>" 
                                    data-name="<?php echo escape_html($p['product_name']); ?>"
                                    data-tracking="<?php echo $p['tracking_type']; ?>">
                                <?php echo escape_html($p['product_name']); ?> (<?php echo $p['product_code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Unit Cost (₹)</label>
                    <input type="number" id="prod_cost" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group col-md-2" id="qty_group">
                    <label class="form-label">Quantity</label>
                    <input type="number" id="prod_qty" class="form-control" placeholder="1" min="1">
                </div>
                <div class="form-group col-md-2">
                    <button type="button" class="btn btn-success w-100" onclick="addItem()">
                        <span class="btn-icon">＋</span> Add
                    </button>
                </div>
            </div>

            <!-- Dynamic Input Area (Hidden by default) -->
            <div id="tracking_inputs" class="tracking-panel" style="display:none;">
                <h4 style="margin:0 0 15px 0; font-size:14px; color:#475569; position:relative; padding-left:20px;">
                    <span style="position:absolute; left:0; color:#3b82f6;">ℹ</span> Product Tracking Details
                </h4>
                
                <div id="batch_inputs" style="display:none;">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Batch Number *</label>
                            <input type="text" id="batch_no" class="form-control" placeholder="Enter Batch No">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" id="expiry_date" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div id="serial_inputs" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Serial Numbers / IMEIs *</label>
                        <textarea id="serial_numbers" class="form-control" rows="3" placeholder="Enter Serial Numbers separated by comma (e.g. SN001, SN002...)"></textarea>
                        <small class="text-muted">Enter serial keys separated by comma. Count should match Quantity entered.</small>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="data-table table-striped">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Info</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="items_table">
                        <!-- Items will be added here -->
                    </tbody>
                </table>
            </div>
            
            <div id="empty_msg" class="text-center py-4 text-muted">
                No items added yet.
            </div>
        </div>
    </div>

    <!-- 3. FOOTER ACTIONS -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="received">Received (Updates Stock Immediately)</option>
                        <option value="pending">Pending (Draft)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <!-- Spacer -->
                </div>
                <div class="col-md-4 text-right">
                     <div class="purchase-total-card">
                        <small>Grand Total</small>
                        <h3 id="grand_total_display">₹0.00</h3>
                     </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-right">
                    <a href="index.php" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <span class="btn-icon">✓</span> Save Record
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let items = [];
const trackingDiv = document.getElementById('tracking_inputs');
const batchDiv = document.getElementById('batch_inputs');
const serialDiv = document.getElementById('serial_inputs');
const qtyGroup = document.getElementById('qty_group');
const prodQty = document.getElementById('prod_qty');

document.getElementById('prod_select').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const type = opt.getAttribute('data-tracking');
    
    // Reset inputs
    document.getElementById('batch_no').value = '';
    document.getElementById('expiry_date').value = '';
    document.getElementById('serial_numbers').value = '';
    
    if (type === 'batch') {
        trackingDiv.style.display = 'block';
        batchDiv.style.display = 'block';
        serialDiv.style.display = 'none';
        prodQty.readOnly = false;
    } else if (type === 'serial') {
        trackingDiv.style.display = 'block';
        batchDiv.style.display = 'none';
        serialDiv.style.display = 'block';
        prodQty.readOnly = false; 
    } else {
        trackingDiv.style.display = 'none';
        prodQty.readOnly = false;
    }
});

function addItem() {
    const select = document.getElementById('prod_select');
    const opt = select.options[select.selectedIndex];
    if(!select.value) { alert('Please select a product'); return; }

    const pid = select.value;
    const pname = opt.getAttribute('data-name');
    const type = opt.getAttribute('data-tracking');
    const cost = parseFloat(document.getElementById('prod_cost').value) || 0;
    
    let qty = parseFloat(prodQty.value) || 0;
    let batch = '', expiry = '', serials = '';
    let trackingDisplay = '<span class="text-muted">-</span>';

    if (type === 'batch') {
        batch = document.getElementById('batch_no').value;
        expiry = document.getElementById('expiry_date').value;
        if(!batch) { alert('Enter Batch No'); return; }
        trackingDisplay = `<strong>Batch:</strong> ${batch} <br><small>Exp: ${expiry}</small>`;
    } 
    else if (type === 'serial') {
        serials = document.getElementById('serial_numbers').value;
        if(!serials) { alert('Enter Serial Numbers'); return; }
        // Auto-count
        const arr = serials.split(',').filter(s => s.trim().length > 0);
        if (arr.length !== qty) {
            if(!confirm(`You entered ${arr.length} serials but Quantity is ${qty}. Update Quantity to ${arr.length}?`)) return;
            qty = arr.length;
        }
        trackingDisplay = `<div style="max-width:200px; overflow-wrap:break-word;"><small><strong>SN:</strong> ${serials}</small></div>`;
    }
    
    if (qty <= 0) { alert('Invalid Qty'); return; }

    items.push({
        product_id: pid,
        product_name: pname,
        tracking_type: type,
        quantity: qty,
        unit_cost: cost,
        batch_no: batch,
        expiry_date: expiry,
        serial_numbers: serials,
        display_tracking: trackingDisplay
    });

    renderItems();
    
    // Reset form partially
    select.value = '';
    document.getElementById('prod_cost').value = '';
    prodQty.value = '';
    trackingDiv.style.display = 'none';
}

function removeItem(index) {
    items.splice(index, 1);
    renderItems();
}

function renderItems() {
    const tbody = document.getElementById('items_table');
    const emptyMsg = document.getElementById('empty_msg');
    
    tbody.innerHTML = '';
    let total = 0;
    
    if(items.length > 0) {
        emptyMsg.style.display = 'none';
    } else {
        emptyMsg.style.display = 'block';
    }
    
    items.forEach((item, index) => {
        const lineTotal = item.quantity * item.unit_cost;
        total += lineTotal;
        
        tbody.innerHTML += `
            <tr>
                <td><strong>${item.product_name}</strong></td>
                <td>${item.display_tracking}</td>
                <td>${item.quantity}</td>
                <td>₹${item.unit_cost.toFixed(2)}</td>
                <td>₹${lineTotal.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger icon-btn" onclick="removeItem(${index})">🗑️</button></td>
            </tr>
        `;
    });
    
    document.getElementById('grand_total_display').innerText = '₹' + total.toFixed(2);
    document.getElementById('items_data').value = JSON.stringify(items);
}
</script>

<?php require_once '../includes/footer.php'; ?>
