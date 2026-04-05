<?php
$pageTitle = 'Grievance Detail';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();
$db = getDB();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT g.*, u.name as user_name, u.avatar, u.role as user_role FROM grievances g JOIN users u ON g.user_id=u.id WHERE g.id=?");
$stmt->execute([$id]);
$grievance = $stmt->fetch();
if (!$grievance) { setFlashMessage('error','Grievance not found.'); header('Location: grievances.php'); exit; }

// Handle comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    verify_csrf_token();
    $comment = sanitize($_POST['comment']);
    if (!empty($comment)) {
        $db->prepare("INSERT INTO grievance_comments (grievance_id, user_id, comment) VALUES (?, ?, ?)")->execute([$id, getCurrentUserId(), $comment]);
        setFlashMessage('success', 'Comment posted!');
        header("Location: grievance_detail.php?id=$id"); exit;
    }
}
// Handle status update (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status']) && isAdmin()) {
    verify_csrf_token();
    $newStatus = $_POST['new_status'];
    $resolved = $newStatus === 'resolved' ? ', resolved_at = NOW()' : '';
    $db->prepare("UPDATE grievances SET status = ? $resolved WHERE id = ?")->execute([$newStatus, $id]);
    setFlashMessage('success', 'Status updated!');
    header("Location: grievance_detail.php?id=$id"); exit;
}

$comments = $db->prepare("SELECT c.*, u.name, u.avatar, u.role FROM grievance_comments c JOIN users u ON c.user_id=u.id WHERE c.grievance_id=? ORDER BY c.created_at ASC");
$comments->execute([$id]);
$comments = $comments->fetchAll();

// Check if user upvoted
$userUpvoted = false;
if (isLoggedIn()) {
    $upCheck = $db->prepare("SELECT id FROM grievance_upvotes WHERE grievance_id=? AND user_id=?");
    $upCheck->execute([$id, getCurrentUserId()]);
    $userUpvoted = $upCheck->fetch() !== false;
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <a href="grievances.php" class="btn btn-ghost btn-sm" style="margin-bottom:var(--space-md);"><i class="fas fa-arrow-left"></i> Back to Grievances</a>
    
    <div style="display:grid;grid-template-columns:1fr 300px;gap:var(--space-xl);">
        <!-- Main Content -->
        <div>
            <?php if ($grievance['image_path']): ?>
            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($grievance['image_path']) ?>" alt="" class="detail-image">
            <?php endif; ?>
            
            <div class="detail-header">
                <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-md);"><?= htmlspecialchars($grievance['title']) ?></h1>
                <div class="detail-meta">
                    <?= getStatusBadge($grievance['status']) ?>
                    <?= getPriorityBadge($grievance['priority']) ?>
                    <span class="tag"><i class="fas fa-<?= getCategoryIcon($grievance['category']) ?>"></i> <?= ucfirst($grievance['category']) ?></span>
                    <span class="tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($grievance['location']) ?></span>
                </div>
            </div>
            
            <div class="detail-content">
                <p><?= nl2br(htmlspecialchars($grievance['description'])) ?></p>
            </div>
            
            <!-- Actions -->
            <div style="display:flex;gap:var(--space-md);margin-bottom:var(--space-xl);">
                <button class="upvote-btn <?= $userUpvoted ? 'upvoted' : '' ?>" onclick="upvoteGrievance(<?= $id ?>)" id="upvoteBtn">
                    <i class="fas fa-arrow-up"></i>
                    <span id="upvoteCount"><?= $grievance['upvotes'] ?></span>
                </button>
                <?php if (isAdmin()): ?>
                <form method="POST" style="display:flex;gap:8px;">
                    <?php echo csrf_field(); ?>
                    <select name="new_status" class="form-control" style="width:auto;">
                        <option value="open" <?= $grievance['status']==='open'?'selected':'' ?>>Open</option>
                        <option value="in_progress" <?= $grievance['status']==='in_progress'?'selected':'' ?>>In Progress</option>
                        <option value="resolved" <?= $grievance['status']==='resolved'?'selected':'' ?>>Resolved</option>
                        <option value="closed" <?= $grievance['status']==='closed'?'selected':'' ?>>Closed</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
                </form>
                <?php endif; ?>
                <?php if (getCurrentUserId() === $grievance['user_id'] || isAdmin()): ?>
                <a href="grievance_edit.php?edit=<?= $id ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <form method="POST" action="../delete.php" style="display:flex;gap:8px;" onsubmit="return confirm('Delete this grievance? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="grievance">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="redirect" value="<?= SITE_URL ?>/modules/grievances.php">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Comments -->
            <div class="comments-section">
                <h3 style="margin-bottom:var(--space-lg);">Comments (<?= count($comments) ?>)</h3>
                <?php foreach ($comments as $c): ?>
                <div class="comment">
                    <img src="<?= getAvatarUrl($c['avatar'], $c['name']) ?>" alt="" class="comment-avatar">
                    <div class="comment-body">
                        <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($c['name']) ?></span>
                            <?php if ($c['role'] !== 'student'): ?><span class="badge badge-primary" style="font-size:9px;"><?= ucfirst($c['role']) ?></span><?php endif; ?>
                            <span class="comment-time"><?= timeAgo($c['created_at']) ?></span>
                        </div>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Add Comment -->
                <form method="POST" style="margin-top:var(--space-lg);">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-comment"></i> Post Comment</button>
                </form>
            </div>
        </div>
        
        <!-- Sidebar Info -->
        <div>
            <div class="card detail-sidebar">
                <h4 style="margin-bottom:var(--space-md);">Details</h4>
                <div style="display:flex;flex-direction:column;gap:var(--space-md);">
                    <div>
                        <span class="text-xs text-muted">Reported by</span>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                            <img src="<?= getAvatarUrl($grievance['avatar'], $grievance['user_name']) ?>" alt="" style="width:28px;height:28px;border-radius:50%;">
                            <span style="font-size:var(--text-sm);font-weight:500;"><?= htmlspecialchars($grievance['user_name']) ?></span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-muted">Reported on</span>
                        <p style="font-size:var(--text-sm);"><?= formatDateTime($grievance['created_at']) ?></p>
                    </div>
                    <div>
                        <span class="text-xs text-muted">Last updated</span>
                        <p style="font-size:var(--text-sm);"><?= formatDateTime($grievance['updated_at']) ?></p>
                    </div>
                    <?php if ($grievance['resolved_at']): ?>
                    <div>
                        <span class="text-xs text-muted">Resolved on</span>
                        <p style="font-size:var(--text-sm);color:var(--success);"><?= formatDateTime($grievance['resolved_at']) ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <span class="text-xs text-muted">Upvotes</span>
                        <p style="font-size:var(--text-xl);font-weight:800;font-family:var(--font-mono);"><?= $grievance['upvotes'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function upvoteGrievance(id) {
    fetch('<?= SITE_URL ?>/api/upvote.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({grievance_id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('upvoteCount').textContent = data.count;
            document.getElementById('upvoteBtn').classList.toggle('upvoted');
        } else {
            showNotification(data.message || 'Error', 'error');
        }
    });
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
