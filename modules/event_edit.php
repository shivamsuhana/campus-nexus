<?php
$pageTitle = 'Edit Event';
require_once __DIR__ . '/../includes/header.php';
requireFacultyOrAdmin();
initCSRFToken();

$event = null;
$isEditMode = false;

// Check if edit parameter exists
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$editId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        setFlashMessage('error', 'Event not found.');
        header('Location: events.php');
        exit;
    }
    
    // Verify ownership (user can edit their own or admin can edit any)
    if (!isAdmin() && $event['created_by'] !== getCurrentUserId()) {
        setFlashMessage('error', 'You do not have permission to edit this event.');
        header('Location: events.php');
        exit;
    }
    
    $isEditMode = true;
}

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
        
        // Verify event date is in the future (only for new events, not edits)
        if (!$isEditMode) {
            $eventDateTime = new DateTime($event_date);
            $now = new DateTime();
            if ($eventDateTime <= $now) {
                throw new Exception('Event date must be in the future.');
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
        } else {
            // EDIT mode
            $eventId = (int)$_POST['event_id'];
            $db = getDB();
            
            // Re-verify ownership
            $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $existingEvent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingEvent) {
                throw new Exception('Event not found.');
            }
            
            if (!isAdmin() && $existingEvent['created_by'] !== getCurrentUserId()) {
                throw new Exception('You do not have permission to edit this event.');
            }
            
            // Handle image upload (optional replace)
            $imagePath = $existingEvent['image_path'];
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('File upload failed. Please try again.');
                }
                if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                    throw new Exception('Image size must not exceed 3MB.');
                }
                
                // Delete old image if exists
                if ($existingEvent['image_path'] && file_exists(__DIR__ . '/../' . $existingEvent['image_path'])) {
                    unlink(__DIR__ . '/../' . $existingEvent['image_path']);
                }
                
                $imagePath = handleFileUpload($_FILES['image'], 'events');
                if (!$imagePath) {
                    throw new Exception('Invalid image file or unsupported format.');
                }
            }
            
            // Update database
            $stmt = $db->prepare("UPDATE events SET title = ?, description = ?, category = ?, venue = ?, event_date = ?, capacity = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $category, $venue, $event_date, $capacity, $imagePath, $eventId]);
            
            setFlashMessage('success', 'Event updated successfully!');
            header('Location: event_detail.php?id=' . $eventId);
            exit;
        }
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}

// Format event_date for datetime-local input
$dateValue = '';
if ($isEditMode && $event['event_date']) {
    $dateValue = (new DateTime($event['event_date']))->format('Y-m-d\TH:i');
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header">
        <h1><i class="fas fa-<?= $isEditMode ? 'edit' : 'calendar-plus' ?>" style="color:var(--clr-events);"></i> <?= $isEditMode ? 'Edit Event' : 'Create Event' ?></h1>
        <p><?= $isEditMode ? 'Update your event details' : 'Organize a campus event and invite students' ?></p>
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
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title" class="form-label">Event Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Tech Fest 2026" value="<?= htmlspecialchars($event['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="technical" <?= ($event['category'] ?? '') === 'technical' ? 'selected' : '' ?>>🔧 Technical</option>
                        <option value="cultural" <?= ($event['category'] ?? '') === 'cultural' ? 'selected' : '' ?>>🎭 Cultural</option>
                        <option value="sports" <?= ($event['category'] ?? '') === 'sports' ? 'selected' : '' ?>>⚽ Sports</option>
                        <option value="workshop" <?= ($event['category'] ?? 'workshop') === 'workshop' ? 'selected' : '' ?>>🛠️ Workshop</option>
                        <option value="seminar" <?= ($event['category'] ?? '') === 'seminar' ? 'selected' : '' ?>>📚 Seminar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="capacity" class="form-label">Capacity <span class="required">*</span></label>
                    <input type="number" id="capacity" name="capacity" class="form-control" value="<?= htmlspecialchars($event['capacity'] ?? '100') ?>" min="1" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venue" class="form-label">Venue <span class="required">*</span></label>
                    <input type="text" id="venue" name="venue" class="form-control" placeholder="e.g., Main Auditorium" value="<?= htmlspecialchars($event['venue'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="event_date" class="form-label">Date & Time <span class="required">*</span></label>
                    <input type="datetime-local" id="event_date" name="event_date" class="form-control" value="<?= $dateValue ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="required">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe the event..." required><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image" class="form-label">Banner Image</label>
                <?php if ($isEditMode && $event['image_path']): ?>
                <div style="margin-bottom: var(--space-md); padding: var(--space-md); background: var(--bg-secondary); border-radius: 8px; text-align: center;">
                    <img src="<?= htmlspecialchars($event['image_path']) ?>" alt="Event banner" style="max-height: 200px; border-radius: 4px;">
                    <p class="form-help" style="margin-top: var(--space-sm);">Current banner (upload new to replace)</p>
                </div>
                <?php endif; ?>
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                    <i class="fas fa-image"></i>
                    <p>Upload or update banner image</p>
                    <img id="imagePreview" class="file-preview" alt="">
                </div>
                <input type="file" id="image" name="image" accept="image/*" hidden>
            </div>
            
            <div style="display:flex;gap:var(--space-md);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-<?= $isEditMode ? 'save' : 'check' ?>"></i> <?= $isEditMode ? 'Save Changes' : 'Create Event' ?></button>
                <a href="<?= $isEditMode ? 'event_detail.php?id=' . $event['id'] : 'events.php' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script src="<?= SITE_URL ?>/js/upload.js"></script>
<script>document.addEventListener('DOMContentLoaded',()=>{initImagePreview('image','imagePreview','uploadArea');});</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
