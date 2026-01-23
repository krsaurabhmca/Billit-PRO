<?php
/**
 * ============================================================================
 * SETUP WIZARD
 * ============================================================================
 * Purpose: Guide user through initial system setup
 */

// We don't use standard header.php because we want a focused layout
// but we need the config and functions
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/functions_wizard.php';

require_login(); // Still need login
if (!has_role('admin')) {
    redirect('../index.php');
}

// Handle Form Submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // --- STEP 1: COMPANY SETTINGS ---
    if ($action === 'save_company') {
        $company_name = sanitize_sql($connection, $_POST['company_name']);
        $invoice_prefix = sanitize_sql($connection, $_POST['invoice_prefix']);
        $state_code = sanitize_sql($connection, $_POST['company_state_code']);
        
        // Basic validation
        if (empty($company_name) || empty($state_code)) {
            $message = "Company Name and State are required.";
            $message_type = "error";
        } else {
            // Get state name
            $states = get_indian_states();
            $state_name = $states[$state_code] ?? '';
            $state_name = sanitize_sql($connection, $state_name);
            
            // Check if exists
            $exists = db_fetch_one($connection, "SELECT setting_id FROM company_settings LIMIT 1");
            
            if ($exists) {
                $query = "UPDATE company_settings SET 
                          company_name='$company_name', 
                          company_state_code='$state_code',
                          company_state='$state_name',
                          invoice_prefix='$invoice_prefix' 
                          WHERE setting_id={$exists['setting_id']}";
            } else {
                $query = "INSERT INTO company_settings (company_name, company_state_code, company_state, invoice_prefix) 
                          VALUES ('$company_name', '$state_code', '$state_name', '$invoice_prefix')";
            }
            
            if (db_execute($connection, $query)) {
                $message = "Company settings saved!";
                $message_type = "success";
            } else {
                $message = "Error saving settings.";
                $message_type = "error";
            }
        }
    }
    
    // --- STEP 2: ADD PRODUCT ---
    if ($action === 'save_product') {
        $name = sanitize_sql($connection, $_POST['product_name']);
        $price = sanitize_sql($connection, $_POST['unit_price']);
        
        if (empty($name) || empty($price)) {
            $message = "Product Name and Price are required.";
            $message_type = "error";
        } else {
            // Create default category if needed
            $cat_id = 1;
            $cat_check = db_fetch_one($connection, "SELECT category_id FROM categories LIMIT 1");
            if (!$cat_check) {
                db_execute($connection, "INSERT INTO categories (category_name) VALUES ('General')");
                $cat_id = db_insert_id($connection);
            } else {
                $cat_id = $cat_check['category_id'];
            }
            
            $code = 'PROD' . rand(100,999);
            
            $query = "INSERT INTO products (product_code, product_name, category_id, unit_price, status) 
                      VALUES ('$code', '$name', $cat_id, '$price', 'active')";
            
            if (db_execute($connection, $query)) {
                $message = "Product saved!";
                $message_type = "success";
            } else {
                $message = "Error saving product.";
                $message_type = "error";
            }
        }
    }
    
    // --- STEP 3: ADD CUSTOMER ---
    if ($action === 'save_customer') {
        $name = sanitize_sql($connection, $_POST['customer_name']);
        $phone = sanitize_sql($connection, $_POST['phone']);
        
        if (empty($name)) {
            $message = "Customer Name is required.";
            $message_type = "error";
        } else {
            $query = "INSERT INTO customers (customer_name, phone, status) VALUES ('$name', '$phone', 'active')";
             if (db_execute($connection, $query)) {
                $message = "Customer saved!";
                $message_type = "success";
            } else {
                $message = "Error saving customer.";
                $message_type = "error";
            }
        }
    }
}

// Determine Current Step
$status = check_setup_status($connection);
$current_step = 1;
if ($status['company_configured']) $current_step = 2;
if ($status['company_configured'] && $status['has_products']) $current_step = 3;
if ($status['company_configured'] && $status['has_products'] && $status['has_customers']) $current_step = 4;

// Override step if user wants to see logic flow or forcing 'guide'
if (isset($_GET['step'])) $current_step = (int)$_GET['step'];

