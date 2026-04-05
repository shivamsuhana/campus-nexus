<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$db = getDB();

$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($role) { $where .= " AND role=?"; $params[] = $role; }
if ($search) { $where .= " AND (name LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $db->prepare("SELECT * FROM users $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    try {
        $userId = intval($_POST['user_id']);
        $newRole = $_POST['new_role'];
        
        // Validate role
        if (!in_array($newRole, ['student', 'faculty', 'admin'])) {
            throw new Exception('Invalid role');
        }
        
        // Check if trying to demote self
        if ($userId === getCurrentUserId()) {
            throw new Exception('You cannot change your own role');
        }
        
        $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$newRole, $userId]);
        setFlashMessage('success', 'User role updated successfully!');
        header('Location: users.php?role=' . $role);
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header"><h1>👥 User Management</h1><p>Manage all registered users</p></div>

    <div class="filter-bar">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key==='Enter')window.location='users.php?search='+this.value">
        </div>
        <select onchange="window.location='users.php?role='+this.value">
            <option value="">All Roles</option>
            <option value="student" <?= $role==='student'?'selected':'' ?>>Students</option>
            <option value="faculty" <?= $role==='faculty'?'selected':'' ?>>Faculty</option>
            <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admins</option>
        </select>
        <span class="badge badge-primary badge-lg"><?= count($users) ?> users</span>
    </div>
    
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>" style="margin-bottom:var(--space-md);">
        <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>User</th><th>Email</th><th>Role</th><th>Department</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr id="user-<?= $u['id'] ?>">
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <img src="<?= getAvatarUrl($u['avatar'], $u['name']) ?>" alt="" style="width:32px;height:32px;border-radius:50%;">
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                        </div>
                    </td>
                    <td style="font-size:var(--text-sm);"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role']==='admin'?'danger':($u['role']==='faculty'?'warning':'info') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td style="font-size:var(--text-sm);"><?= htmlspecialchars($u['department']) ?></td>
                    <td>
                        <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-muted' ?>" id="status-<?= $u['id'] ?>">
                            <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td style="font-size:var(--text-xs);color:var(--text-muted);"><?= formatDate($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['id'] !== getCurrentUserId()): ?>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <!-- Role Change Form -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="change_role" value="1">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="new_role" class="form-control" style="width:auto;font-size:12px;padding:4px 6px;" onchange="if(confirm('Change role to ' + this.options[this.selectedIndex].text + '?')) this.form.submit();">
                                    <option value="">Change Role</option>
                                    <option value="student" <?= $u['role'] === 'student' ? 'disabled' : '' ?>>→ Student</option>
                                    <option value="faculty" <?= $u['role'] === 'faculty' ? 'disabled' : '' ?>>→ Faculty</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'disabled' : '' ?>>→ Admin</option>
                                </select>
                            </form>
                            <!-- Active/Inactive Toggle -->
                            <button class="btn btn-ghost btn-sm" onclick="toggleUser(<?= $u['id'] ?>)" title="Toggle active status">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </div>
                        <?php else: ?>
                        <span class="text-xs text-muted">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleUser(userId) {
    if (!confirm('Toggle this user\'s active status?')) return;
    fetch('<?= SITE_URL ?>/api/toggle_user.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({user_id: userId})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('status-' + userId);
            badge.textContent = data.is_active ? 'Active' : 'Inactive';
            badge.className = 'badge ' + (data.is_active ? 'badge-success' : 'badge-muted');
            showNotification('User status updated!', 'success');
        }
    });
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
