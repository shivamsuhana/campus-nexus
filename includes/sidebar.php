<?php
/**
 * CampusNexus — Dashboard Sidebar
 * Role-aware navigation sidebar with mobile overlay
 */
$user = getCurrentUser();
$sidebarPage = basename($_SERVER['PHP_SELF'], '.php');
$isInAdmin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
?>
<!-- Sidebar Overlay (mobile close) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <!-- Mobile close button -->
    <button class="sidebar-close-btn" onclick="closeSidebar()"><i class="fas fa-times"></i></button>
    
    <!-- Main Navigation -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Main</div>
        <a href="<?= SITE_URL ?>/dashboard.php" class="sidebar-link <?= $sidebarPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large" style="color: var(--primary);"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/profile.php" class="sidebar-link <?= $sidebarPage === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user" style="color: var(--text-secondary);"></i> My Profile
        </a>
    </div>
    
    <!-- Modules -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Modules</div>
        
        <a href="<?= SITE_URL ?>/modules/attendance.php" class="sidebar-link <?= $sidebarPage === 'attendance' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check" style="color: var(--clr-attendance);"></i> Attendance
        </a>
        
        <a href="<?= SITE_URL ?>/modules/resources.php" class="sidebar-link <?= in_array($sidebarPage, ['resources', 'resource_upload']) ? 'active' : '' ?>">
            <i class="fas fa-book-open" style="color: var(--clr-resources);"></i> Resources
        </a>
        
        <a href="<?= SITE_URL ?>/modules/grievances.php" class="sidebar-link <?= in_array($sidebarPage, ['grievances', 'grievance_new', 'grievance_detail']) ? 'active' : '' ?>">
            <i class="fas fa-exclamation-circle" style="color: var(--clr-grievances);"></i> Grievances
        </a>
        
        <a href="<?= SITE_URL ?>/modules/marketplace.php" class="sidebar-link <?= in_array($sidebarPage, ['marketplace', 'marketplace_new', 'listing_detail']) ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag" style="color: var(--clr-marketplace);"></i> Marketplace
        </a>
        
        <a href="<?= SITE_URL ?>/modules/events.php" class="sidebar-link <?= in_array($sidebarPage, ['events', 'event_new', 'event_detail']) ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt" style="color: var(--clr-events);"></i> Events
        </a>
        
        <a href="<?= SITE_URL ?>/modules/lost_found.php" class="sidebar-link <?= in_array($sidebarPage, ['lost_found', 'lost_found_new']) ? 'active' : '' ?>">
            <i class="fas fa-search-location" style="color: var(--clr-lost-found);"></i> Lost & Found
        </a>
        
        <a href="<?= SITE_URL ?>/modules/mess.php" class="sidebar-link <?= $sidebarPage === 'mess' ? 'active' : '' ?>">
            <i class="fas fa-utensils" style="color: var(--clr-mess);"></i> Mess Menu
        </a>
        
        <a href="<?= SITE_URL ?>/modules/announcements.php" class="sidebar-link <?= $sidebarPage === 'announcements' && !$isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-bullhorn" style="color: var(--clr-announcements);"></i> Announcements
        </a>
    </div>
    
    <?php if (isAdmin()): ?>
    <!-- Admin -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Admin Panel</div>
        <a href="<?= SITE_URL ?>/admin/" class="sidebar-link <?= $sidebarPage === 'index' && $isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-shield-alt" style="color: var(--danger);"></i> Overview
        </a>
        <a href="<?= SITE_URL ?>/admin/users.php" class="sidebar-link <?= $sidebarPage === 'users' && $isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-users-cog" style="color: var(--warning);"></i> Users
        </a>
        <a href="<?= SITE_URL ?>/admin/grievances.php" class="sidebar-link <?= $sidebarPage === 'grievances' && $isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-tools" style="color: var(--clr-grievances);"></i> Grievances
        </a>
        <a href="<?= SITE_URL ?>/admin/marketplace.php" class="sidebar-link <?= $sidebarPage === 'marketplace' && $isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-store" style="color: var(--clr-marketplace);"></i> Marketplace
        </a>
        <a href="<?= SITE_URL ?>/admin/announcements.php" class="sidebar-link <?= $sidebarPage === 'announcements' && $isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-megaphone" style="color: var(--clr-announcements);"></i> Announcements
        </a>
        <a href="<?= SITE_URL ?>/admin/mess_menu.php" class="sidebar-link <?= $sidebarPage === 'mess_menu' && $isInAdmin ? 'active' : '' ?>">
            <i class="fas fa-concierge-bell" style="color: var(--clr-mess);"></i> Mess Menu
        </a>
    </div>
    <?php endif; ?>
</aside>
