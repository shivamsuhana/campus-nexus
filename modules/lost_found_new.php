<?php
$pageTitle = 'Report Lost/Found Item';
$pageScripts = ['upload.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'lost';
        $category = $_POST['category'] ?? 'other';
        $location = sanitize($_POST['location'] ?? '');
        $item_date = $_POST['item_date'] ?? '';
        
        // Validation
        if (empty($title)) throw new Exception('Item title is required.');
        if (strlen($title) > 150) throw new Exception('Title must not exceed 150 characters.');
        if (empty($description)) throw new Exception('Description is required.');
        if (strlen($description) > 2000) throw new Exception('Description must not exceed 2000 characters.');
        if (empty($location)) throw new Exception('Location is required.');
        if (empty($item_date)) throw new Exception('Date is required.');
        
        // Validate date is not in the future
        $itemDateTime = new DateTime($item_date);
        $now = new DateTime();
        if ($itemDateTime > $now) {
            throw new Exception('Date cannot be in the future.');
        }
        
        // Image upload
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed. Please try again.');
            }
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                throw new Exception('Image size must not exceed 2MB.');
            }
            $imagePath = handleFileUpload($_FILES['image'], 'lost_found');
            if (!$imagePath) {
                throw new Exception('Invalid image file or unsupported format.');
            }
        }
        
        // Insert into database
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO lost_found (user_id, title, description, type, category, location, image_path, item_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')");
        $stmt->execute([getCurrentUserId(), $title, $description, $type, $category, $location, $imagePath, $item_date]);
        
        setFlashMessage('success', 'Item report submitted! Help others find their belongings or recover yours.');
        header('Location: lost_found.php');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-search-location" style="color:var(--clr-lost-found);"></i> Report Lost/Found Item</h1>
        <p>Help reunite items with their owners</p>
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
        <form method="POST" enctype="multipart/form-data">            <?php echo csrf_field(); ?>            <div class="form-row">
                <div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" class="form-control"><option value="lost">🔴 I Lost Something</option><option value="found">🟢 I Found Something</option></select>
                </div>
                <div class="form-group"><label class="form-label">Category</label>
                    <select name="category" class="form-control"><option value="electronics">Electronics</option><option value="documents">Documents</option><option value="accessories">Accessories</option><option value="clothing">Clothing</option><option value="other">Other</option></select>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Item Title *</label><input type="text" name="title" class="form-control" placeholder="e.g., Blue JBL Earbuds" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Location *</label><input type="text" name="location" class="form-control" placeholder="Where lost/found?" required></div>
                <div class="form-group"><label class="form-label">Date *</label><input type="date" name="item_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            </div>
            <div class="form-group"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="4" placeholder="Describe the item in detail..." required></textarea></div>
            <div class="form-group"><label class="form-label">Photo</label>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()"><i class="fas fa-camera"></i><p>Add a photo</p><img id="imagePreview" class="file-preview" alt=""></div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Report</button>
        </form>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{initImagePreview('image','imagePreview','uploadArea');});</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
