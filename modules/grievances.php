<?php
$pageTitle = 'Grievances';
$pageScripts = ['filter.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($category) { $where .= " AND g.category = ?"; $params[] = $category; }
if ($status) { $where .= " AND g.status = ?"; $params[] = $status; }
if ($search) { $where .= " AND (g.title LIKE ? OR g.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$countStmt = $db->prepare("SELECT COUNT(*) FROM grievances g $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$pagination = getPagination($total, $page, $perPage);

$stmt = $db->prepare("SELECT g.*, u.name as user_name, u.avatar FROM grievances g JOIN users u ON g.user_id=u.id $where ORDER BY g.created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}");
$stmt->execute($params);
$grievances = $stmt->fetchAll();
?>

<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <div class="page-header-actions">
        <div>
            <div class="module-page-header">
                <div class="module-icon" style="background:rgba(245,87,108,0.12);color:var(--clr-grievances);"><i class="fas fa-exclamation-circle"></i></div>
                <div><h1>Campus Grievances</h1><p>Report, track, and resolve campus issues</p></div>
            </div>
        </div>
        <a href="grievance_new.php" class="btn btn-primary"><i class="fas fa-plus"></i> Report Issue</a>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search grievances..." value="<?= htmlspecialchars($search) ?>"
                   onkeyup="if(event.key==='Enter')applyFilters()">
        </div>
        <select id="filterCategory" onchange="applyFilters()">
            <option value="">All Categories</option>
            <option value="infrastructure" <?= $category==='infrastructure'?'selected':'' ?>>Infrastructure</option>
            <option value="it" <?= $category==='it'?'selected':'' ?>>IT / Network</option>
            <option value="hygiene" <?= $category==='hygiene'?'selected':'' ?>>Hygiene</option>
            <option value="safety" <?= $category==='safety'?'selected':'' ?>>Safety</option>
            <option value="electrical" <?= $category==='electrical'?'selected':'' ?>>Electrical</option>
            <option value="academic" <?= $category==='academic'?'selected':'' ?>>Academic</option>
        </select>
        <select id="filterStatus" onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="open" <?= $status==='open'?'selected':'' ?>>Open</option>
            <option value="in_progress" <?= $status==='in_progress'?'selected':'' ?>>In Progress</option>
            <option value="resolved" <?= $status==='resolved'?'selected':'' ?>>Resolved</option>
        </select>
    </div>

    <!-- Grievances Grid -->
    <?php if (empty($grievances)): ?>
    <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <h3>No grievances found</h3>
        <p>Try adjusting your filters or be the first to report an issue.</p>
        <a href="grievance_new.php" class="btn btn-primary"><i class="fas fa-plus"></i> Report Issue</a>
    </div>
    <?php else: ?>
    <div class="grid-auto">
        <?php foreach ($grievances as $g): ?>
        <a href="grievance_detail.php?id=<?= $g['id'] ?>" class="card card-clickable grievance-card" style="text-decoration:none;color:inherit;">
            <?php if ($g['image_path']): ?>
            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($g['image_path']) ?>" alt="" class="card-image">
            <?php endif; ?>
            <div class="card-header">
                <h4 class="card-title"><?= htmlspecialchars(truncateText($g['title'], 50)) ?></h4>
                <?= getStatusBadge($g['status']) ?>
            </div>
            <p class="card-body" style="font-size:var(--text-sm);color:var(--text-secondary);">
                <?= htmlspecialchars(truncateText($g['description'], 100)) ?>
            </p>
            <div class="grievance-meta">
                <span><i class="fas fa-<?= getCategoryIcon($g['category']) ?>"></i> <?= ucfirst($g['category']) ?></span>
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(truncateText($g['location'], 20)) ?></span>
                <span><i class="fas fa-arrow-up"></i> <?= $g['upvotes'] ?></span>
            </div>
            <div class="card-footer">
                <div style="display:flex;align-items:center;gap:8px;">
                    <img src="<?= getAvatarUrl($g['avatar'], $g['user_name']) ?>" alt="" class="avatar-sm" style="width:24px;height:24px;border-radius:50%;">
                    <span style="font-size:var(--text-xs);"><?= htmlspecialchars($g['user_name']) ?></span>
                </div>
                <span style="font-size:var(--text-xs);color:var(--text-muted);"><?= timeAgo($g['created_at']) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?= renderPagination($pagination, 'grievances.php') ?>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('filterCategory').value;
    const status = document.getElementById('filterStatus').value;
    let url = 'grievances.php?';
    if (search) url += 'search=' + encodeURIComponent(search) + '&';
    if (category) url += 'category=' + category + '&';
    if (status) url += 'status=' + status + '&';
    window.location.href = url;
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
