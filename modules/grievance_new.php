<?php
$pageTitle = 'Report Issue';
$pageScripts = ['validation.js', 'upload.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();

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
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-exclamation-circle" style="color:var(--clr-grievances);"></i> Report New Issue</h1>
        <p>Describe the campus issue you want to report</p>
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
            <div class="form-group">
                <label for="title" class="form-label">Issue Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Broken ceiling fan in Room 301" required>
                <div class="form-error" id="title-error"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category" class="form-label">Category <span class="required">*</span></label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="infrastructure">🔧 Infrastructure</option>
                        <option value="it">📶 IT / Network</option>
                        <option value="hygiene">🧹 Hygiene</option>
                        <option value="safety">🛡️ Safety</option>
                        <option value="electrical">⚡ Electrical</option>
                        <option value="academic">🎓 Academic</option>
                        <option value="other">📋 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority" class="form-label">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="location" class="form-label">Location <span class="required">*</span></label>
                <input type="text" id="location" name="location" class="form-control" placeholder="e.g., CS Block - Room 301" required>
                <div class="form-error" id="location-error"></div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="required">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Describe the issue in detail..." required></textarea>
                <div class="form-error" id="description-error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Photo Evidence (optional)</label>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click or drag & drop an image</p>
                    <p class="form-help">Max 5MB — JPG, PNG, GIF, WebP</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            
            <div style="display:flex;gap:var(--space-md);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Report</button>
                <a href="grievances.php" class="btn btn-secondary">Cancel</a>
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
