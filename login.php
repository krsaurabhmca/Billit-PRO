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
                    
                    // Log Login
                    log_activity($connection, "Login", "User logged in successfully");
                    
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
// End of POST processing

// Fetch Company Settings for Branding
$c_query = "SELECT * FROM company_settings LIMIT 1";
$c_result = db_query($connection, $c_query);
$company = mysqli_fetch_assoc($c_result);
$has_logo = !empty($company['company_logo']);
$theme_col = !empty($company['invoice_color']) ? $company['invoice_color'] : '#2563eb';
?>
<?php
// Greeting Logic
$hour = date('H');
if ($hour < 12) { $greeting = "Good Morning!"; }
elseif ($hour < 18) { $greeting = "Good Afternoon!"; }
else { $greeting = "Good Evening!"; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: <?php echo $theme_col; ?>;
            --primary-dark: <?php echo $theme_col; ?>; 
            /* We will use a calculated dark hex manually or just same color */
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; height: 100vh; display: flex; overflow: hidden; }
        
        .split-screen { display: flex; width: 100%; height: 100%; }
        
        /* Left Side */
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .brand-side { 
            width: 50%; 
            background: linear-gradient(-45deg, var(--primary), #0f172a, #334155, var(--primary));
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            color: white; 
            position: relative;
            overflow: hidden;
        }
        
        .brand-side::before {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.5;
        }
        
        .brand-content { z-index: 2; text-align: center; max-width: 80%; backdrop-filter: blur(5px); padding: 20px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); }
        .brand-content h1 { font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .brand-content p { font-size: 1.2rem; opacity: 0.9; line-height: 1.6; font-weight: 300; }
        .floating-icon { font-size: 15rem; opacity: 0.1; position: absolute; bottom: -4rem; right: -4rem; transform: rotate(-20deg); animation: float 6s ease-in-out infinite; }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(-20deg); }
            50% { transform: translateY(-20px) rotate(-20deg); }
            100% { transform: translateY(0px) rotate(-20deg); }
        }
        
        /* Right Side */
        .form-side { 
            width: 50%; 
            background: white; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            padding: 2rem;
            position: relative;
        }
        .login-card { width: 100%; max-width: 420px; padding: 2rem; perspective: 1000px; }
        
        .logo-area { text-align: center; margin-bottom: 2rem; animation: fadeInDown 0.8s ease; }
        .logo-area img { max-height: 70px; }
        .logo-area .text-logo { font-size: 28px; font-weight: 700; color: #1e293b; letter-spacing: -1px; }
        
        .welcome-text { margin-bottom: 2.5rem; text-align: center; animation: fadeIn 1s ease; }
        .welcome-text h2 { color: #1e293b; font-size: 1.8rem; margin-bottom: 0.5rem; }
        .welcome-text p { color: #64748b; }
        
        /* Floating Labels */
        .floating-group { position: relative; margin-bottom: 1.5rem; }
        .floating-input {
            width: 100%; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 1rem; background: transparent; transition: all 0.2s;
            font-family: inherit; color: #1e293b;
        }
        .floating-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .floating-label {
            position: absolute; left: 14px; top: 14px; color: #94a3b8; font-size: 1rem;
            pointer-events: none; transition: 0.2s ease all; background: white; padding: 0 4px;
        }
        
        .floating-input:focus ~ .floating-label,
        .floating-input:not(:placeholder-shown) ~ .floating-label {
            top: -10px; font-size: 0.8rem; color: var(--primary); font-weight: 600;
        }
        
        .password-toggle { 
            position: absolute; right: 14px; top: 14px; cursor: pointer; color: #cbd5e1; user-select: none; transition: color 0.2s;
        }
        .password-toggle:hover { color: var(--primary); }
        
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .remember-me { display: flex; align-items: center; gap: 8px; color: #64748b; cursor: pointer; }
        .forgot-pass { color: var(--primary); font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
        .forgot-pass:hover { opacity: 0.8; }
        
        .btn-submit { 
            width: 100%; padding: 16px; background: var(--primary); color: white; border: none; 
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; 
            transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3); }
        .btn-submit:active { transform: translateY(0); }
        
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; animation: slideIn 0.5s ease; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        
        .footer-copy { text-align: center; margin-top: 2rem; color: #94a3b8; font-size: 0.8rem; }
        
        #capsLockWarning {
            display: none; color: #ea580c; font-size: 0.8rem; margin-top: 5px; font-weight: 500; display: flex; align-items: center; gap: 5px;
        }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
        
        @media (max-width: 768px) {
            .brand-side { display: none; }
            .form-side { width: 100%; background: #f8fafc; }
            .login-card { background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 2.5rem; }
        }
    </style>
</head>
<body>

    <div class="split-screen">
        <!-- Brand Side -->
        <div class="brand-side">
            <div class="brand-content">
                <h1><?php echo $greeting; ?></h1>
                <p>Login to access your Billit Pro Dashboard.</p>
            </div>
            <div class="floating-icon">📦</div>
        </div>
        
        <!-- Form Side -->
        <div class="form-side">
            <div class="login-card">
                <!-- Brand Logo -->
                <div class="logo-area">
                    <?php if($has_logo): ?>
                        <img src="<?php echo escape_html($company['company_logo']); ?>" alt="Logo">
                    <?php else: ?>
                        <div class="text-logo">📦 <?php echo escape_html($company['company_name'] ?? APP_NAME); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="welcome-text">
                    <h2>Welcome Back</h2>
                    <p>Enter your credentials to continue.</p>
                </div>
                
                <?php echo display_messages(); ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <!-- Username -->
                    <div class="floating-group">
                        <input type="text" id="username" name="username" class="floating-input" placeholder=" "
                               value="<?php echo isset($_POST['username']) ? escape_html($_POST['username']) : ''; ?>" required autofocus>
                        <label class="floating-label" for="username">Username</label>
                    </div>
                    
                    <!-- Password -->
                    <div class="floating-group">
                        <input type="password" id="password" name="password" class="floating-input" placeholder=" " required>
                        <label class="floating-label" for="password">Password</label>
                        <span class="password-toggle" onclick="togglePassword()">👁️</span>
                        <div id="capsLockWarning" style="display:none;">⚠️ Caps Lock is ON</div>
                    </div>
                    
                    <div class="form-actions">
                        <label class="remember-me">
                            <input type="checkbox" name="remember"> Keep me logged in
                        </label>
                        <a href="javascript:alert('Please contact your administrator to reset password.')" class="forgot-pass">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        Login to Dashboard
                        <span>➔</span>
                    </button>
                </form>
                
                <div class="footer-copy">
                    &copy; <?php echo date('Y'); ?> Billit Pro. Version <?php echo APP_VERSION; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password
        function togglePassword() {
            const pwdInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                pwdInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
        
        // Caps Lock Detector
        const passwordField = document.getElementById('password');
        const capsWarning = document.getElementById('capsLockWarning');

        passwordField.addEventListener('keyup', function(event) {
            checkCaps(event);
        });
        passwordField.addEventListener('mousedown', function(event) {
            checkCaps(event);
        });

        function checkCaps(event) {
            if (event.getModifierState && event.getModifierState('CapsLock')) {
                capsWarning.style.display = 'block';
            } else {
                capsWarning.style.display = 'none';
            }
        }
    </script>
</body>
</html>
