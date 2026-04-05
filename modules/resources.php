<?php
$pageTitle = 'Resources';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();

$subject = $_GET['subject'] ?? '';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($subject) { $where .= " AND r.subject LIKE ?"; $params[] = "%$subject%"; }
if ($type) { $where .= " AND r.type = ?"; $params[] = $type; }
if ($search) { $where .= " AND (r.title LIKE ? OR r.subject LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $db->prepare("SELECT r.*, u.name as uploader_name, u.avatar FROM resources r JOIN users u ON r.uploaded_by=u.id $where ORDER BY r.created_at DESC");
$stmt->execute($params);
$resources = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header-actions">
        <div>
            <div class="module-page-header">
                <div class="module-icon" style="background:rgba(67,233,123,0.12);color:var(--clr-resources);"><i class="fas fa-book-open"></i></div>
                <div><h1>Resource Hub</h1><p>Notes, slides, past papers & assignments</p></div>
            </div>
        </div>
        <?php if (isFacultyOrAdmin()): ?>
        <a href="resource_upload.php" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Resource</a>
        <?php endif; ?>
    </div>

    <div class="filter-bar">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search resources..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key==='Enter')window.location='resources.php?search='+this.value">
        </div>
        <select onchange="window.location='resources.php?type='+this.value+'&search=<?= urlencode($search) ?>'">
            <option value="">All Types</option>
            <option value="notes" <?= $type==='notes'?'selected':'' ?>>📝 Notes</option>
            <option value="slides" <?= $type==='slides'?'selected':'' ?>>📊 Slides</option>
            <option value="paper" <?= $type==='paper'?'selected':'' ?>>📄 Past Papers</option>
            <option value="assignment" <?= $type==='assignment'?'selected':'' ?>>📋 Assignments</option>
            <option value="other" <?= $type==='other'?'selected':'' ?>>📦 Other</option>
        </select>
    </div>

    <?php if (empty($resources)): ?>
    <div class="empty-state"><i class="fas fa-folder-open"></i><h3>No resources found</h3><p>Try adjusting your filters.</p></div>
    <?php else: ?>
    <div class="grid-auto">
        <?php foreach ($resources as $r): ?>
        <div class="card hover-lift">
            <div style="display:flex;gap:var(--space-md);align-items:flex-start;">
                <div class="resource-type-icon" style="background:<?php
                    $typeColors = ['notes'=>'rgba(67,233,123,0.12)','slides'=>'rgba(79,172,254,0.12)','paper'=>'rgba(250,112,154,0.12)','assignment'=>'rgba(245,158,11,0.12)','other'=>'rgba(161,140,209,0.12)'];
                    echo $typeColors[$r['type']] ?? 'var(--bg-glass)';
                ?>;color:<?php
                    $typeTextColors = ['notes'=>'var(--clr-resources)','slides'=>'var(--clr-attendance)','paper'=>'var(--clr-marketplace)','assignment'=>'var(--warning)','other'=>'var(--clr-lost-found)'];
                    echo $typeTextColors[$r['type']] ?? 'var(--text-muted)';
                ?>;">
                    <i class="fas fa-<?= getCategoryIcon($r['type']) ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <h4 class="card-title" style="margin-bottom:4px;"><?= htmlspecialchars($r['title']) ?></h4>
                    <p style="font-size:var(--text-xs);color:var(--text-muted);"><?= htmlspecialchars($r['subject']) ?> • <?= $r['semester'] ?? 'N/A' ?></p>
                </div>
            </div>
            <?php if ($r['description']): ?>
            <p style="font-size:var(--text-sm);color:var(--text-secondary);margin:var(--space-md) 0;"><?= htmlspecialchars(truncateText($r['description'], 100)) ?></p>
            <?php endif; ?>
            
            <!-- Rating Display -->
            <div style="margin:var(--space-sm) 0;">
                <div class="star-display">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= round($r['avg_rating']) ? 'filled' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span style="font-size:var(--text-xs);color:var(--text-muted);margin-left:4px;"><?= number_format($r['avg_rating'], 1) ?> (<?= $r['rating_count'] ?>)</span>
            </div>
            
            <div class="card-footer">
                <div class="resource-stats">
                    <span><i class="fas fa-download"></i> <?= $r['download_count'] ?></span>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($r['uploader_name']) ?></span>
                </div>
                <a href="<?= SITE_URL ?>/<?= htmlspecialchars($r['file_path']) ?>" class="btn btn-primary btn-sm" download>
                    <i class="fas fa-download"></i>
                </a>
            </div>
            
            <!-- AJAX Rating -->
            <div style="margin-top:var(--space-md);padding-top:var(--space-md);border-top:1px solid var(--border-glass);">
                <span style="font-size:var(--text-xs);color:var(--text-muted);">Rate this resource:</span>
                <div class="star-rating" id="rating-<?= $r['id'] ?>" style="direction:ltr;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star" data-rating="<?= $i ?>" onclick="rateResource(<?= $r['id'] ?>, <?= $i ?>)" style="cursor:pointer;"></i>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function rateResource(resourceId, rating) {
    fetch('<?= SITE_URL ?>/api/rate_resource.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({resource_id: resourceId, rating: rating})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Rating submitted! Avg: ' + data.avg_rating, 'success');
            // Highlight stars
            const container = document.getElementById('rating-' + resourceId);
            container.querySelectorAll('i').forEach((star, idx) => {
                star.classList.toggle('active', idx < rating);
            });
        } else {
            showNotification(data.message || 'Error', 'error');
        }
    });
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
