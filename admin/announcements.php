<?php
$pageTitle = 'Admin - Announcements';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$db = getDB();
$announcements = $db->query("SELECT a.*, u.name as posted_by_name FROM announcements a JOIN users u ON a.posted_by=u.id ORDER BY a.created_at DESC")->fetchAll();

$editMode = false;
$editAnnouncement = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $title = sanitize($_POST['title'] ?? '');
            $content = sanitize($_POST['content'] ?? '');
            $priority = $_POST['priority'] ?? 'normal';
            $department = sanitize($_POST['department'] ?? 'all');
            
            if (empty($title)) throw new Exception('Title is required.');
            if (empty($content)) throw new Exception('Content is required.');
            
            if ($_POST['action'] === 'create') {
                $db->prepare("INSERT INTO announcements (title, content, priority, department, posted_by) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$title, $content, $priority, $department, getCurrentUserId()]);
                setFlashMessage('success', 'Announcement created!');
            } else {
                $id = intval($_POST['announcement_id']);
                $db->prepare("UPDATE announcements SET title=?, content=?, priority=?, department=? WHERE id=?")
                    ->execute([$title, $content, $priority, $department, $id]);
                setFlashMessage('success', 'Announcement updated!');
            }
            header('Location: announcements.php');
            exit;
        }
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $db->prepare("DELETE FROM announcements WHERE id=?")->execute([intval($_POST['delete_id'])]);
    setFlashMessage('success','Announcement deleted.'); header('Location: announcements.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_pin'])) {
    $db->prepare("UPDATE announcements SET is_pinned = NOT is_pinned WHERE id=?")->execute([intval($_POST['toggle_pin'])]);
    header('Location: announcements.php'); exit;
}

if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM announcements WHERE id=?");
    $stmt->execute([$editId]);
    $editAnnouncement = $stmt->fetch();
    if ($editAnnouncement) {
        $editMode = true;
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header"><h1>📢 Manage Announcements</h1></div>
    
    <!-- Create/Edit Form -->
    <div class="card" style="max-width:700px;margin-bottom:var(--space-xl);">
        <h3 style="margin-bottom:var(--space-md);"><?= $editMode ? 'Edit Announcement' : 'Create New Announcement' ?></h3>
        
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editMode ? 'update' : 'create' ?>">
            <?php if ($editMode): ?>
                <input type="hidden" name="announcement_id" value="<?= $editAnnouncement['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Campus Maintenance Schedule" value="<?= htmlspecialchars($editAnnouncement['title'] ?? '') ?>" required maxlength="200">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control">
                        <option value="normal" <?= ($editAnnouncement['priority'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="important" <?= ($editAnnouncement['priority'] ?? '') === 'important' ? 'selected' : '' ?>>Important</option>
                        <option value="urgent" <?= ($editAnnouncement['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Department</label>
                    <select name="department" class="form-control">
                        <option value="all" <?= ($editAnnouncement['department'] ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="cs" <?= ($editAnnouncement['department'] ?? '') === 'cs' ? 'selected' : '' ?>>Computer Science</option>
                        <option value="ec" <?= ($editAnnouncement['department'] ?? '') === 'ec' ? 'selected' : '' ?>>Electronics</option>
                        <option value="me" <?= ($editAnnouncement['department'] ?? '') === 'me' ? 'selected' : '' ?>>Mechanical</option>
                        <option value="ce" <?= ($editAnnouncement['department'] ?? '') === 'ce' ? 'selected' : '' ?>>Civil</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Content <span class="required">*</span></label>
                <textarea name="content" class="form-control" rows="5" placeholder="Write the announcement content..." required><?= htmlspecialchars($editAnnouncement['content'] ?? '') ?></textarea>
            </div>
            
            <div style="display:flex;gap:var(--space-md);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-<?= $editMode ? 'save' : 'plus' ?>"></i> <?= $editMode ? 'Update' : 'Create' ?> Announcement</button>
                <?php if ($editMode): ?>
                    <a href="announcements.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card"><div class="table-container"><table class="table">
        <thead><tr><th>Title</th><th>Priority</th><th>Department</th><th>Author</th><th>Pinned</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($announcements as $a): ?>
        <tr>
            <td><strong><?= htmlspecialchars(truncateText($a['title'],30)) ?></strong></td>
            <td><?php if ($a['priority']==='urgent') echo '<span class="badge badge-danger">Urgent</span>';
                elseif ($a['priority']==='important') echo '<span class="badge badge-warning">Important</span>';
                else echo '<span class="badge badge-info">Normal</span>'; ?></td>
            <td style="font-size:var(--text-sm);"><?= htmlspecialchars($a['department']) ?></td>
            <td style="font-size:var(--text-sm);"><?= htmlspecialchars($a['posted_by_name']) ?></td>
            <td>
                <form method="POST" style="display:inline;"><input type="hidden" name="toggle_pin" value="<?= $a['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm"><i class="fas fa-thumbtack" style="color:<?= $a['is_pinned']?'var(--primary)':'var(--text-muted)' ?>;"></i></button></form>
            </td>
            <td style="font-size:var(--text-xs);"><?= formatDate($a['created_at']) ?></td>
            <td style="display:flex;gap:4px;">
                <a href="announcements.php?edit=<?= $a['id'] ?>" class="btn btn-secondary btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                <form method="POST" onsubmit="return confirm('Delete this announcement?')" style="display:inline;"><input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm text-danger"><i class="fas fa-trash"></i></button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
