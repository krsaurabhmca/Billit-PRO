<?php
/**
 * ============================================================================
 * EDIT USER PAGE
 * ============================================================================
 * Purpose: Update user details and roles
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require Admin Role
require_role('admin');

$page_title = "Edit User";
require_once '../includes/header.php';

// Get ID
if (!isset($_GET['id']) || !validate_numeric($_GET['id'])) {
    redirect('users.php');
}
$user_id = sanitize_sql($connection, $_GET['id']);

// Fetch User
$user = db_fetch_one($connection, "SELECT * FROM users WHERE user_id = '$user_id'");

if (!$user) {
    set_error_message("User not found.");
    redirect('users.php');
}

// Process Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_sql($connection, $_POST['full_name']);
    $email = sanitize_sql($connection, $_POST['email']);
    $role = sanitize_sql($connection, $_POST['role']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    // Password Update (Optional)
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$hashed_password'";
    }

    $query = "UPDATE users SET full_name = '$full_name', email = '$email', role = '$role', status = '$status' $password_sql 
              WHERE user_id = '$user_id'";
    
    if (db_execute($connection, $query)) {
        set_success_message("User updated successfully!");
        redirect('users.php');
    } else {
        set_error_message("Failed to update user.");
    }
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">✏️</span>
            Edit User: <?php echo escape_html($user['username']); ?>
        </h2>
    </div>
    <div class="page-actions">
        <a href="users.php" class="btn btn-secondary">
            <span class="btn-icon">←</span> Back
        </a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group mb-20">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required value="<?php echo escape_html($user['full_name']); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" disabled value="<?php echo escape_html($user['username']); ?>">
                    <small class="text-muted">Username cannot be changed.</small>
                </div>
                
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo escape_html($user['email']); ?>">
                </div>
            </div>
            
            <div class="form-group mb-20">
                <label class="form-label">New Password (Empty to keep current)</label>
                <input type="password" name="password" class="form-control" minlength="6" placeholder="******">
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-control form-select">
                        <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="manager" <?php echo $user['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Update User</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
