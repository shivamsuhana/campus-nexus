<?php
$pageTitle = 'Admin Dashboard';
$pageScripts = ['animations.js', 'charts.js'];
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$db = getDB();

$stats = getDashboardStats('admin');
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalFaculty = $db->query("SELECT COUNT(*) FROM users WHERE role='faculty'")->fetchColumn();

// Charts data
$grievancesByCategory = $db->query("SELECT category, COUNT(*) as cnt FROM grievances GROUP BY category")->fetchAll();
$grievancesByStatus = $db->query("SELECT status, COUNT(*) as cnt FROM grievances GROUP BY status")->fetchAll();
$recentUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$unreadMessages = $db->query("SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header">
        <h1>🛡️ Admin Control Center</h1>
        <p>System-wide overview and management</p>
    </div>

    <!-- Stats -->
    <div class="dashboard-stats">
        <div class="stat-card reveal stagger-1">
            <div class="stat-card-icon" style="background:rgba(102,126,234,0.12);color:var(--primary);"><i class="fas fa-users"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['total_users'] ?>">0</h3><p>Total Users</p></div>
        </div>
        <div class="stat-card reveal stagger-2">
            <div class="stat-card-icon" style="background:var(--danger-bg);color:var(--danger);"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['open_issues'] ?>">0</h3><p>Open Issues</p></div>
        </div>
        <div class="stat-card reveal stagger-3">
            <div class="stat-card-icon" style="background:var(--success-bg);color:var(--success);"><i class="fas fa-check-circle"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['resolved_grievances'] ?>">0</h3><p>Resolved</p></div>
        </div>
        <div class="stat-card reveal stagger-4">
            <div class="stat-card-icon" style="background:var(--warning-bg);color:var(--warning);"><i class="fas fa-envelope"></i></div>
            <div class="stat-card-info"><h3 data-count="<?= $stats['unread_messages'] ?>">0</h3><p>Unread Messages</p></div>
        </div>
    </div>

    <!-- Charts -->
    <div class="dashboard-grid-equal reveal" style="margin-bottom:var(--space-lg);">
        <div class="chart-container">
            <div class="chart-header"><h3>Issues by Category</h3></div>
            <div class="chart-wrapper"><canvas id="categoryChart"></canvas></div>
        </div>
        <div class="chart-container">
            <div class="chart-header"><h3>Issues by Status</h3></div>
            <div class="chart-wrapper"><canvas id="statusChart"></canvas></div>
        </div>
    </div>

    <!-- Quick Tables -->
    <div class="dashboard-grid-equal reveal">
        <!-- Recent Users -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">Recent Users</h3><a href="users.php" class="btn btn-ghost btn-sm">Manage</a></div>
            <div class="table-container">
                <table class="table">
                    <thead><tr><th>User</th><th>Role</th><th>Joined</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                    <tr>
                        <td style="display:flex;align-items:center;gap:8px;">
                            <img src="<?= getAvatarUrl($u['avatar'], $u['name']) ?>" alt="" style="width:28px;height:28px;border-radius:50%;">
                            <?= htmlspecialchars($u['name']) ?>
                        </td>
                        <td><span class="badge badge-<?= $u['role']==='admin'?'danger':($u['role']==='faculty'?'warning':'info') ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td style="font-size:var(--text-xs);"><?= timeAgo($u['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Unread Messages -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">Unread Messages</h3></div>
            <?php if (empty($unreadMessages)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:var(--space-lg);">No unread messages</p>
            <?php else: ?>
            <div class="recent-list">
                <?php foreach ($unreadMessages as $msg): ?>
                <div class="recent-item">
                    <div class="recent-item-icon" style="background:var(--info-bg);color:var(--info);"><i class="fas fa-envelope"></i></div>
                    <div class="recent-item-info">
                        <h4><?= htmlspecialchars($msg['name']) ?>: <?= htmlspecialchars(truncateText($msg['subject'], 25)) ?></h4>
                        <p><?= htmlspecialchars(truncateText($msg['message'], 40)) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const categoryData = <?= json_encode($grievancesByCategory) ?>;
const statusData = <?= json_encode($grievancesByStatus) ?>;
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Status chart
    const ctx2 = document.getElementById('statusChart');
    if (ctx2 && typeof statusData !== 'undefined') {
        const statusColors = {open:'#EF4444',in_progress:'#F59E0B',resolved:'#22C55E',closed:'#6B7280'};
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: statusData.map(d => d.status.replace('_',' ').replace(/\b\w/g,l=>l.toUpperCase())),
                datasets: [{
                    label: 'Count',
                    data: statusData.map(d => d.cnt),
                    backgroundColor: statusData.map(d => statusColors[d.status]||'#667EEA'),
                    borderRadius: 8, borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#A1A1AA' } },
                    x: { grid: { display: false }, ticks: { color: '#A1A1AA' } }
                }
            }
        });
    }
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
