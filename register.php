<?php
/**
 * ============================================================================
 * USER REGISTRATION PAGE (Admin Only)
 * ============================================================================
 * Purpose: Allow administrators to create new user accounts
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Set page title
$page_title = "User Registration";

// Include header (includes config, functions, and authentication check)
require_once 'includes/header.php';

// Require admin role to access this page
require_role('admin');

// ============================================================================
// PROCESS REGISTRATION FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $username = sanitize_sql($connection, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize_sql($connection, $_POST['full_name']);
    $email = sanitize_sql($connection, $_POST['email']);
    $role = sanitize_sql($connection, $_POST['role']);
    
    // Initialize error flag
    $has_error = false;
    
    // Validate inputs
    if (empty($username)) {
        set_error_message("Username is required.");
        $has_error = true;
    } elseif (strlen($username) < 3) {
        set_error_message("Username must be at least 3 characters long.");
        $has_error = true;
    }
    
    if (empty($password)) {
        set_error_message("Password is required.");
        $has_error = true;
    } elseif (strlen($password) < 6) {
        set_error_message("Password must be at least 6 characters long.");
        $has_error = true;
    }
    
    if ($password !== $confirm_password) {
        set_error_message("Passwords do not match.");
        $has_error = true;
    }
    
    if (empty($full_name)) {
        set_error_message("Full name is required.");
        $has_error = true;
    }
    
    if (empty($email)) {
        set_error_message("Email is required.");
        $has_error = true;
    } elseif (!validate_email($email)) {
        set_error_message("Invalid email format.");
        $has_error = true;
    }
    
    if (empty($role)) {
        set_error_message("Role is required.");
        $has_error = true;
    }
    
    // Check if username already exists
    if (!$has_error) {
        $check_query = "SELECT user_id FROM users WHERE username = '{$username}' LIMIT 1";
        $check_result = db_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            set_error_message("Username already exists. Please choose a different username.");
            $has_error = true;
        }
    }
    
    // If no errors, create new user
    if (!$has_error) {
        // Hash the password using bcrypt
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert new user into database
        $insert_query = "INSERT INTO users (username, password, full_name, email, role, status) 
                        VALUES ('{$username}', '{$hashed_password}', '{$full_name}', '{$email}', '{$role}', 'active')";
        
        if (db_execute($connection, $insert_query)) {
            set_success_message("User '{$username}' has been successfully registered.");
            
            // Clear form by redirecting
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_error_message("Failed to register user. Please try again.");
        }
    }
}

// ============================================================================
// FETCH ALL USERS FOR DISPLAY
// ============================================================================

$users_query = "SELECT user_id, username, full_name, email, role, status, created_at 
                FROM users 
                ORDER BY created_at DESC";
$users_result = db_query($connection, $users_query);
?>

<!-- ================================================================ -->
<!-- PAGE HEADER -->
<!-- ================================================================ -->
<div class="page-header">
    <h2 class="page-title">
        <span class="page-icon">👤</span>
        User Management
    </h2>
    <p class="page-description">Create and manage user accounts</p>
</div>

<!-- ================================================================ -->
<!-- REGISTRATION FORM -->
<!-- ================================================================ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Register New User</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form-horizontal">
            <div class="form-row">
                <!-- Username -->
                <div class="form-group col-md-6">
                    <label for="username" class="form-label">Username *</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Enter username"
                        value="<?php echo isset($_POST['username']) ? escape_html($_POST['username']) : ''; ?>"
                        required
                    >
                </div>
                
                <!-- Full Name -->
                <div class="form-group col-md-6">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-control" 
                        placeholder="Enter full name"
                        value="<?php echo isset($_POST['full_name']) ? escape_html($_POST['full_name']) : ''; ?>"
                        required
                    >
                </div>
            </div>
            
            <div class="form-row">
                <!-- Email -->
                <div class="form-group col-md-6">
                    <label for="email" class="form-label">Email *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Enter email address"
                        value="<?php echo isset($_POST['email']) ? escape_html($_POST['email']) : ''; ?>"
                        required
                    >
                </div>
                
                <!-- Role -->
                <div class="form-group col-md-6">
                    <label for="role" class="form-label">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="manager" <?php echo (isset($_POST['role']) && $_POST['role'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                        <option value="staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <!-- Password -->
                <div class="form-group col-md-6">
                    <label for="password" class="form-label">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter password (min 6 characters)"
                        required
                    >
                </div>
                
                <!-- Confirm Password -->
                <div class="form-group col-md-6">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="Re-enter password"
                        required
                    >
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span>
                    Register User
                </button>
                <button type="reset" class="btn btn-secondary">
                    <span class="btn-icon">↺</span>
                    Reset Form
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- USERS LIST -->
<!-- ================================================================ -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Registered Users</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result && mysqli_num_rows($users_result) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo escape_html($user['username']); ?></td>
                                <td><?php echo escape_html($user['full_name']); ?></td>
                                <td><?php echo escape_html($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo format_datetime($user['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>
