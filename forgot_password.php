<?php
/**
 * ============================================================================
 * FORGOT PASSWORD PAGE
 * ============================================================================
 * Purpose: Allow users to reset forgotten passwords via email
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_sql($connection, $_POST['email']);
    
    // Check if email exists
    $user = db_fetch_one($connection, "SELECT user_id, username, full_name FROM users WHERE email = '$email' AND status = 'active'");
    
    if ($user) {
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save Token
        $query = "INSERT INTO password_resets (email, token, expiry) VALUES ('$email', '$token', '$expiry')";
        db_execute($connection, $query);
        
        // Send Email
        $reset_link = BASE_URL . "reset_password.php?token=" . $token;
        
        $subject = "Reset Your Password - " . APP_NAME;
        $body = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2>Hello {$user['full_name']},</h2>
            <p>You requested to reset your password for " . APP_NAME . ".</p>
            <p>Please click the link below to verify your email and set a new password:</p>
            <p style='margin: 20px 0;'>
                <a href='$reset_link' style='background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a>
            </p>
            <p>Or verify using this link: <a href='$reset_link'>$reset_link</a></p>
            <p><small>This link expires in 1 hour. If you did not request this, please ignore this email.</small></p>
        </div>";
        
        $result = send_email($connection, $email, $subject, $body);
        
        if ($result['success']) {
            $message = "A password reset link has been sent to your email.";
            $message_type = "success";
        } else {
            $message = "Failed to send email. please contact system administrator.";
            $message_type = "danger";
        }
    } else {
        // Look ambiguous for security
        $message = "If an account exists with that email, we have sent a reset link.";
        $message_type = "info";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
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
        <span class="login-icon">🔐</span>
        <h1 class="login-title">Forgot Password</h1>
        <p class="login-subtitle">Enter your email to receive a reset link</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 20px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group mb-20">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required placeholder="name@company.com">
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mb-20">Send Reset Link</button>
        
        <div class="text-center">
            <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-size: 14px;">← Back to Login</a>
        </div>
    </form>
</div>

</body>
</html>
