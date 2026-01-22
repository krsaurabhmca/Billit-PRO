<?php
/**
 * ============================================================================
 * ADD USER PAGE
 * ============================================================================
 * Purpose: Create new user accounts
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require Admin Role
require_role('admin');

$page_title = "Add User";
require_once '../includes/header.php';

// Process Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_sql($connection, $_POST['full_name']);
    $username = sanitize_sql($connection, $_POST['username']);
    $email = sanitize_sql($connection, $_POST['email']);
    $password = $_POST['password'];
    $role = sanitize_sql($connection, $_POST['role']);
    $status = sanitize_sql($connection, $_POST['status']);
    
    // Validation
    if (empty($username) || empty($password) || empty($email)) {
        set_error_message("All fields are required.");
    } else {
        // Check if username already exists
        $check_query = "SELECT user_id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = db_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            set_error_message("Username or Email already exists.");
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert User
            $query = "INSERT INTO users (username, password, full_name, email, role, status) 
                      VALUES ('$username', '$hashed_password', '$full_name', '$email', '$role', '$status')";
            
            if (db_execute($connection, $query)) {
                set_success_message("User '$username' created successfully!");
                redirect('users.php');
            } else {
                set_error_message("Failed to create user.");
            }
        }
    }
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">👤</span>
            Add New User
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
                <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required placeholder="johndoe">
                </div>
                
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                </div>
            </div>
            
            <div class="form-group mb-20">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required minlength="6" placeholder="******">
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-control form-select">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group col-md-6 mb-20">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Create User</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
