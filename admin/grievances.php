<?php
$pageTitle = 'Admin - Grievances';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$db = getDB();

// Fetch all faculty members for assignment dropdown
$faculty = $db->query("SELECT id, name FROM users WHERE role='faculty' ORDER BY name ASC")->fetchAll();

// Fetch grievances with assignment info
$grievances = $db->query("SELECT g.*, u.name as user_name, a.name as assigned_to_name FROM grievances g JOIN users u ON g.user_id=u.id LEFT JOIN users a ON g.assigned_to=a.id ORDER BY FIELD(g.status,'open','in_progress','resolved','closed'), g.created_at DESC")->fetchAll();

// Handle status and assignment updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $grievanceId = intval($_POST['update_id']);
        
        // Update status
        if (isset($_POST['status'])) {
            $newStatus = $_POST['status'];
            $resolved = $newStatus === 'resolved' ? ', resolved_at=NOW()' : '';
            $db->prepare("UPDATE grievances SET status=? $resolved WHERE id=?")->execute([$newStatus, $grievanceId]);
        }
        
        // Update assignment
        if (isset($_POST['assigned_to'])) {
            $assignedTo = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
            $db->prepare("UPDATE grievances SET assigned_to=? WHERE id=?")->execute([$assignedTo, $grievanceId]);
        }
        
        setFlashMessage('success','Grievance updated!');
        header('Location: grievances.php');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header"><h1>🔧 Manage Grievances</h1><p><?= count($grievances) ?> total grievances</p></div>
    
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>" style="margin-bottom:var(--space-md);">
        <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>
    
    <div class="card"><div class="table-container"><table class="table">
        <thead><tr><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned</th><th>Upvotes</th><th>Reporter</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($grievances as $g): ?>
        <tr>
            <td><a href="<?= SITE_URL ?>/modules/grievance_detail.php?id=<?= $g['id'] ?>" style="font-weight:500;"><?= htmlspecialchars(truncateText($g['title'],30)) ?></a></td>
            <td><span class="tag"><i class="fas fa-<?= getCategoryIcon($g['category']) ?>"></i> <?= ucfirst($g['category']) ?></span></td>
            <td><?= getPriorityBadge($g['priority']) ?></td>
            <td><?= getStatusBadge($g['status']) ?></td>
            <td style="font-size:var(--text-xs);"><?= $g['assigned_to_name'] ? htmlspecialchars($g['assigned_to_name']) : '—' ?></td>
            <td><span class="text-mono"><?= $g['upvotes'] ?></span></td>
            <td style="font-size:var(--text-sm);"><?= htmlspecialchars($g['user_name']) ?></td>
            <td>
                <form method="POST" style="display:flex;flex-direction:column;gap:6px;">
                    <input type="hidden" name="update_id" value="<?= $g['id'] ?>">
                    <div style="display:flex;gap:4px;">
                        <select name="status" class="form-control" style="width:auto;padding:4px 8px;font-size:11px;">
                            <option value="open" <?= $g['status']==='open'?'selected':'' ?>>Open</option>
                            <option value="in_progress" <?= $g['status']==='in_progress'?'selected':'' ?>>In Progress</option>
                            <option value="resolved" <?= $g['status']==='resolved'?'selected':'' ?>>Resolved</option>
                            <option value="closed" <?= $g['status']==='closed'?'selected':'' ?>>Closed</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm" style="padding:4px 8px;"><i class="fas fa-check"></i></button>
                    </div>
                    <select name="assigned_to" class="form-control" style="padding:4px 8px;font-size:11px;">
                        <option value="">Unassigned</option>
                        <?php foreach ($faculty as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= $g['assigned_to'] === $f['id'] ? 'selected' : '' ?>>→ <?= htmlspecialchars($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
