<?php
$pageTitle = 'Create Event';
require_once __DIR__ . '/../includes/header.php';
requireFacultyOrAdmin();
initCSRFToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'workshop';
        $venue = sanitize($_POST['venue'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $capacity = intval($_POST['capacity'] ?? 50);
        
        // Validation
        if (empty($title)) throw new Exception('Event title is required.');
        if (strlen($title) > 200) throw new Exception('Title must not exceed 200 characters.');
        if (empty($description)) throw new Exception('Description is required.');
        if (empty($venue)) throw new Exception('Venue is required.');
        if (empty($event_date)) throw new Exception('Event date is required.');
        if ($capacity < 1) throw new Exception('Capacity must be at least 1.');
        
        // Verify event date is in the future
        $eventDateTime = new DateTime($event_date);
        $now = new DateTime();
        if ($eventDateTime <= $now) {
            throw new Exception('Event date must be in the future.');
        }
        
        // Image upload
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed. Please try again.');
            }
            if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                throw new Exception('Image size must not exceed 3MB.');
            }
            $imagePath = handleFileUpload($_FILES['image'], 'events');
            if (!$imagePath) {
                throw new Exception('Invalid image file or unsupported format.');
            }
        }
        
        // Insert into database
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO events (created_by, title, description, category, venue, event_date, capacity, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([getCurrentUserId(), $title, $description, $category, $venue, $event_date, $capacity, $imagePath]);
        
        setFlashMessage('success', 'Event created successfully!');
        header('Location: events.php');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-calendar-plus" style="color:var(--clr-events);"></i> Create Event</h1>
        <p>Organize a campus event and invite students</p>
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
            <div class="form-group">
                <label for="title" class="form-label">Event Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Tech Fest 2026" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="technical">🔧 Technical</option>
                        <option value="cultural">🎭 Cultural</option>
                        <option value="sports">⚽ Sports</option>
                        <option value="workshop">🛠️ Workshop</option>
                        <option value="seminar">📚 Seminar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="capacity" class="form-label">Capacity <span class="required">*</span></label>
                    <input type="number" id="capacity" name="capacity" class="form-control" value="100" min="1" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venue" class="form-label">Venue <span class="required">*</span></label>
                    <input type="text" id="venue" name="venue" class="form-control" placeholder="e.g., Main Auditorium" required>
                </div>
                <div class="form-group">
                    <label for="event_date" class="form-label">Date & Time <span class="required">*</span></label>
                    <input type="datetime-local" id="event_date" name="event_date" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="required">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe the event..." required></textarea>
            </div>
            
            <div class="form-group">
                <label for="image" class="form-label">Banner Image</label>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-image"></i>
                    <p>Upload banner image</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Create Event</button>
        </form>
    </div>
</div>
<script src="<?= SITE_URL ?>/js/upload.js"></script>
<script>document.addEventListener('DOMContentLoaded',()=>{initImagePreview('image','imagePreview','uploadArea');});</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
