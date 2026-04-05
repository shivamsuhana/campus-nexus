<?php
$pageTitle = 'Edit Lost/Found Item';
$pageScripts = ['upload.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();

$item = null;
$isEditMode = false;

// Check if edit parameter exists
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM lost_found WHERE id = ?");
    $stmt->execute([$editId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        setFlashMessage('error', 'Item not found.');
        header('Location: lost_found.php');
        exit;
    }
    
    // Verify ownership (user can edit their own or admin can edit any)
    if (!isAdmin() && $item['user_id'] !== getCurrentUserId()) {
        setFlashMessage('error', 'You do not have permission to edit this item.');
        header('Location: lost_found.php');
        exit;
    }
    
    $isEditMode = true;
}

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
        
        // Validate date is not in the future (only for new items, not edits)
        if (!$isEditMode) {
            $itemDateTime = new DateTime($item_date);
            $now = new DateTime();
            if ($itemDateTime > $now) {
                throw new Exception('Date cannot be in the future.');
            }
        }
        
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
        } else {
            // EDIT mode
            $itemId = (int)$_POST['item_id'];
            $db = getDB();
            
            // Re-verify ownership
            $stmt = $db->prepare("SELECT * FROM lost_found WHERE id = ?");
            $stmt->execute([$itemId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingItem) {
                throw new Exception('Item not found.');
            }
            
            if (!isAdmin() && $existingItem['user_id'] !== getCurrentUserId()) {
                throw new Exception('You do not have permission to edit this item.');
            }
            
            // Handle image upload (optional replace)
            $imagePath = $existingItem['image_path'];
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed. Please try again.');
                }
                if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception('Image size must not exceed 2MB.');
                }
                
                // Delete old image if exists
                if ($existingItem['image_path'] && file_exists(__DIR__ . '/../' . $existingItem['image_path'])) {
                    unlink(__DIR__ . '/../' . $existingItem['image_path']);
                }
                
                $imagePath = handleFileUpload($_FILES['image'], 'lost_found');
                if (!$imagePath) {
                    throw new Exception('Invalid image file or unsupported format.');
                }
            }
            
            // Update database
            $stmt = $db->prepare("UPDATE lost_found SET title = ?, description = ?, type = ?, category = ?, location = ?, image_path = ?, item_date = ? WHERE id = ?");
            $stmt->execute([$title, $description, $type, $category, $location, $imagePath, $item_date, $itemId]);
            
            setFlashMessage('success', 'Item updated successfully!');
            header('Location: lost_found.php?view_item=' . $itemId);
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
        <h1><i class="fas fa-<?= $isEditMode ? 'edit' : 'search-location' ?>" style="color:var(--clr-lost-found);"></i> <?= $isEditMode ? 'Edit Lost/Found Item' : 'Report Lost/Found Item' ?></h1>
        <p><?= $isEditMode ? 'Update your item details' : 'Help reunite items with their owners' ?></p>
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
            <?php if ($isEditMode): ?>
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-control">
                        <option value="lost" <?= ($item['type'] ?? '') === 'lost' ? 'selected' : '' ?>>🔴 I Lost Something</option>
                        <option value="found" <?= ($item['type'] ?? '') === 'found' ? 'selected' : '' ?>>🟢 I Found Something</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="electronics" <?= ($item['category'] ?? '') === 'electronics' ? 'selected' : '' ?>>Electronics</option>
                        <option value="documents" <?= ($item['category'] ?? '') === 'documents' ? 'selected' : '' ?>>Documents</option>
                        <option value="accessories" <?= ($item['category'] ?? '') === 'accessories' ? 'selected' : '' ?>>Accessories</option>
                        <option value="clothing" <?= ($item['category'] ?? '') === 'clothing' ? 'selected' : '' ?>>Clothing</option>
                        <option value="other" <?= ($item['category'] ?? 'other') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Item Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Blue JBL Earbuds" value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location *</label>
                    <input type="text" name="location" class="form-control" placeholder="Where lost/found?" value="<?= htmlspecialchars($item['location'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="item_date" class="form-control" value="<?= htmlspecialchars($item['item_date'] ?? date('Y-m-d')) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Describe the item in detail..." required><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Photo</label>
                <?php if ($isEditMode && $item['image_path']): ?>
                <div style="margin-bottom: var(--space-md); padding: var(--space-md); background: var(--bg-secondary); border-radius: 8px; text-align: center;">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item image" style="max-height: 200px; border-radius: 4px;">
                    <p class="form-help" style="margin-top: var(--space-sm);">Current photo (upload new to replace)</p>
                </div>
                <?php endif; ?>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-camera"></i>
                    <p>Add or update a photo</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            <div style="display:flex;gap:var(--space-md);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-<?= $isEditMode ? 'save' : 'check' ?>"></i> <?= $isEditMode ? 'Save Changes' : 'Submit Report' ?></button>
                <a href="lost_found.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{initImagePreview('image','imagePreview','uploadArea');});</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
