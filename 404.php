<?php
$pageTitle = 'Page Not Found';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Try to load auth functions if available (don't fail if not, as user might not be logged in)
if (file_exists(__DIR__ . '/includes/auth.php')) {
    require_once __DIR__ . '/includes/auth.php';
}

$isLoggedIn = function_exists('isLoggedIn') && isLoggedIn();

?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | CampusNexus</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/components.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/animations.css">
    
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-xl);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .error-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }
        
        .error-code {
            font-size: 150px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: var(--space-md);
            background: linear-gradient(135deg, var(--primary-start), var(--primary-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 10px 30px rgba(102,126,234,0.3);
            animation: float 6s ease-in-out infinite;
        }
        
        .error-title {
            font-size: var(--text-3xl);
            margin-bottom: var(--space-md);
        }
        
        .error-desc {
            font-size: var(--text-lg);
            color: var(--text-secondary);
            margin-bottom: var(--space-xl);
            line-height: var(--leading-relaxed);
        }
        
        .error-actions {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .error-bg-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            z-index: 0;
        }
        
        .circle-1 {
            width: 400px; height: 400px;
            background: var(--primary);
            top: -100px; left: -100px;
            animation: pulse 8s infinite alternate;
        }
        
        .circle-2 {
            width: 500px; height: 500px;
            background: var(--clr-marketplace);
            bottom: -200px; right: -100px;
            animation: pulse 10s infinite alternate-reverse;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.1; }
            100% { transform: scale(1.2); opacity: 0.2; }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        @media (max-width: 768px) {
            .error-code { font-size: 100px; }
            .error-title { font-size: var(--text-2xl); }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-bg-circle circle-1"></div>
        <div class="error-bg-circle circle-2"></div>
        
        <div class="error-content reveal">
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-desc">Oops! The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            
            <div class="error-actions">
                <a href="<?= SITE_URL ?>/" class="btn btn-primary btn-lg">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <?php if ($isLoggedIn): ?>
                <a href="<?= SITE_URL ?>/dashboard.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="<?= SITE_URL ?>/js/app.js"></script>
</body>
</html>
