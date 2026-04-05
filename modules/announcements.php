<?php
$pageTitle = 'Announcements';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();

$dept = $_GET['dept'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($dept) { $where .= " AND a.department = ?"; $params[] = $dept; }

$stmt = $db->prepare("SELECT a.*, u.name as posted_by_name, u.role FROM announcements a JOIN users u ON a.posted_by=u.id $where ORDER BY a.is_pinned DESC, a.created_at DESC");
$stmt->execute($params);
$announcements = $stmt->fetchAll();

// Handle new announcement (faculty/admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isFacultyOrAdmin()) {
    $attachPath = !empty($_FILES['attachment']['name']) ? handleDocumentUpload($_FILES['attachment'], 'announcements') : null;
    $stmt = $db->prepare("INSERT INTO announcements (posted_by,title,content,priority,department,attachment_path,is_pinned) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([getCurrentUserId(), sanitize($_POST['title']), sanitize($_POST['content']), $_POST['priority'], sanitize($_POST['department']), $attachPath, isset($_POST['is_pinned']) ? 1 : 0]);
    setFlashMessage('success', 'Announcement posted!');
    header('Location: announcements.php'); exit;
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header-actions">
        <div>
            <div class="module-page-header">
                <div class="module-icon" style="background:rgba(48,207,208,0.12);color:var(--clr-announcements);"><i class="fas fa-bullhorn"></i></div>
                <div><h1>Announcements</h1><p>Campus notices & departmental updates</p></div>
            </div>
        </div>
        <?php if (isFacultyOrAdmin()): ?>
        <button class="btn btn-primary" onclick="document.getElementById('newAnnouncement').classList.toggle('hidden')"><i class="fas fa-plus"></i> Post Announcement</button>
        <?php endif; ?>
    </div>

    <!-- New Announcement Form -->
    <?php if (isFacultyOrAdmin()): ?>
    <div id="newAnnouncement" class="card hidden" style="margin-bottom:var(--space-xl);">
        <h3 style="margin-bottom:var(--space-lg);">Post New Announcement</h3>
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Priority</label>
                    <select name="priority" class="form-control"><option value="normal">Normal</option><option value="important">Important</option><option value="urgent">Urgent</option></select>
                </div>
                <div class="form-group"><label class="form-label">Department</label>
                    <select name="department" class="form-control"><option value="General">General</option><option value="Computer Science">Computer Science</option><option value="Electronics">Electronics</option><option value="Mechanical">Mechanical</option><option value="Civil">Civil</option></select>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Content *</label><textarea name="content" class="form-control" rows="4" required></textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Attachment (optional)</label><input type="file" name="attachment" class="form-control"></div>
                <div class="form-group" style="display:flex;align-items:flex-end;">
                    <label class="form-check"><input type="checkbox" name="is_pinned"><label>Pin this announcement</label></label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="filter-bar">
        <select onchange="window.location='announcements.php?dept='+this.value">
            <option value="">All Departments</option>
            <option value="General" <?= $dept==='General'?'selected':'' ?>>General</option>
            <option value="Computer Science" <?= $dept==='Computer Science'?'selected':'' ?>>Computer Science</option>
            <option value="Electronics" <?= $dept==='Electronics'?'selected':'' ?>>Electronics</option>
            <option value="Mechanical" <?= $dept==='Mechanical'?'selected':'' ?>>Mechanical</option>
        </select>
    </div>

    <!-- Announcements List -->
    <?php if (empty($announcements)): ?>
    <div class="empty-state"><i class="fas fa-bullhorn"></i><h3>No announcements</h3></div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:var(--space-md);">
        <?php foreach ($announcements as $a): ?>
        <div class="card announcement-card <?= $a['priority'] ?> <?= $a['is_pinned'] ? 'pinned' : '' ?>">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:var(--space-sm);flex-wrap:wrap;">
                    <?php if ($a['is_pinned']): ?><span class="badge badge-primary"><i class="fas fa-thumbtack"></i> Pinned</span><?php endif; ?>
                    <?php if ($a['priority'] === 'urgent'): ?><span class="badge badge-danger">Urgent</span>
                    <?php elseif ($a['priority'] === 'important'): ?><span class="badge badge-warning">Important</span>
                    <?php else: ?><span class="badge badge-info">Normal</span><?php endif; ?>
                    <span class="tag"><?= htmlspecialchars($a['department']) ?></span>
                </div>
                <span style="font-size:var(--text-xs);color:var(--text-muted);white-space:nowrap;"><?= timeAgo($a['created_at']) ?></span>
            </div>
            <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-sm);"><?= htmlspecialchars($a['title']) ?></h3>
            <p style="font-size:var(--text-sm);color:var(--text-secondary);line-height:var(--leading-relaxed);"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
            <?php if ($a['attachment_path']): ?>
            <div style="margin-top:var(--space-md);">
                <a href="<?= SITE_URL ?>/<?= htmlspecialchars($a['attachment_path']) ?>" class="btn btn-secondary btn-sm" download><i class="fas fa-paperclip"></i> Attachment</a>
            </div>
            <?php endif; ?>
            <div style="margin-top:var(--space-md);font-size:var(--text-xs);color:var(--text-muted);">
                Posted by <strong><?= htmlspecialchars($a['posted_by_name']) ?></strong> (<?= ucfirst($a['role']) ?>)
                on <?= formatDateTime($a['created_at']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
