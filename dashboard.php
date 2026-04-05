<?php
$pageTitle = 'Dashboard';
$pageScripts = ['animations.js', 'charts.js'];
require_once 'includes/header.php';
requireLogin();

$db = getDB();
$stats = getDashboardStats(getCurrentUserRole(), getCurrentUserId());
$role = getCurrentUserRole();

// Recent grievances
$recentGrievances = $db->query("SELECT g.*, u.name as user_name FROM grievances g JOIN users u ON g.user_id=u.id ORDER BY g.created_at DESC LIMIT 5")->fetchAll();
// Upcoming events
$upcomingEvents = $db->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 5")->fetchAll();
// Latest announcements
$announcements = $db->query("SELECT a.*, u.name as posted_by_name FROM announcements a JOIN users u ON a.posted_by=u.id ORDER BY a.is_pinned DESC, a.created_at DESC LIMIT 5")->fetchAll();

// Chart data
$grievancesByCategory = $db->query("SELECT category, COUNT(*) as cnt FROM grievances GROUP BY category")->fetchAll();
$grievancesByStatus = $db->query("SELECT status, COUNT(*) as cnt FROM grievances GROUP BY status")->fetchAll();
?>

<?php require_once 'includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-actions">
            <div>
                <h1>Welcome, <?= htmlspecialchars(getCurrentUserName()) ?>! 
                    <?= $role === 'admin' ? '🛡️' : ($role === 'faculty' ? '👨‍🏫' : '🎓') ?>
                </h1>
                <p>Here's your campus overview for today</p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="modules/grievance_new.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Report Issue</a>
                <button class="btn btn-secondary btn-sm" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <?php if ($role === 'admin'): ?>
        <div class="stat-card reveal stagger-1">
            <div class="stat-card-icon" style="background:rgba(102,126,234,0.12);color:var(--primary);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-card-info">
                <h3 data-count="<?= $stats['total_users'] ?>">0</h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="stat-card reveal stagger-2">
            <div class="stat-card-icon" style="background:var(--danger-bg);color:var(--danger);">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-card-info">
                <h3 data-count="<?= $stats['open_issues'] ?>">0</h3>
                <p>Open Issues</p>
            </div>
        </div>
        <div class="stat-card reveal stagger-3">
            <div class="stat-card-icon" style="background:var(--success-bg);color:var(--success);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-card-info">
                <h3 data-count="<?= $stats['active_events'] ?>">0</h3>
                <p>Active Events</p>
            </div>
        </div>
        <div class="stat-card reveal stagger-4">
            <div class="stat-card-icon" style="background:var(--warning-bg);color:var(--warning);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card-info">
                <h3 data-count="<?= $stats['pending_listings'] ?>">0</h3>
                <p>Pending Listings</p>
            </div>
        </div>
        <?php elseif ($role === 'faculty'): ?>
        <div class="stat-card reveal stagger-1">
            <div class="stat-card-icon" style="background:rgba(67,233,123,0.12);color:var(--clr-resources);"><i class="fas fa-book"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_resources'] ?>">0</h3><p>Resources Uploaded</p></div>
        </div>
        <div class="stat-card reveal stagger-2">
            <div class="stat-card-icon" style="background:rgba(254,225,64,0.12);color:var(--clr-events);"><i class="fas fa-calendar"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_events'] ?>">0</h3><p>Events Created</p></div>
        </div>
        <div class="stat-card reveal stagger-3">
            <div class="stat-card-icon" style="background:rgba(79,172,254,0.12);color:var(--clr-attendance);"><i class="fas fa-clipboard-check"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_sessions'] ?>">0</h3><p>Attendance Sessions</p></div>
        </div>
        <div class="stat-card reveal stagger-4">
            <div class="stat-card-icon" style="background:rgba(48,207,208,0.12);color:var(--clr-announcements);"><i class="fas fa-bullhorn"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_announcements'] ?>">0</h3><p>Announcements</p></div>
        </div>
        <?php else: ?>
        <div class="stat-card reveal stagger-1">
            <div class="stat-card-icon" style="background:var(--danger-bg);color:var(--clr-grievances);"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_issues'] ?>">0</h3><p>My Issues</p></div>
        </div>
        <div class="stat-card reveal stagger-2">
            <div class="stat-card-icon" style="background:rgba(250,112,154,0.12);color:var(--clr-marketplace);"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_listings'] ?>">0</h3><p>My Listings</p></div>
        </div>
        <div class="stat-card reveal stagger-3">
            <div class="stat-card-icon" style="background:rgba(254,225,64,0.12);color:var(--clr-events);"><i class="fas fa-calendar"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['my_events'] ?>">0</h3><p>Events Registered</p></div>
        </div>
        <div class="stat-card reveal stagger-4">
            <div class="stat-card-icon" style="background:rgba(79,172,254,0.12);color:var(--clr-attendance);"><i class="fas fa-chart-line"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['attendance'] ?>" data-suffix="%">0</h3><p>Attendance</p></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts + Quick Actions -->
    <div class="dashboard-grid reveal">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Grievances by Category</h3>
                <span class="badge badge-primary"><?= count($grievancesByCategory) ?> categories</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div>
            <div class="card" style="margin-bottom:var(--space-lg);">
                <h3 class="card-title" style="margin-bottom:var(--space-md);">Quick Actions</h3>
                <div class="quick-actions">
                    <a href="modules/grievance_new.php" class="quick-action-btn">
                        <i class="fas fa-exclamation-circle" style="color:var(--clr-grievances);"></i>
                        <span>Report Issue</span>
                    </a>
                    <a href="modules/marketplace_new.php" class="quick-action-btn">
                        <i class="fas fa-shopping-bag" style="color:var(--clr-marketplace);"></i>
                        <span>Sell Item</span>
                    </a>
                    <a href="modules/events.php" class="quick-action-btn">
                        <i class="fas fa-calendar-alt" style="color:var(--clr-events);"></i>
                        <span>Find Events</span>
                    </a>
                    <a href="modules/resources.php" class="quick-action-btn">
                        <i class="fas fa-book-open" style="color:var(--clr-resources);"></i>
                        <span>Study Resources</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity + Announcements -->
    <div class="dashboard-grid-equal reveal" style="margin-top:var(--space-lg);">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Grievances</h3>
                <a href="modules/grievances.php" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="recent-list">
                <?php foreach ($recentGrievances as $g): ?>
                <a href="modules/grievance_detail.php?id=<?= $g['id'] ?>" class="recent-item" style="text-decoration:none;color:inherit;">
                    <div class="recent-item-icon" style="background:var(--danger-bg);color:var(--danger);">
                        <i class="fas fa-<?= getCategoryIcon($g['category']) ?>"></i>
                    </div>
                    <div class="recent-item-info">
                        <h4><?= htmlspecialchars(truncateText($g['title'], 35)) ?></h4>
                        <p><?= htmlspecialchars($g['user_name']) ?></p>
                    </div>
                    <div class="recent-item-meta"><?= getStatusBadge($g['status']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Latest Announcements</h3>
                <a href="modules/announcements.php" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="recent-list">
                <?php foreach ($announcements as $ann): ?>
                <div class="recent-item">
                    <div class="recent-item-icon" style="background:<?= $ann['priority'] === 'urgent' ? 'var(--danger-bg)' : ($ann['priority'] === 'important' ? 'var(--warning-bg)' : 'var(--info-bg)') ?>;color:<?= $ann['priority'] === 'urgent' ? 'var(--danger)' : ($ann['priority'] === 'important' ? 'var(--warning)' : 'var(--info)') ?>;">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="recent-item-info">
                        <h4><?= htmlspecialchars(truncateText($ann['title'], 35)) ?></h4>
                        <p><?= htmlspecialchars($ann['posted_by_name']) ?></p>
                    </div>
                    <div class="recent-item-meta"><?= timeAgo($ann['created_at']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart Data -->
<script>
    const categoryData = <?= json_encode($grievancesByCategory) ?>;
    const statusData = <?= json_encode($grievancesByStatus) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