// Fetch Indian States for Step 1
$indian_states = get_indian_states();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f8fafc; display: block; height: auto; min-height: 100vh; }
        .wizard-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            min-height: 500px;
        }
        .wizard-sidebar {
            width: 280px;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
        }
        .wizard-content {
            flex: 1;
            padding: 40px;
            position: relative;
        }
        
        /* Steps List */
        .step-list { list-style: none; padding: 0; margin-top: 40px; }
        .step-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            opacity: 0.6;
            transition: 0.3s;
        }
        .step-item.active { opacity: 1; font-weight: 600; }
        .step-item.completed { opacity: 1; }
        
        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 14px;
        }
        .step-item.completed .step-num { background: white; color: #4338ca; }
        .step-item.active .step-num { background: rgba(255,255,255,0.2); }
        
        .wizard-title { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        .wizard-desc { color: #64748b; margin-bottom: 30px; }
        
        .guide-card {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #4f46e5;
        }
        .guide-step { margin-bottom: 15px; }
        .guide-step h4 { margin: 0 0 5px; color: #334155; }
        .guide-step p { margin: 0; font-size: 13px; color: #64748b; }
        
        .wizard-footer {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="wizard-container">
    <!-- Sidebar -->
    <div class="wizard-sidebar">
        <h1 style="font-size: 22px; display:flex; align-items:center; gap:10px;">
            <span>🚀</span> Billit Setup
        </h1>
        <p style="font-size: 13px; opacity: 0.8; margin-top:10px;">Complete these steps to start billing.</p>
        
        <ul class="step-list">
            <li class="step-item <?php echo $current_step > 1 ? 'completed' : ($current_step == 1 ? 'active' : ''); ?>">
                <span class="step-num"><?php echo $current_step > 1 ? '✓' : '1'; ?></span>
                <span>Company Profile</span>
            </li>
            <li class="step-item <?php echo $current_step > 2 ? 'completed' : ($current_step == 2 ? 'active' : ''); ?>">
                <span class="step-num"><?php echo $current_step > 2 ? '✓' : '2'; ?></span>
                <span>Add Product</span>
            </li>
            <li class="step-item <?php echo $current_step > 3 ? 'completed' : ($current_step == 3 ? 'active' : ''); ?>">
                <span class="step-num"><?php echo $current_step > 3 ? '✓' : '3'; ?></span>
                <span>Add Customer</span>
            </li>
            <li class="step-item <?php echo $current_step == 4 ? 'active' : ''; ?>">
                <span class="step-num">4</span>
                <span>Quick Guide</span>
            </li>
        </ul>
        
        <div style="margin-top:auto; font-size:12px; opacity:0.6;">
            Having trouble? <a href="../help.php" style="color:white; text-decoration:underline;">View Documentation</a>
        </div>
    </div>
    
    <!-- Content -->
    <div class="wizard-content">
        
        <?php if (!empty($message)): ?>
            <div class="alert-box alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    
        <!-- STEP 1: COMPANY -->
        <?php if ($current_step == 1): ?>
        <form method="POST">
            <h2 class="wizard-title">Tell us about your Business</h2>
            <p class="wizard-desc">We need these details to generate valid invoices.</p>
            <input type="hidden" name="action" value="save_company">
            
            <div class="form-group">
                <label class="form-label">Company Name *</label>
                <input type="text" name="company_name" class="form-control" placeholder="e.g. My Retail Shop" required>
            </div>
            
            <div class="form-row">
                 <div class="form-group col-md-8">
                    <label class="form-label">State * (Crucial for GST)</label>
                    <select name="company_state_code" class="form-control" required>
                        <option value="">Select State</option>
                        <?php foreach ($indian_states as $code => $name): ?>
                            <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="form-group col-md-4">
                    <label class="form-label">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" class="form-control" value="INV" placeholder="INV">
                </div>
            </div>
            
            <div class="wizard-footer">
                <button type="submit" class="btn btn-primary">Save & Continue &rarr;</button>
            </div>
        </form>
        <?php endif; ?>

        <!-- STEP 2: PRODUCT -->
        <?php if ($current_step == 2): ?>
        <form method="POST">
            <h2 class="wizard-title">Add your first Product</h2>
            <p class="wizard-desc">Let's add at least one item to sell.</p>
            <input type="hidden" name="action" value="save_product">
            
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="product_name" class="form-control" placeholder="e.g. Wireless Mouse" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Selling Price (₹) *</label>
                <input type="number" name="unit_price" class="form-control" placeholder="0.00" step="0.01" required>
            </div>
            
            <div class="wizard-footer">
                 <!-- Allow skipping if data exists but maybe logic didn't catch it, or user wants to skip -->
                <a href="?step=3" class="btn btn-secondary">Skip</a>
                <button type="submit" class="btn btn-primary">Add Product &rarr;</button>
            </div>
        </form>
        <?php endif; ?>

        <!-- STEP 3: CUSTOMER -->
        <?php if ($current_step == 3): ?>
        <form method="POST">
            <h2 class="wizard-title">Add a Customer</h2>
            <p class="wizard-desc">Add a regular customer or create a 'Walk-in' profile.</p>
            <input type="hidden" name="action" value="save_customer">
            
            <div class="form-group">
                <label class="form-label">Customer Name *</label>
                <input type="text" name="customer_name" class="form-control" placeholder="e.g. Walk-in Customer" value="Walk-in Customer" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="Optional">
            </div>
            
            <div class="wizard-footer">
                 <a href="?step=4" class="btn btn-secondary">Skip</a>
                <button type="submit" class="btn btn-primary">Add Customer &rarr;</button>
            </div>
        </form>
        <?php endif; ?>

        <!-- STEP 4: GUIDE -->
        <?php if ($current_step == 4): ?>
        <div>
            <h2 class="wizard-title">🎉 You are all set!</h2>
            <p class="wizard-desc">Here is a quick guide to help you get started:</p>
            
            <div class="guide-card">
                <div class="guide-step">
                    <h4>1. creating Invoices</h4>
                    <p>Go to <b>Sales > Create Invoice</b>. Select a customer, add products, and click Finalize to generate a bill.</p>
                </div>
                <div class="guide-step">
                    <h4>2. Manage Inventory</h4>
                    <p>Use <b>Purchase > Record Purchase</b> to add stock when you buy from suppliers.</p>
                </div>
                <div class="guide-step">
                    <h4>3. Reports</h4>
                    <p>Check <b>Reports</b> to see your daily sales, profit/loss, and stock alerts.</p>
                </div>
            </div>
            
            <div class="alert-box alert-success" style="display:flex; gap:10px; align-items:center;">
                <span style="font-size:24px">👍</span>
                <div>
                    <strong>Ready to go!</strong><br>
                    Your basic configuration is complete.
                </div>
            </div>

            <div class="wizard-footer">
                <a href="../index.php" class="btn btn-primary btn-block">Go to Dashboard &rarr;</a>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

</body>
</html>
