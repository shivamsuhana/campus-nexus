<?php
/**
 * CampusNexus — Shared Header
 * Role-aware navigation bar
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/functions.php';

$currentPage = getCurrentPage();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CampusNexus - Unified Smart Campus Ecosystem connecting students, faculty, and administration.">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | CampusNexus' : 'CampusNexus — Smart Campus Ecosystem' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js (dashboard + admin dashboard) -->
    <?php $isAdminPage = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false; ?>
    <?php if (in_array($currentPage, ['dashboard']) || ($currentPage === 'index' && $isAdminPage)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/components.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/layout.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/animations.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
    
    <!-- Page-specific CSS -->
    <?php if (in_array($currentPage, ['index']) && !$isAdminPage): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/pages/home.css">
    <?php elseif (in_array($currentPage, ['login', 'register'])): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/pages/auth.css">
    <?php elseif ($currentPage === 'dashboard'): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/pages/dashboard.css">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/pages/modules.css">
    <?php if ($isAdminPage): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/pages/admin.css">
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-inner">
            <!-- Brand -->
            <a href="<?= SITE_URL ?>/" class="navbar-brand">
                <div class="navbar-brand-icon">CN</div>
                <span>Campus<span class="text-gradient">Nexus</span></span>
            </a>
            
            <!-- Navigation Links -->
            <div class="navbar-nav" id="navbarNav">
                <a href="<?= SITE_URL ?>/" class="nav-link <?= isCurrentPage('index') ?>">
                    <i class="fas fa-home"></i> Home
                </a>
                
                <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/dashboard.php" class="nav-link <?= isCurrentPage('dashboard') ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                
                <a href="<?= SITE_URL ?>/modules/grievances.php" class="nav-link <?= isCurrentPage('grievances') ?>">
                    <i class="fas fa-exclamation-triangle"></i> Grievances
                </a>
                
                <a href="<?= SITE_URL ?>/modules/marketplace.php" class="nav-link <?= isCurrentPage('marketplace') ?>">
                    <i class="fas fa-store"></i> Marketplace
                </a>

                <a href="<?= SITE_URL ?>/modules/events.php" class="nav-link <?= isCurrentPage('events') ?>">
                    <i class="fas fa-calendar-alt"></i> Events
                </a>
                
                <a href="<?= SITE_URL ?>/modules/resources.php" class="nav-link <?= isCurrentPage('resources') ?>">
                    <i class="fas fa-book"></i> Resources
                </a>
                <?php endif; ?>
                
                <a href="<?= SITE_URL ?>/about.php" class="nav-link <?= isCurrentPage('about') ?>">
                    <i class="fas fa-info-circle"></i> About
                </a>
                
                <a href="<?= SITE_URL ?>/contact.php" class="nav-link <?= isCurrentPage('contact') ?>">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </div>
            
            <!-- Right Actions -->
            <div class="navbar-actions">
                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" data-tooltip="Toggle theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                <!-- User Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="navbar-user" id="userDropdownToggle">
                        <img src="<?= getAvatarUrl($user['avatar'], $user['name']) ?>" alt="Avatar">
                        <span><?= htmlspecialchars($user['name']) ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 4px; color: var(--text-muted);"></i>
                    </div>
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <a href="<?= SITE_URL ?>/profile.php">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="<?= SITE_URL ?>/dashboard.php">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="<?= SITE_URL ?>/admin/">
                            <i class="fas fa-shield-alt"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        <div class="user-dropdown-divider"></div>
                        <a href="<?= SITE_URL ?>/logout.php" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-ghost btn-sm">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Sign Up</a>
                <?php endif; ?>
                
                <!-- Mobile Toggle -->
                <button class="navbar-toggle" id="navbarToggle">
                    <div class="hamburger">
                        <span></span><span></span><span></span>
                    </div>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?= renderFlashMessage() ?>
