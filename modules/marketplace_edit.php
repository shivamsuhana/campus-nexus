<?php
$pageTitle = 'Edit Listing';
$pageScripts = ['validation.js', 'upload.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();

$listing = null;
$isEditMode = false;

// Check if edit parameter exists
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM marketplace_listings WHERE id = ?");
    $stmt->execute([$editId]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$listing) {
        setFlashMessage('error', 'Listing not found.');
        header('Location: marketplace.php');
        exit;
    }
    
    // Verify ownership (user can edit their own or admin can edit any)
    if (!isAdmin() && $listing['seller_id'] !== getCurrentUserId()) {
        setFlashMessage('error', 'You do not have permission to edit this listing.');
        header('Location: marketplace.php');
        exit;
    }
    
    $isEditMode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? 'other';
        $condition_status = $_POST['condition_status'] ?? 'good';
        
        // Validation
        if (empty($title)) throw new Exception('Item title is required.');
        if (strlen($title) > 200) throw new Exception('Title must not exceed 200 characters.');
        if (empty($description)) throw new Exception('Description is required.');
        if ($price <= 0) throw new Exception('Price must be greater than 0.');
        if ($price > 999999) throw new Exception('Price seems too high. Please verify.');
        
        if (!$isEditMode) {
            // CREATE mode
            // Image upload
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed. Please try again.');
                }
                if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                    throw new Exception('Image size must not exceed 3MB.');
                }
                $imagePath = handleFileUpload($_FILES['image'], 'marketplace');
                if (!$imagePath) {
                    throw new Exception('Invalid image file or unsupported format.');
                }
            }
            
            // Insert into database
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO marketplace_listings (seller_id, title, description, price, category, condition_status, image_path, is_approved, status) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'active')");
            $stmt->execute([getCurrentUserId(), $title, $description, $price, $category, $condition_status, $imagePath]);
            
            setFlashMessage('success', 'Listing created! It will be visible after admin approval.');
            header('Location: marketplace.php');
            exit;
        } else {
            // EDIT mode
            $listingId = (int)$_POST['listing_id'];
            $db = getDB();
            
            // Re-verify ownership
            $stmt = $db->prepare("SELECT * FROM marketplace_listings WHERE id = ?");
            $stmt->execute([$listingId]);
            $existingListing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingListing) {
                throw new Exception('Listing not found.');
            }
            
            if (!isAdmin() && $existingListing['seller_id'] !== getCurrentUserId()) {
                throw new Exception('You do not have permission to edit this listing.');
            }
            
            // Handle image upload (optional replace)
            $imagePath = $existingListing['image_path'];
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed. Please try again.');
                }
                if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                    throw new Exception('Image size must not exceed 3MB.');
                }
                
                // Delete old image if exists
                if ($existingListing['image_path'] && file_exists(__DIR__ . '/../' . $existingListing['image_path'])) {
                    unlink(__DIR__ . '/../' . $existingListing['image_path']);
                }
                
                $imagePath = handleFileUpload($_FILES['image'], 'marketplace');
                if (!$imagePath) {
                    throw new Exception('Invalid image file or unsupported format.');
                }
            }
            
            // Update database
            $stmt = $db->prepare("UPDATE marketplace_listings SET title = ?, description = ?, price = ?, category = ?, condition_status = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $price, $category, $condition_status, $imagePath, $listingId]);
            
            setFlashMessage('success', 'Listing updated successfully!');
            header('Location: listing_detail.php?id=' . $listingId);
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
        <h1><i class="fas fa-<?= $isEditMode ? 'edit' : 'shopping-bag' ?>" style="color:var(--clr-marketplace);"></i> <?= $isEditMode ? 'Edit Listing' : 'Sell an Item' ?></h1>
        <p><?= $isEditMode ? 'Update your listing' : 'Post a listing and find buyers from the campus' ?></p>
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
        <form method="POST" enctype="multipart/form-data" id="listingForm">
            <?php echo csrf_field(); ?>
            <?php if ($isEditMode): ?>
                <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Item Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Data Structures Textbook" value="<?= htmlspecialchars($listing['title'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (₹) <span class="required">*</span></label>
                    <input type="number" name="price" class="form-control" placeholder="350" min="0" step="1" value="<?= htmlspecialchars($listing['price'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="books" <?= ($listing['category'] ?? '') === 'books' ? 'selected' : '' ?>>Books</option>
                        <option value="electronics" <?= ($listing['category'] ?? '') === 'electronics' ? 'selected' : '' ?>>Electronics</option>
                        <option value="furniture" <?= ($listing['category'] ?? '') === 'furniture' ? 'selected' : '' ?>>Furniture</option>
                        <option value="clothing" <?= ($listing['category'] ?? '') === 'clothing' ? 'selected' : '' ?>>Clothing</option>
                        <option value="other" <?= ($listing['category'] ?? 'other') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Condition</label>
                <select name="condition_status" class="form-control">
                    <option value="new" <?= ($listing['condition_status'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="like_new" <?= ($listing['condition_status'] ?? '') === 'like_new' ? 'selected' : '' ?>>Like New</option>
                    <option value="good" <?= ($listing['condition_status'] ?? 'good') === 'good' ? 'selected' : '' ?>>Good</option>
                    <option value="fair" <?= ($listing['condition_status'] ?? '') === 'fair' ? 'selected' : '' ?>>Fair</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Description <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="4" placeholder="Describe the item..." required><?= htmlspecialchars($listing['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Photo</label>
                <?php if ($isEditMode && $listing['image_path']): ?>
                <div style="margin-bottom: var(--space-md); padding: var(--space-md); background: var(--bg-secondary); border-radius: 8px; text-align: center;">
                    <img src="<?= htmlspecialchars($listing['image_path']) ?>" alt="Listing image" style="max-height: 200px; border-radius: 4px;">
                    <p class="form-help" style="margin-top: var(--space-sm);">Current image (upload new to replace)</p>
                </div>
                <?php endif; ?>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-camera"></i><p>Add or update photo</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            <div style="display:flex;gap:var(--space-md);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-<?= $isEditMode ? 'save' : 'check' ?>"></i> <?= $isEditMode ? 'Save Changes' : 'Post Listing' ?></button>
                <a href="<?= $isEditMode ? 'listing_detail.php?id=' . $listing['id'] : 'marketplace.php' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{initImagePreview('image','imagePreview','uploadArea');});</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
