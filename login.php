<?php
$pageTitle = 'Login';
$pageScripts = ['validation.js'];
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/flash.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

// Initialize CSRF token
initCSRFToken();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    
    // Rate limiting: 5 attempts per 300 seconds (5 minutes)
    if (!check_rate_limit('login_attempt', 5, 300)) {
        setFlashMessage('error', 'Too many login attempts. Please wait 5 minutes before trying again.');
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
    
        if (empty($email) || empty($password)) {
            setFlashMessage('error', 'Please fill in all fields.');
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                setUserSession($user);
                
                // Update last login
                $update = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->execute([$user['id']]);
                
                // Remember me
                if ($remember) {
                    $token = generateToken();
                    $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$token, $user['id']]);
                    setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/');
                }
                
                setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
                header('Location: dashboard.php');
                exit;
            } else {
                setFlashMessage('error', 'Invalid email or password.');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to CampusNexus — your unified smart campus ecosystem.">
    <title>Login | CampusNexus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/components.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/layout.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/animations.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/pages/auth.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
</head>
<body>
    <div class="auth-bg-pattern"></div>
    
    <!-- Floating Particles -->
    <div class="auth-particles" aria-hidden="true">
        <div class="auth-particle" style="--x:15%;--y:20%;--size:6px;--dur:7s;--delay:0s;"></div>
        <div class="auth-particle" style="--x:85%;--y:30%;--size:4px;--dur:9s;--delay:1s;"></div>
        <div class="auth-particle" style="--x:45%;--y:70%;--size:5px;--dur:8s;--delay:2s;"></div>
        <div class="auth-particle" style="--x:70%;--y:80%;--size:3px;--dur:6s;--delay:0.5s;"></div>
        <div class="auth-particle" style="--x:25%;--y:55%;--size:4px;--dur:10s;--delay:3s;"></div>
        <div class="auth-particle" style="--x:60%;--y:15%;--size:5px;--dur:7.5s;--delay:1.5s;"></div>
        <div class="auth-particle" style="--x:90%;--y:60%;--size:3px;--dur:8.5s;--delay:4s;"></div>
        <div class="auth-particle" style="--x:10%;--y:85%;--size:6px;--dur:6.5s;--delay:2.5s;"></div>
    </div>
    
    <?= renderFlashMessage() ?>
    <?php if (isset($_GET['logout']) && $_GET['logout'] == '1'): ?>
    <div class="flash-message flash-success" id="flashMessage">
        <div class="flash-content">
            <i class="fas fa-check-circle"></i>
            <span>You have been logged out successfully.</span>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card auth-card-enhanced">
                <div class="auth-header">
                    <a href="<?= SITE_URL ?>/" class="auth-logo-link">
                        <div class="auth-logo-icon">
                            <span>CN</span>
                            <div class="auth-logo-ring"></div>
                        </div>
                    </a>
                    <h1>Welcome Back</h1>
                    <p>Sign in to your CampusNexus account</p>
                </div>
                
                <form method="POST" id="loginForm">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control form-control-icon" 
                                   placeholder="you@campusnexus.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-error" id="email-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control form-control-icon" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-error" id="password-error"></div>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                        <label class="form-check" style="gap:var(--space-sm);">
                            <input type="checkbox" name="remember" id="remember" style="margin:0;">
                            <span style="font-size:var(--text-sm);color:var(--text-secondary);cursor:pointer;">Remember me</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                        <div class="btn-shine"></div>
                    </button>
                </form>
                
                <div class="auth-divider">or</div>
                
                <div class="demo-accounts">
                    <p class="demo-label"><i class="fas fa-info-circle"></i> Demo Accounts <span class="text-muted">(password: password)</span></p>
                    <div class="demo-badges">
                        <span class="badge badge-info" style="cursor:pointer;" onclick="fillDemo('admin@campusnexus.com')"><i class="fas fa-shield-alt"></i> Admin</span>
                        <span class="badge badge-success" style="cursor:pointer;" onclick="fillDemo('priya@campusnexus.com')"><i class="fas fa-chalkboard-teacher"></i> Faculty</span>
                        <span class="badge badge-primary" style="cursor:pointer;" onclick="fillDemo('arjun@campusnexus.com')"><i class="fas fa-user-graduate"></i> Student</span>
                    </div>
                </div>
                
                <div class="auth-footer">
                    Don't have an account? <a href="<?= SITE_URL ?>/register.php">Create one</a>
                </div>
            </div>
            
            <!-- Back to home link -->
            <div class="auth-back-home">
                <a href="<?= SITE_URL ?>/"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>
    
    <script src="<?= SITE_URL ?>/js/app.js"></script>
    <script src="<?= SITE_URL ?>/js/validation.js"></script>
    <script>
        new FormValidator('loginForm', {
            email: { required: true, email: true, label: 'Email' },
            password: { required: true, minLength: 3, label: 'Password' }
        });
        
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').focus();
        }
    </script>
</body>
</html>
