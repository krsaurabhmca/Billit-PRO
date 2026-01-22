<?php
/**
 * ============================================================================
 * LOGIN PAGE
 * ============================================================================
 * Purpose: User authentication and session creation
 * Author: Inventory Management System
 * Date: 2026-01-23
 * ============================================================================
 */

// Include configuration and functions
require_once 'config/config.php';
require_once 'includes/functions.php';

// Start session
init_session();

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect(BASE_URL . 'index.php');
}

// ============================================================================
// PROCESS LOGIN FORM SUBMISSION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $username = sanitize_sql($connection, $_POST['username']);
    $password = $_POST['password']; // Don't sanitize password (will be verified with hash)
    
    // Initialize error flag
    $has_error = false;
    
    // Validate inputs
    if (empty($username)) {
        set_error_message("Username is required.");
        $has_error = true;
    }
    
    if (empty($password)) {
        set_error_message("Password is required.");
        $has_error = true;
    }
    
    // If no validation errors, proceed with authentication
    if (!$has_error) {
        // Query to fetch user by username
        $query = "SELECT user_id, username, password, full_name, email, role, status 
                  FROM users 
                  WHERE username = '{$username}' 
                  LIMIT 1";
        
        $result = db_query($connection, $query);
        
        // Check if user exists
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password using password_verify (for bcrypt hashed passwords)
            if (password_verify($password, $user['password'])) {
                // Check if user account is active
                if ($user['status'] === 'active') {
                    // Password is correct and account is active
                    // Create session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Set success message
                    set_success_message("Welcome back, " . $user['full_name'] . "!");
                    
                    // Redirect to dashboard
                    redirect(BASE_URL . 'index.php');
                } else {
                    // Account is inactive
                    set_error_message("Your account has been deactivated. Please contact administrator.");
                }
            } else {
                // Invalid password
                set_error_message("Invalid username or password.");
            }
        } else {
            // User not found
            set_error_message("Invalid username or password.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <!-- ================================================================ -->
    <!-- LOGIN CONTAINER -->
    <!-- ================================================================ -->
    <div class="login-container">
        <div class="login-box">
            <!-- Logo and Title -->
            <div class="login-header">
                <div class="login-logo">📦</div>
                <h1 class="login-title"><?php echo APP_NAME; ?></h1>
                <p class="login-subtitle">Please login to continue</p>
            </div>
            
            <!-- Display error messages -->
            <?php echo display_messages(); ?>
            
            <!-- Login Form -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="login-form">
                <!-- Username Field -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <span class="label-icon">👤</span>
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Enter your username"
                        value="<?php echo isset($_POST['username']) ? escape_html($_POST['username']) : ''; ?>"
                        required
                        autofocus
                    >
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <span class="label-icon">🔒</span>
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-icon">🚀</span>
                        Login
                    </button>
                </div>
            </form>
            
            <!-- Login Info -->
            <div class="login-info">
                <p class="info-text">
                    <strong>Default Credentials:</strong><br>
                    Username: <code>admin</code> | Password: <code>admin123</code>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> | Version <?php echo APP_VERSION; ?></p>
        </div>
    </div>
</body>
</html>
