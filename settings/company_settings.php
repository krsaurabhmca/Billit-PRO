<?php
/**
 * ============================================================================
 * COMPANY SETTINGS PAGE
 * ============================================================================
 * Purpose: Configure company details and GST information
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "Company Settings";

// Include header
require_once '../includes/header.php';

// Require admin role
if (!has_role('admin')) {
    set_error_message("Access denied. Admin privileges required.");
    redirect('../index.php');
}

// ============================================================================
// PROCESS FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize_sql($connection, $_POST['company_name']);
    $company_address = sanitize_sql($connection, $_POST['company_address']);
    $company_city = sanitize_sql($connection, $_POST['company_city']);
    $company_state = sanitize_sql($connection, $_POST['company_state']);
    $company_state_code = sanitize_sql($connection, $_POST['company_state_code']);
    $company_pincode = sanitize_sql($connection, $_POST['company_pincode']);
    $company_gstin = sanitize_sql($connection, $_POST['company_gstin']);
    $company_pan = sanitize_sql($connection, $_POST['company_pan']);
    $company_phone = sanitize_sql($connection, $_POST['company_phone']);
    $company_email = sanitize_sql($connection, $_POST['company_email']);
    $invoice_prefix = sanitize_sql($connection, $_POST['invoice_prefix']);
    $terms_conditions = sanitize_sql($connection, $_POST['terms_conditions']);
    $bank_name = sanitize_sql($connection, $_POST['bank_name']);
    $bank_account_number = sanitize_sql($connection, $_POST['bank_account_number']);
    $bank_ifsc = sanitize_sql($connection, $_POST['bank_ifsc']);
    $bank_branch = sanitize_sql($connection, $_POST['bank_branch']);
    
    $has_error = false;
    
    // Validate GSTIN
    if (!empty($company_gstin) && !validate_gstin($company_gstin)) {
        set_error_message("Invalid GSTIN format.");
        $has_error = true;
    }
    
    if (!$has_error) {
        // Check if settings exist
        $check_query = "SELECT setting_id FROM company_settings LIMIT 1";
        $existing = db_fetch_one($connection, $check_query);
        
        if ($existing) {
            // Update existing settings
            $update_query = "UPDATE company_settings SET
                            company_name = '{$company_name}',
                            company_address = '{$company_address}',
                            company_city = '{$company_city}',
                            company_state = '{$company_state}',
                            company_state_code = '{$company_state_code}',
                            company_pincode = '{$company_pincode}',
                            company_gstin = '{$company_gstin}',
                            company_pan = '{$company_pan}',
                            company_phone = '{$company_phone}',
                            company_email = '{$company_email}',
                            invoice_prefix = '{$invoice_prefix}',
                            terms_conditions = '{$terms_conditions}',
                            bank_name = '{$bank_name}',
                            bank_account_number = '{$bank_account_number}',
                            bank_ifsc = '{$bank_ifsc}',
                            bank_branch = '{$bank_branch}'
                            WHERE setting_id = '{$existing['setting_id']}'";
            
            if (db_execute($connection, $update_query)) {
                set_success_message("Company settings updated successfully!");
                redirect($_SERVER['PHP_SELF']);
            } else {
                set_error_message("Failed to update settings.");
            }
        } else {
            // Insert new settings
            $insert_query = "INSERT INTO company_settings
                            (company_name, company_address, company_city, company_state, company_state_code,
                             company_pincode, company_gstin, company_pan, company_phone, company_email,
                             invoice_prefix, terms_conditions, bank_name, bank_account_number, bank_ifsc, bank_branch)
                            VALUES
                            ('{$company_name}', '{$company_address}', '{$company_city}', '{$company_state}',
                             '{$company_state_code}', '{$company_pincode}', '{$company_gstin}', '{$company_pan}',
                             '{$company_phone}', '{$company_email}', '{$invoice_prefix}', '{$terms_conditions}',
                             '{$bank_name}', '{$bank_account_number}', '{$bank_ifsc}', '{$bank_branch}')";
            
            if (db_execute($connection, $insert_query)) {
                set_success_message("Company settings saved successfully!");
                redirect($_SERVER['PHP_SELF']);
            } else {
                set_error_message("Failed to save settings.");
            }
        }
    }
}

// ============================================================================
// FETCH CURRENT SETTINGS
// ============================================================================

$settings_query = "SELECT * FROM company_settings LIMIT 1";
$settings = db_fetch_one($connection, $settings_query);

// Get Indian states
$indian_states = get_indian_states();
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">⚙️</span>
        Company Settings
    </h2>
</div>

<!-- ================================================================ -->
<!-- SETTINGS FORM -->
<!-- ================================================================ -->
<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    
    <!-- Company Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Company Information</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="company_name" class="form-label">Company Name *</label>
                <input type="text" id="company_name" name="company_name" class="form-control" 
                       value="<?php echo $settings ? escape_html($settings['company_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="company_address" class="form-label">Address</label>
                <textarea id="company_address" name="company_address" class="form-control" rows="2"><?php echo $settings ? escape_html($settings['company_address']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="company_city" class="form-label">City</label>
                    <input type="text" id="company_city" name="company_city" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['company_city']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="company_state_code" class="form-label">State *</label>
                    <select id="company_state_code" name="company_state_code" class="form-control" required onchange="updateStateName()">
                        <option value="">Select State</option>
                        <?php foreach ($indian_states as $code => $name): ?>
                            <option value="<?php echo $code; ?>" 
                                    <?php echo ($settings && $settings['company_state_code'] === $code) ? 'selected' : ''; ?>>
                                <?php echo $code . ' - ' . $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="company_state" name="company_state" 
                           value="<?php echo $settings ? escape_html($settings['company_state']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="company_pincode" class="form-label">Pincode</label>
                    <input type="text" id="company_pincode" name="company_pincode" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['company_pincode']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="company_phone" class="form-label">Phone</label>
                    <input type="text" id="company_phone" name="company_phone" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['company_phone']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-6">
                    <label for="company_email" class="form-label">Email</label>
                    <input type="email" id="company_email" name="company_email" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['company_email']) : ''; ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- GST Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">GST Information</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="company_gstin" class="form-label">GSTIN (15 characters) *</label>
                    <input type="text" id="company_gstin" name="company_gstin" class="form-control" 
                           maxlength="15" placeholder="e.g., 27AAAAA0000A1Z5"
                           value="<?php echo $settings ? escape_html($settings['company_gstin']) : ''; ?>" required>
                    <small class="form-text">Format: 2-digit state code + 10-char PAN + 3 chars</small>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="company_pan" class="form-label">PAN Number</label>
                    <input type="text" id="company_pan" name="company_pan" class="form-control" 
                           maxlength="10" placeholder="e.g., AAAAA0000A"
                           value="<?php echo $settings ? escape_html($settings['company_pan']) : ''; ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Invoice Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Invoice Settings</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                <input type="text" id="invoice_prefix" name="invoice_prefix" class="form-control" 
                       maxlength="10" placeholder="e.g., INV"
                       value="<?php echo $settings ? escape_html($settings['invoice_prefix']) : 'INV'; ?>">
                <small class="form-text">Invoices will be numbered as: PREFIX0001, PREFIX0002, etc.</small>
            </div>
            
            <div class="form-group">
                <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="4"
                          placeholder="Enter default terms and conditions for invoices"><?php echo $settings ? escape_html($settings['terms_conditions']) : ''; ?></textarea>
            </div>
        </div>
    </div>
    
    <!-- Bank Details -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bank Details (Optional)</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['bank_name']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-6">
                    <label for="bank_account_number" class="form-label">Account Number</label>
                    <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['bank_account_number']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="bank_ifsc" class="form-label">IFSC Code</label>
                    <input type="text" id="bank_ifsc" name="bank_ifsc" class="form-control" 
                           maxlength="11" placeholder="e.g., SBIN0001234"
                           value="<?php echo $settings ? escape_html($settings['bank_ifsc']) : ''; ?>">
                </div>
                
                <div class="form-group col-md-6">
                    <label for="bank_branch" class="form-label">Branch</label>
                    <input type="text" id="bank_branch" name="bank_branch" class="form-control" 
                           value="<?php echo $settings ? escape_html($settings['bank_branch']) : ''; ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="card">
        <div class="card-body">
            <button type="submit" class="btn btn-primary">
                <span class="btn-icon">✓</span>
                Save Settings
            </button>
        </div>
    </div>
</form>

<script>
function updateStateName() {
    const stateCode = document.getElementById('company_state_code').value;
    const stateSelect = document.getElementById('company_state_code');
    const selectedOption = stateSelect.options[stateSelect.selectedIndex];
    const stateName = selectedOption.text.split(' - ')[1] || '';
    
    document.getElementById('company_state').value = stateName;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('company_state_code').value) {
        updateStateName();
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
