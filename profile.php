<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';
requireLogin();
$db = getDB();

$userId = getCurrentUserId();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['change_password'])) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('All password fields are required.');
            }
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New password and confirmation do not match.');
            }
            if (strlen($newPassword) < 6) {
                throw new Exception('New password must be at least 6 characters.');
            }

            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $dbUser = $stmt->fetch();

            if (!password_verify($currentPassword, $dbUser['password'])) {
                throw new Exception('Incorrect current password.');
            }

            // Update to new password (hashed)
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            setFlashMessage('success', 'Password successfully updated!');
            header('Location: profile.php');
            exit;
        }

        $name = sanitize($_POST['name'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        $department = sanitize($_POST['department'] ?? '');
        
        // Validate input
        if (empty($name)) {
            throw new Exception('Name is required.');
        }
        if (strlen($name) > 100) {
            throw new Exception('Name must not exceed 100 characters.');
        }
        if (strlen($bio) > 1000) {
            throw new Exception('Bio must not exceed 1000 characters.');
        }
        
        // Avatar upload
        $avatarPath = $user['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            // Validate file upload
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed. Please try again.');
            }
            
            // Check file size (max 2MB for avatars)
            if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                throw new Exception('Avatar file size must not exceed 2MB.');
            }
            
            $uploaded = handleFileUpload($_FILES['avatar'], 'avatars');
            if (!$uploaded) {
                throw new Exception('Invalid image file or unsupported format. Please use JPEG, PNG, GIF, or WebP.');
            }
            
            // Delete old avatar if exists and is not the default
            if ($user['avatar'] && file_exists(__DIR__ . '/' . $user['avatar'])) {
                unlink(__DIR__ . '/' . $user['avatar']);
            }
            
            $avatarPath = $uploaded;
        }
        
        // Update database
        $stmt = $db->prepare("UPDATE users SET name = ?, bio = ?, department = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $department, $avatarPath, $userId]);
        
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_department'] = $department;
        $_SESSION['user_avatar'] = $avatarPath;
        
        setFlashMessage('success', 'Profile updated successfully!');
        header('Location: profile.php');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}

// User stats
$stats = getDashboardStats($user['role'], $userId);
// Recent activity
$myGrievances = $db->prepare("SELECT * FROM grievances WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$myGrievances->execute([$userId]);
$myGrievances = $myGrievances->fetchAll();
?>

<?php require_once 'includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <!-- Profile Header -->
    <div class="profile-header reveal">
        <img src="<?= getAvatarUrl($user['avatar'], $user['name']) ?>" alt="Avatar" class="profile-avatar">
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <p class="profile-role"><?= ucfirst($user['role']) ?></p>
            <p class="profile-dept"><i class="fas fa-building"></i> <?= htmlspecialchars($user['department']) ?></p>
            <?php if ($user['bio']): ?>
            <p style="margin-top:var(--space-sm);color:var(--text-secondary);"><?= htmlspecialchars($user['bio']) ?></p>
            <?php endif; ?>
            <div class="profile-stats">
                <?php if ($user['role'] === 'student'): ?>
                <div class="profile-stat"><div class="profile-stat-value"><?= $stats['my_issues'] ?? 0 ?></div><div class="profile-stat-label">Issues</div></div>
                <div class="profile-stat"><div class="profile-stat-value"><?= $stats['my_listings'] ?? 0 ?></div><div class="profile-stat-label">Listings</div></div>
                <div class="profile-stat"><div class="profile-stat-value"><?= $stats['my_events'] ?? 0 ?></div><div class="profile-stat-label">Events</div></div>
                <div class="profile-stat"><div class="profile-stat-value"><?= $stats['attendance'] ?? 100 ?>%</div><div class="profile-stat-label">Attendance</div></div>
                <?php else: ?>
                <div class="profile-stat"><div class="profile-stat-value"><?= $stats['my_resources'] ?? 0 ?></div><div class="profile-stat-label">Resources</div></div>
                <div class="profile-stat"><div class="profile-stat-value"><?= $stats['my_events'] ?? 0 ?></div><div class="profile-stat-label">Events</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="switchTab('activity')">Activity</div>
        <div class="tab" onclick="switchTab('edit')">Edit Profile</div>
    </div>
    
    <!-- Activity Tab -->
    <div class="tab-content active" id="tab-activity">
        <h3 style="margin-bottom:var(--space-lg);">My Recent Grievances</h3>
        <?php if (empty($myGrievances)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No grievances yet</h3>
            <p>You haven't reported any campus issues.</p>
            <a href="modules/grievance_new.php" class="btn btn-primary"><i class="fas fa-plus"></i> Report an Issue</a>
        </div>
        <?php else: ?>
        <div class="grid-auto">
            <?php foreach ($myGrievances as $g): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?= htmlspecialchars(truncateText($g['title'], 40)) ?></h4>
                    <?= getStatusBadge($g['status']) ?>
                </div>
                <p style="font-size:var(--text-sm);color:var(--text-secondary);"><?= htmlspecialchars(truncateText($g['description'], 80)) ?></p>
                <div class="card-footer">
                    <?= getPriorityBadge($g['priority']) ?>
                    <span class="text-xs text-muted"><?= timeAgo($g['created_at']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Profile Tab -->
    <div class="tab-content" id="tab-edit">
        <div class="card" style="max-width:600px;">
            <h3 style="margin-bottom:var(--space-lg);">Edit Profile</h3>
            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control">
                    <small class="text-muted">Max size: 2MB. Supported formats: JPEG, PNG, GIF, WebP</small>
                </div>
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-control">
                        <?php foreach (['Computer Science','Electronics','Mechanical','Civil','Electrical','General'] as $d): ?>
                        <option value="<?= $d ?>" <?= $user['department'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </form>
            
            <hr style="margin: var(--space-xl) 0; border: 0; border-top: 1px solid var(--border-color);">

            <h3 style="margin-bottom:var(--space-md);">Change Password</h3>
            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group" style="max-width: 400px;">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-row" style="max-width: 400px;">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top:var(--space-sm);">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/js/tabs.js"></script>
<?php require_once 'includes/footer.php'; ?>
