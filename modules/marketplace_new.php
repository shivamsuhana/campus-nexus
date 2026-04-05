<?php
$pageTitle = 'Sell Item';
$pageScripts = ['validation.js', 'upload.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();
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
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-shopping-bag" style="color:var(--clr-marketplace);"></i> Sell an Item</h1>
        <p>Post a listing and find buyers from the campus</p>
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
            <div class="form-group">
                <label class="form-label">Item Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Data Structures Textbook" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (₹) <span class="required">*</span></label>
                    <input type="number" name="price" class="form-control" placeholder="350" min="0" step="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="books">Books</option><option value="electronics">Electronics</option>
                        <option value="furniture">Furniture</option><option value="clothing">Clothing</option><option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Condition</label>
                <select name="condition_status" class="form-control">
                    <option value="new">New</option><option value="like_new">Like New</option>
                    <option value="good" selected>Good</option><option value="fair">Fair</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Description <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="4" placeholder="Describe the item..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Photo</label>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-camera"></i><p>Add a photo</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Post Listing</button>
        </form>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{initImagePreview('image','imagePreview','uploadArea');});</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
