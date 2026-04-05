<?php
$pageTitle = 'Edit Issue';
$pageScripts = ['validation.js', 'upload.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();

$grievance = null;
$isEditMode = false;

// Check if edit parameter exists
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM grievances WHERE id = ?");
    $stmt->execute([$editId]);
    $grievance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$grievance) {
        setFlashMessage('error', 'Grievance not found.');
        header('Location: grievances.php');
        exit;
    }
    
    // Verify ownership (user can edit their own or admin can edit any)
    if (!isAdmin() && $grievance['user_id'] !== getCurrentUserId()) {
        setFlashMessage('error', 'You do not have permission to edit this grievance.');
        header('Location: grievances.php');
        exit;
    }
    
    $isEditMode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $priority = $_POST['priority'] ?? 'medium';
        
        // Validation
        if (empty($title)) throw new Exception('Issue title is required.');
        if (strlen($title) > 200) throw new Exception('Title must not exceed 200 characters.');
        if (empty($description)) throw new Exception('Description is required.');
        if (strlen($description) > 5000) throw new Exception('Description must not exceed 5000 characters.');
        if (empty($location)) throw new Exception('Location is required.');
        
        if (!$isEditMode) {
            // CREATE mode
            // Image upload
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed. Please try again.');
                }
                if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception('Image size must not exceed 2MB.');
                }
                $imagePath = handleFileUpload($_FILES['image'], 'grievances');
                if (!$imagePath) {
                    throw new Exception('Invalid image file or unsupported format.');
                }
            }
            
            // Insert into database
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO grievances (user_id, title, description, category, location, priority, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'open')");
            $stmt->execute([getCurrentUserId(), $title, $description, $category, $location, $priority, $imagePath]);
            
            setFlashMessage('success', 'Grievance reported successfully! Your issue has been submitted for review.');
            header('Location: grievances.php');
            exit;
        } else {
            // EDIT mode
            $grievanceId = (int)$_POST['grievance_id'];
            $db = getDB();
            
            // Re-verify ownership
            $stmt = $db->prepare("SELECT * FROM grievances WHERE id = ?");
            $stmt->execute([$grievanceId]);
            $existingGrievance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingGrievance) {
                throw new Exception('Grievance not found.');
            }
            
            if (!isAdmin() && $existingGrievance['user_id'] !== getCurrentUserId()) {
                throw new Exception('You do not have permission to edit this grievance.');
            }
            
            // Handle image upload (optional replaceme of existing)
            $imagePath = $existingGrievance['image_path'];
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed. Please try again.');
                }
                if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception('Image size must not exceed 2MB.');
                }
                
                // Delete old image if exists
                if ($existingGrievance['image_path'] && file_exists(__DIR__ . '/../' . $existingGrievance['image_path'])) {
                    unlink(__DIR__ . '/../' . $existingGrievance['image_path']);
                }
                
                $imagePath = handleFileUpload($_FILES['image'], 'grievances');
                if (!$imagePath) {
                    throw new Exception('Invalid image file or unsupported format.');
                }
            }
            
            // Update database
            $stmt = $db->prepare("UPDATE grievances SET title = ?, description = ?, category = ?, location = ?, priority = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $category, $location, $priority, $imagePath, $grievanceId]);
            
            setFlashMessage('success', 'Grievance updated successfully!');
            header('Location: grievance_detail.php?id=' . $grievanceId);
            exit;
        }
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-<?= $isEditMode ? 'edit' : 'exclamation-circle' ?>" style="color:var(--clr-grievances);"></i> <?= $isEditMode ? 'Edit Issue' : 'Report New Issue' ?></h1>
        <p><?= $isEditMode ? 'Update your grievance details' : 'Describe the campus issue you want to report' ?></p>
    </div>

    <div class="card" style="max-width:700px;">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" id="grievanceForm">
            <?php echo csrf_field(); ?>
            <?php if ($isEditMode): ?>
                <input type="hidden" name="grievance_id" value="<?= $grievance['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title" class="form-label">Issue Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Broken ceiling fan in Room 301" value="<?= htmlspecialchars($grievance['title'] ?? '') ?>" required>
                <div class="form-error" id="title-error"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category" class="form-label">Category <span class="required">*</span></label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="infrastructure" <?= ($grievance['category'] ?? '') === 'infrastructure' ? 'selected' : '' ?>>🔧 Infrastructure</option>
                        <option value="it" <?= ($grievance['category'] ?? '') === 'it' ? 'selected' : '' ?>>📶 IT / Network</option>
                        <option value="hygiene" <?= ($grievance['category'] ?? '') === 'hygiene' ? 'selected' : '' ?>>🧹 Hygiene</option>
                        <option value="safety" <?= ($grievance['category'] ?? '') === 'safety' ? 'selected' : '' ?>>🛡️ Safety</option>
                        <option value="electrical" <?= ($grievance['category'] ?? '') === 'electrical' ? 'selected' : '' ?>>⚡ Electrical</option>
                        <option value="academic" <?= ($grievance['category'] ?? '') === 'academic' ? 'selected' : '' ?>>🎓 Academic</option>
                        <option value="other" <?= ($grievance['category'] ?? 'other') === 'other' ? 'selected' : '' ?>>📋 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority" class="form-label">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="low" <?= ($grievance['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($grievance['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($grievance['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= ($grievance['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="location" class="form-label">Location <span class="required">*</span></label>
                <input type="text" id="location" name="location" class="form-control" placeholder="e.g., CS Block - Room 301" value="<?= htmlspecialchars($grievance['location'] ?? '') ?>" required>
                <div class="form-error" id="location-error"></div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="required">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Describe the issue in detail..." required><?= htmlspecialchars($grievance['description'] ?? '') ?></textarea>
                <div class="form-error" id="description-error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Photo Evidence (optional)</label>
                <?php if ($isEditMode && $grievance['image_path']): ?>
                <div style="margin-bottom: var(--space-md); padding: var(--space-md); background: var(--bg-secondary); border-radius: 8px; text-align: center;">
                    <img src="<?= htmlspecialchars($grievance['image_path']) ?>" alt="Grievance image" style="max-height: 200px; border-radius: 4px;">
                    <p class="form-help" style="margin-top: var(--space-sm);">Current image (upload new to replace)</p>
                </div>
                <?php endif; ?>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click or drag & drop an image</p>
                    <p class="form-help">Max 5MB — JPG, PNG, GIF, WebP</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            
            <div style="display:flex;gap:var(--space-md);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-<?= $isEditMode ? 'save' : 'paper-plane' ?>"></i> <?= $isEditMode ? 'Save Changes' : 'Submit Report' ?></button>
                <a href="<?= $isEditMode ? 'grievance_detail.php?id=' . $grievance['id'] : 'grievances.php' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new FormValidator('grievanceForm', {
        title: { required: true, minLength: 5, maxLength: 200, label: 'Title' },
        location: { required: true, minLength: 3, label: 'Location' },
        description: { required: true, minLength: 20, label: 'Description' }
    });
    initImagePreview('image', 'imagePreview', 'uploadArea');
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
