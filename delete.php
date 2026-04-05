<?php
/**
 * CampusNexus — Universal Delete Handler
 * Handles deletion of grievances, events, marketplace listings, resources, and lost_found items
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
verify_csrf_token();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}

try {
    $type = sanitize($_POST['type'] ?? '');
    $id = intval($_POST['id'] ?? 0);
    $redirect = sanitize($_POST['redirect'] ?? SITE_URL . '/dashboard.php');
    
    if (!$type || !$id) {
        throw new Exception('Invalid parameters.');
    }
    
    $userId = getCurrentUserId();
    $isAdmin = isAdmin();
    $db = getDB();
    
    // ==========================================
    // GRIEVANCE DELETION
    // ==========================================
    if ($type === 'grievance') {
        $stmt = $db->prepare("SELECT user_id, image_path FROM grievances WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception('Grievance not found.');
        }
        
        if ($item['user_id'] !== $userId && !$isAdmin) {
            throw new Exception('You do not have permission to delete this grievance.');
        }
        
        // Delete image if exists
        if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
            @unlink(__DIR__ . '/' . $item['image_path']);
        }
        
        // Delete associated records
        $db->prepare("DELETE FROM grievance_comments WHERE grievance_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM grievance_upvotes WHERE grievance_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM grievances WHERE id = ?")->execute([$id]);
        
        setFlashMessage('success', 'Grievance deleted successfully.');
        header('Location: ' . $redirect);
        exit;
    }
    
    // ==========================================
    // EVENT DELETION
    // ==========================================
    if ($type === 'event') {
        $stmt = $db->prepare("SELECT created_by, image_path FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception('Event not found.');
        }
        
        if ($item['created_by'] !== $userId && !$isAdmin) {
            throw new Exception('You do not have permission to delete this event.');
        }
        
        // Delete image if exists
        if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
            @unlink(__DIR__ . '/' . $item['image_path']);
        }
        
        // Delete associated records
        $db->prepare("DELETE FROM event_registrations WHERE event_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
        
        setFlashMessage('success', 'Event deleted successfully.');
        header('Location: ' . $redirect);
        exit;
    }
    
    // ==========================================
    // MARKETPLACE LISTING DELETION
    // ==========================================
    if ($type === 'listing') {
        $stmt = $db->prepare("SELECT seller_id, image_path FROM marketplace_listings WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception('Listing not found.');
        }
        
        if ($item['seller_id'] !== $userId && !$isAdmin) {
            throw new Exception('You do not have permission to delete this listing.');
        }
        
        // Delete image if exists
        if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
            @unlink(__DIR__ . '/' . $item['image_path']);
        }
        
        $db->prepare("DELETE FROM marketplace_listings WHERE id = ?")->execute([$id]);
        
        setFlashMessage('success', 'Listing deleted successfully.');
        header('Location: ' . $redirect);
        exit;
    }
    
    // ==========================================
    // RESOURCE DELETION
    // ==========================================
    if ($type === 'resource') {
        $stmt = $db->prepare("SELECT uploaded_by, file_path FROM resources WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception('Resource not found.');
        }
        
        if ($item['uploaded_by'] !== $userId && !$isAdmin) {
            throw new Exception('You do not have permission to delete this resource.');
        }
        
        // Delete file if exists
        if ($item['file_path'] && file_exists(__DIR__ . '/' . $item['file_path'])) {
            @unlink(__DIR__ . '/' . $item['file_path']);
        }
        
        // Delete associated ratings
        $db->prepare("DELETE FROM resource_ratings WHERE resource_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM resources WHERE id = ?")->execute([$id]);
        
        setFlashMessage('success', 'Resource deleted successfully.');
        header('Location: ' . $redirect);
        exit;
    }
    
    // ==========================================
    // LOST & FOUND ITEM DELETION
    // ==========================================
    if ($type === 'lost_found') {
        $stmt = $db->prepare("SELECT user_id, image_path FROM lost_found WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception('Item not found.');
        }
        
        if ($item['user_id'] !== $userId && !$isAdmin) {
            throw new Exception('You do not have permission to delete this item.');
        }
        
        // Delete image if exists
        if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
            @unlink(__DIR__ . '/' . $item['image_path']);
        }
        
        $db->prepare("DELETE FROM lost_found WHERE id = ?")->execute([$id]);
        
        setFlashMessage('success', 'Item deleted successfully.');
        header('Location: ' . $redirect);
        exit;
    }
    
    throw new Exception('Invalid deletion type.');
    
} catch (Exception $e) {
    setFlashMessage('error', $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . '/dashboard.php'));
    exit;
}
?>
