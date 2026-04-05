<?php
$pageTitle = 'Home';
$pageScripts = ['animations.js'];
require_once 'includes/header.php';

$db = getDB();
// Fetch stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalIssues = $db->query("SELECT COUNT(*) FROM grievances")->fetchColumn();
$resolvedIssues = $db->query("SELECT COUNT(*) FROM grievances WHERE status IN ('resolved','closed')")->fetchColumn();
$totalResources = $db->query("SELECT COUNT(*) FROM resources")->fetchColumn();
$totalEvents = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();

// Recent activity
$recentGrievances = $db->query("SELECT g.*, u.name as user_name FROM grievances g JOIN users u ON g.user_id = u.id ORDER BY g.created_at DESC LIMIT 5")->fetchAll();
$upcomingEvents = $db->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 4")->fetchAll();
?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-bg"></div>
        <div class="container">
            <div class="hero-content reveal">
                <div class="hero-badge">
                    <i class="fas fa-bolt"></i> The Future of Campus Management
                </div>
                <h1 class="hero-title">
                    Your Campus.<br>
                    <span class="text-gradient">Connected. Empowered.</span>
                </h1>
                <p class="hero-subtitle">
                    One unified platform for students, faculty, and administration.
                    Report issues, share resources, discover events, and transform your campus experience.
                </p>
                <div class="hero-actions">
                    <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i> Get Started Free
                    </a>
                    <a href="#modules" class="btn btn-secondary btn-lg">
                        <i class="fas fa-compass"></i> Explore Modules
                    </a>
                    <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-th-large"></i> Go to Dashboard
                    </a>
                    <a href="#modules" class="btn btn-secondary btn-lg">
                        <i class="fas fa-compass"></i> Explore Modules
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Stats Bar -->
            <div class="hero-stats reveal stagger-2">
                <div class="hero-stat">
                    <div class="hero-stat-number" data-count="<?= $totalUsers ?>" data-suffix="+">0</div>
                    <div class="hero-stat-label">Active Users</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number" data-count="<?= $totalIssues ?>">0</div>
                    <div class="hero-stat-label">Issues Reported</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number" data-count="<?= $resolvedIssues ?>">0</div>
                    <div class="hero-stat-label">Issues Resolved</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number" data-count="<?= $totalResources ?>" data-suffix="+">0</div>
                    <div class="hero-stat-label">Study Resources</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section" id="how-it-works">
        <div class="container">
            <div class="section-header reveal">
                <h2>How <span class="text-gradient">CampusNexus</span> Works</h2>
                <p>Three simple steps to transform your campus experience</p>
            </div>
            <div class="steps-grid">
                <div class="step-card reveal stagger-1">
                    <div class="step-number">1</div>
                    <h3>Sign Up</h3>
                    <p>Create your account as a Student, Faculty, or Admin. Get instant access to all campus modules.</p>
                </div>
                <div class="step-card reveal stagger-2">
                    <div class="step-number">2</div>
                    <h3>Engage</h3>
                    <p>Report issues, share resources, list items for sale, register for events, and rate your meals.</p>
                </div>
                <div class="step-card reveal stagger-3">
                    <div class="step-number">3</div>
                    <h3>Track & Resolve</h3>
                    <p>Monitor progress on your reports, get real-time updates, and see campus data analytics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Showcase -->
    <section class="section" id="modules">
        <div class="container">
            <div class="section-header reveal">
                <h2>8 Powerful <span class="text-gradient">Modules</span></h2>
                <p>Everything your campus needs, in one unified ecosystem</p>
            </div>
            <div class="modules-grid">
                <a href="modules/attendance.php" class="module-card reveal stagger-1">
                    <div class="module-card-icon" style="background: rgba(79,172,254,0.12); color: var(--clr-attendance);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3>Smart Attendance</h3>
                    <p>Session codes, anti-proxy, real-time tracking</p>
                </a>
                <a href="modules/resources.php" class="module-card reveal stagger-2">
                    <div class="module-card-icon" style="background: rgba(67,233,123,0.12); color: var(--clr-resources);">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Resource Hub</h3>
                    <p>Notes, slides, past papers with ratings</p>
                </a>
                <a href="modules/grievances.php" class="module-card reveal stagger-3">
                    <div class="module-card-icon" style="background: rgba(245,87,108,0.12); color: var(--clr-grievances);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3>Grievance Tracker</h3>
                    <p>Report issues, upvote, track resolution</p>
                </a>
                <a href="modules/marketplace.php" class="module-card reveal stagger-4">
                    <div class="module-card-icon" style="background: rgba(250,112,154,0.12); color: var(--clr-marketplace);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3>Campus Marketplace</h3>
                    <p>Buy & sell books, electronics, furniture</p>
                </a>
                <a href="modules/events.php" class="module-card reveal stagger-5">
                    <div class="module-card-icon" style="background: rgba(254,225,64,0.12); color: var(--clr-events);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Events Hub</h3>
                    <p>Discover, register, manage campus events</p>
                </a>
                <a href="modules/lost_found.php" class="module-card reveal stagger-6">
                    <div class="module-card-icon" style="background: rgba(161,140,209,0.12); color: var(--clr-lost-found);">
                        <i class="fas fa-search-location"></i>
                    </div>
                    <h3>Lost & Found</h3>
                    <p>Report & recover lost items easily</p>
                </a>
                <a href="modules/mess.php" class="module-card reveal stagger-7">
                    <div class="module-card-icon" style="background: rgba(247,151,30,0.12); color: var(--clr-mess);">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Mess Feedback</h3>
                    <p>Daily menu, meal ratings, quality tracking</p>
                </a>
                <a href="modules/announcements.php" class="module-card reveal stagger-8">
                    <div class="module-card-icon" style="background: rgba(48,207,208,0.12); color: var(--clr-announcements);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Announcements</h3>
                    <p>Priority notices & departmental updates</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Recent Activity -->
    <section class="section">
        <div class="container">
            <div class="section-header reveal">
                <h2>Recent <span class="text-gradient">Activity</span></h2>
                <p>Stay updated with the latest campus happenings</p>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl);">
                <!-- Recent Issues -->
                <div class="card reveal stagger-1">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-circle" style="color:var(--clr-grievances);margin-right:8px;"></i>Latest Grievances</h3>
                        <a href="modules/grievances.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <div class="activity-list">
                        <?php foreach ($recentGrievances as $g): ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: var(--danger-bg); color: var(--danger);">
                                <i class="fas fa-<?= getCategoryIcon($g['category']) ?>"></i>
                            </div>
                            <div class="activity-info">
                                <h4><?= htmlspecialchars(truncateText($g['title'], 45)) ?></h4>
                                <p>by <?= htmlspecialchars($g['user_name']) ?> • <?= getStatusBadge($g['status']) ?></p>
                            </div>
                            <span class="activity-time"><?= timeAgo($g['created_at']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Upcoming Events -->
                <div class="card reveal stagger-2">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt" style="color:var(--clr-events);margin-right:8px;"></i>Upcoming Events</h3>
                        <a href="modules/events.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <div class="activity-list">
                        <?php foreach ($upcomingEvents as $evt): ?>
                        <div class="activity-item">
                            <div class="event-date-badge" style="width:50px;padding:6px;">
                                <div class="day" style="font-size:18px;"><?= date('d', strtotime($evt['event_date'])) ?></div>
                                <div class="month" style="font-size:9px;"><?= date('M', strtotime($evt['event_date'])) ?></div>
                            </div>
                            <div class="activity-info">
                                <h4><?= htmlspecialchars(truncateText($evt['title'], 40)) ?></h4>
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($evt['venue']) ?></p>
                            </div>
                            <span class="badge badge-primary"><?= $evt['registered_count'] ?>/<?= $evt['capacity'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section">
        <div class="container">
            <div class="cta-section reveal">
                <h2>Ready to Transform Your Campus?</h2>
                <p>Join hundreds of students and faculty already using CampusNexus to make their campus better.</p>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Create Account</a>
                    <a href="about.php" class="btn btn-secondary btn-lg"><i class="fas fa-info-circle"></i> Learn More</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
