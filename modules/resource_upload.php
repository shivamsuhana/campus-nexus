<?php
$pageTitle = 'Upload Resource';
require_once __DIR__ . '/../includes/header.php';
requireFacultyOrAdmin();
initCSRFToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $semester = sanitize($_POST['semester'] ?? '');
        $type = $_POST['type'] ?? 'notes';
        
        // Validation
        if (empty($title)) throw new Exception('Resource title is required.');
        if (strlen($title) > 200) throw new Exception('Title must not exceed 200 characters.');
        if (empty($subject)) throw new Exception('Subject is required.');
        
        // File upload
        if (empty($_FILES['file']['name'])) {
            throw new Exception('Please select a file to upload.');
        }
        
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed. Please try again.');
        }
        
        if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size must not exceed 5MB.');
        }
        
        $filePath = handleDocumentUpload($_FILES['file'], 'resources');
        if (!$filePath) {
            throw new Exception('Invalid file type or unsupported format. Allowed: PDF, Word, PowerPoint, Images.');
        }
        
        // Insert into database
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO resources (uploaded_by, title, description, subject, semester, type, file_path, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([getCurrentUserId(), $title, $description, $subject, $semester, $type, $filePath, $_FILES['file']['size']]);
        
        setFlashMessage('success', 'Resource uploaded successfully!');
        header('Location: resources.php');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-upload" style="color:var(--clr-resources);"></i> Upload Resource</h1>
        <p>Share educational materials with the campus community</p>
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
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" placeholder="e.g., Data Structures Complete Notes" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Subject *</label><input type="text" name="subject" class="form-control" placeholder="e.g., Data Structures" required></div>
                <div class="form-group"><label class="form-label">Semester</label>
                    <select name="semester" class="form-control">
                        <option value="1st Sem">1st Sem</option><option value="2nd Sem">2nd Sem</option><option value="3rd Sem">3rd Sem</option>
                        <option value="4th Sem">4th Sem</option><option value="5th Sem">5th Sem</option><option value="6th Sem" selected>6th Sem</option>
                        <option value="7th Sem">7th Sem</option><option value="8th Sem">8th Sem</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="notes">📝 Notes</option><option value="slides">📊 Slides</option>
                    <option value="paper">📄 Past Paper</option><option value="assignment">📋 Assignment</option><option value="other">📦 Other</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea></div>
            <div class="form-group"><label class="form-label">File * (PDF, DOC, PPT, images — max 5MB)</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png,.gif" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
