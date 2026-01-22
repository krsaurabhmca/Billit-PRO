<?php
/**
 * ============================================================================
 * RESET PASSWORD PAGE
 * ============================================================================
 * Purpose: Set a new password using a token
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';
$valid_token = false;

// Check Token
if (isset($_GET['token'])) {
    $token = sanitize_sql($connection, $_GET['token']);
    
    // Validate Token and Expiry
    $query = "SELECT * FROM password_resets WHERE token = '$token' AND expiry > NOW() LIMIT 1";
    $reset = db_fetch_one($connection, $query);
    
    if ($reset) {
        $valid_token = true;
    } else {
        $message = "Invalid or expired reset link. Please request a new one.";
        $message_type = "danger";
    }
} else {
    redirect('login.php');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($password !== $confirm) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "danger";
    } else {
        // Update Password
        $email = $reset['email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
        
        if (db_execute($connection, $update)) {
            // Delete Token
            db_execute($connection, "DELETE FROM password_resets WHERE email = '$email'");
            
            $message = "Password updated successfully! Redirecting to login...";
            $message_type = "success";
            echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>";
        } else {
            $message = "Database error. Please try again.";
            $message_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .login-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 5px;
        }
        .login-subtitle {
            color: var(--gray-500);
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <span class="login-icon">🔑</span>
        <h1 class="login-title">Reset Password</h1>
        <?php if ($valid_token): ?>
        <p class="login-subtitle">Enter your new password</p>
        <?php endif; ?>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 20px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($valid_token): ?>
    <form method="POST" action="">
        <div class="form-group mb-20">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required minlength="6" placeholder="******">
        </div>
        
        <div class="form-group mb-20">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required placeholder="******">
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Set New Password</button>
    </form>
    <?php else: ?>
        <div class="text-center">
            <a href="forgot_password.php" class="btn btn-primary w-100">Request New Link</a>
        </div>
    <?php endif; ?>
    
    <div class="text-center mt-20">
        <a href="login.php" style="color: var(--gray-600); text-decoration: none; font-size: 14px;">← Back to Login</a>
    </div>
</div>

</body>
</html>
