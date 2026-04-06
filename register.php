<?php
$pageTitle = 'Register';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/flash.php';
require_once 'includes/functions.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

// Initialize CSRF token
initCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = 'student'; // Unconditionally set role to student
    $department = sanitize($_POST['department'] ?? 'General');
    
    $errors = [];
    if (empty($name) || strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
    if (!isValidEmail($email)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    
    if (empty($errors)) {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }
    
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed, $role, $department]);
        
        setFlashMessage('success', 'Account created successfully! Please login.');
        header('Location: login.php');
        exit;
    } else {
        setFlashMessage('error', implode(' ', $errors));
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your CampusNexus account and join the smart campus ecosystem.">
    <title>Register | CampusNexus</title>
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
        <div class="auth-particle" style="--x:10%;--y:25%;--size:5px;--dur:8s;--delay:0s;"></div>
        <div class="auth-particle" style="--x:80%;--y:35%;--size:4px;--dur:7s;--delay:1s;"></div>
        <div class="auth-particle" style="--x:50%;--y:75%;--size:6px;--dur:9s;--delay:2s;"></div>
        <div class="auth-particle" style="--x:75%;--y:85%;--size:3px;--dur:6s;--delay:0.5s;"></div>
        <div class="auth-particle" style="--x:20%;--y:60%;--size:4px;--dur:10s;--delay:3s;"></div>
        <div class="auth-particle" style="--x:65%;--y:10%;--size:5px;--dur:7.5s;--delay:1.5s;"></div>
    </div>
    
    <?= renderFlashMessage() ?>
    
    <div class="auth-page">
        <div class="auth-container" style="max-width:520px;">
            <div class="auth-card auth-card-enhanced">
                <div class="auth-header">
                    <a href="<?= SITE_URL ?>/" class="auth-logo-link">
                        <div class="auth-logo-icon">
                            <span>CN</span>
                            <div class="auth-logo-ring"></div>
                        </div>
                    </a>
                    <h1>Create Account</h1>
                    <p>Join the CampusNexus ecosystem</p>
                </div>
                
                <form method="POST" id="registerForm">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" class="form-control form-control-icon" 
                                   placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-error" id="name-error"></div>
                    </div>
                    
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
                        <label for="department" class="form-label">Department</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-building"></i>
                            <select id="department" name="department" class="form-control form-control-icon">
                                <option value="Computer Science">Computer Science</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Mechanical">Mechanical</option>
                                <option value="Civil">Civil</option>
                                <option value="Electrical">Electrical</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control form-control-icon" 
                                   placeholder="Minimum 6 characters" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength"><div class="password-strength-bar"></div></div>
                        <div class="password-strength-text"></div>
                        <div class="form-error" id="password-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-icon" 
                                   placeholder="Re-enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-error" id="confirm_password-error"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn" style="margin-top:8px;">
                        <i class="fas fa-user-plus"></i> Create Account
                        <div class="btn-shine"></div>
                    </button>
                </form>
                
                <div class="auth-footer">
                    Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign in</a>
                </div>
            </div>
            
            <div class="auth-back-home">
                <a href="<?= SITE_URL ?>/"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>
    
    <script src="<?= SITE_URL ?>/js/app.js"></script>
    <script src="<?= SITE_URL ?>/js/validation.js"></script>
    <script>
        new FormValidator('registerForm', {
            name: { required: true, minLength: 2, maxLength: 100, label: 'Full name' },
            email: { required: true, email: true, label: 'Email' },
            password: { required: true, minLength: 6, label: 'Password' },
            confirm_password: { required: true, match: 'password', matchMsg: 'Passwords do not match', label: 'Confirm password' }
        });
        initPasswordStrength('password');
        
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
    </script>
</body>
</html>
