<?php
/**
 * ============================================================================
 * SMTP SETTINGS PAGE
 * ============================================================================
 * Purpose: Configure email server settings
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require Admin Role
require_role('admin');

$page_title = "SMTP Settings";
require_once '../includes/header.php';

// Fetch current settings
$smtp = db_fetch_one($connection, "SELECT * FROM smtp_settings LIMIT 1");

// Process Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = sanitize_sql($connection, $_POST['host']);
    $port = sanitize_sql($connection, $_POST['port']);
    $username = sanitize_sql($connection, $_POST['username']);
    $password = $_POST['password']; // Do not sanitize if special chars
    $encryption = sanitize_sql($connection, $_POST['encryption']);
    $from_email = sanitize_sql($connection, $_POST['from_email']);
    $from_name = sanitize_sql($connection, $_POST['from_name']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    // Only update password if provided, else keep existing
    $pass_sql = !empty($password) ? ", password = '" . mysqli_real_escape_string($connection, $password) . "'" : "";
    
    if ($smtp) {
        $query = "UPDATE smtp_settings SET 
                  host = '$host', port = '$port', username = '$username' $pass_sql, 
                  encryption = '$encryption', from_email = '$from_email', from_name = '$from_name', 
                  status = '$status' 
                  WHERE id = {$smtp['id']}";
    } else {
        $query = "INSERT INTO smtp_settings (host, port, username, password, encryption, from_email, from_name, status) 
                  VALUES ('$host', '$port', '$username', '" . mysqli_real_escape_string($connection, $password) . "', 
                          '$encryption', '$from_email', '$from_name', '$status')";
    }
    
    if (db_execute($connection, $query)) {
        log_activity($connection, "Update SMTP Settings", "Updated by " . $_SESSION['username']);
        set_success_message("SMTP Settings updated successfully!");
        
        // Refresh data
        $smtp = db_fetch_one($connection, "SELECT * FROM smtp_settings LIMIT 1");
    } else {
        set_error_message("Failed to update settings.");
    }
}

// Test Email Send
if (isset($_POST['test_email'])) {
    $test_to = sanitize_sql($connection, $_POST['test_to']);
    $result = send_email($connection, $test_to, "Test Email from Billit", "<h1>It Works!</h1><p>Your SMTP settings are configured correctly.</p>");
    
    if ($result['success']) {
        set_success_message($result['message']);
    } else {
        set_error_message($result['message']);
    }
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">📧</span>
            SMTP Configuration
        </h2>
        <p class="page-description">Configure your email server settings for sending invoices and notifications.</p>
    </div>
    <div class="page-actions">
        <a href="company_settings.php" class="btn btn-secondary">
            <span class="btn-icon">⚙️</span> Company Settings
        </a>
    </div>
</div>

<?php echo display_messages(); ?>

<div class="dashboard-grid">
    <!-- SMTP Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Server Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group col-md-8 mb-20">
                        <label class="form-label">SMTP Host *</label>
                        <input type="text" name="host" class="form-control" required value="<?php echo escape_html($smtp['host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                    </div>
                    
                    <div class="form-group col-md-4 mb-20">
                        <label class="form-label">SMTP Port *</label>
                        <input type="number" name="port" class="form-control" required value="<?php echo escape_html($smtp['port'] ?? '587'); ?>" placeholder="587">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6 mb-20">
                        <label class="form-label">Username (Email) *</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo escape_html($smtp['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group col-md-6 mb-20">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave empty to keep current">
                    </div>
                </div>
                
                <div class="form-group mb-20">
                    <label class="form-label">Encryption Protocol *</label>
                    <div style="display: flex; gap: 20px;">
                        <label><input type="radio" name="encryption" value="tls" <?php echo ($smtp['encryption'] ?? '') == 'tls' ? 'checked' : ''; ?>> TLS</label>
                        <label><input type="radio" name="encryption" value="ssl" <?php echo ($smtp['encryption'] ?? '') == 'ssl' ? 'checked' : ''; ?>> SSL</label>
                        <label><input type="radio" name="encryption" value="none" <?php echo ($smtp['encryption'] ?? '') == 'none' ? 'checked' : ''; ?>> None</label>
                    </div>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: 20px 0;">
                
                <div class="form-row">
                    <div class="form-group col-md-6 mb-20">
                        <label class="form-label">From Email *</label>
                        <input type="email" name="from_email" class="form-control" required value="<?php echo escape_html($smtp['from_email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group col-md-6 mb-20">
                        <label class="form-label">From Name *</label>
                        <input type="text" name="from_name" class="form-control" required value="<?php echo escape_html($smtp['from_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group mb-20">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="active" <?php echo ($smtp['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($smtp['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </form>
        </div>
    </div>
    
    <!-- Test Email -->
    <div class="card" style="height: fit-content;">
        <div class="card-header">
            <h3 class="card-title">🧪 Test Configuration</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-muted mb-20">Send a test email to verify your settings are working correctly.</p>
            
            <form method="POST" action="">
                <div class="form-group mb-20">
                    <label class="form-label">Test Recipient Email</label>
                    <input type="email" name="test_to" class="form-control" required placeholder="youremail@example.com">
                </div>
                <button type="submit" name="test_email" class="btn btn-secondary w-100">Send Test Email</button>
            </form>
            
            <div class="mt-30 text-sm">
                <strong>Notes for Gmail Users:</strong>
                <ul style="padding-left: 20px; color: var(--gray-600); margin-top: 5px;">
                    <li>Use <code>smtp.gmail.com</code></li>
                    <li>Port <code>587</code> for TLS</li>
                    <li>You must use an <strong>App Password</strong> instead of your real password if using 2FA.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
