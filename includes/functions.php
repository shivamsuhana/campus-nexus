<?php
/**
 * CampusNexus — Utility Functions
 * Common helpers used across the application
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a random token
 */
function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate attendance session code (6 digits)
 */
function generateSessionCode() {
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Format datetime to relative time (e.g., "2 hours ago")
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Format date nicely
 */
function formatDate($datetime, $format = 'M d, Y') {
    return date($format, strtotime($datetime));
}

/**
 * Format date with time
 */
function formatDateTime($datetime) {
    return date('M d, Y \a\t h:i A', strtotime($datetime));
}

/**
 * Handle file upload
 * Returns file path on success, false on failure
 */
function handleFileUpload($file, $directory, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Check file type
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($file['tmp_name']);
    } else {
        $mimeType = $file['type'] ?? '';
    }
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    // Create directory if not exists
    $uploadPath = UPLOAD_DIR . $directory;
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('cn_', true) . '.' . $extension;
    $fullPath = $uploadPath . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return 'uploads/' . $directory . '/' . $filename;
    }
    
    return false;
}

/**
 * Handle document upload (PDF, DOC, PPT, etc.)
 */
function handleDocumentUpload($file, $directory) {
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg', 'image/png', 'image/gif'
    ];
    return handleFileUpload($file, $directory, $allowedTypes);
}

/**
 * Get user avatar URL or default
 */
function getAvatarUrl($avatar, $name = '') {
    if ($avatar && file_exists(__DIR__ . '/../' . $avatar)) {
        return SITE_URL . '/' . $avatar;
    }
    // Generate initial-based avatar
    $initial = strtoupper(substr($name ?: 'U', 0, 1));
    $colors = ['667EEA', '764BA2', '43E97B', 'F5576C', 'FA709A', '4FACFE', 'F7971E', '30CFD0'];
    $colorIndex = ord($initial) % count($colors);
    $color = $colors[$colorIndex];
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background={$color}&color=fff&size=128&bold=true";
}

/**
 * Get pagination data
 */
function getPagination($totalItems, $currentPage, $perPage = 12) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $totalItems,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

/**
 * Render pagination HTML
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($pagination['has_prev']) {
        $prev = $pagination['current_page'] - 1;
        $html .= "<a href=\"{$baseUrl}?page={$prev}\" class=\"pagination-btn\"><i class=\"fas fa-chevron-left\"></i></a>";
    }
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    if ($start > 1) {
        $html .= "<a href=\"{$baseUrl}?page=1\" class=\"pagination-btn\">1</a>";
        if ($start > 2) $html .= '<span class="pagination-dots">...</span>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $pagination['current_page']) ? 'active' : '';
        $html .= "<a href=\"{$baseUrl}?page={$i}\" class=\"pagination-btn {$active}\">{$i}</a>";
    }
    
    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) $html .= '<span class="pagination-dots">...</span>';
        $html .= "<a href=\"{$baseUrl}?page={$pagination['total_pages']}\" class=\"pagination-btn\">{$pagination['total_pages']}</a>";
    }
    
    // Next button
    if ($pagination['has_next']) {
        $next = $pagination['current_page'] + 1;
        $html .= "<a href=\"{$baseUrl}?page={$next}\" class=\"pagination-btn\"><i class=\"fas fa-chevron-right\"></i></a>";
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'open' => '<span class="badge badge-danger">Open</span>',
        'in_progress' => '<span class="badge badge-warning">In Progress</span>',
        'resolved' => '<span class="badge badge-success">Resolved</span>',
        'closed' => '<span class="badge badge-muted">Closed</span>',
        'active' => '<span class="badge badge-success">Active</span>',
        'sold' => '<span class="badge badge-muted">Sold</span>',
        'removed' => '<span class="badge badge-danger">Removed</span>',
        'claimed' => '<span class="badge badge-warning">Claimed</span>',
        'returned' => '<span class="badge badge-success">Returned</span>',
        'lost' => '<span class="badge badge-danger">Lost</span>',
        'found' => '<span class="badge badge-success">Found</span>',
    ];
    return $badges[$status] ?? '<span class="badge">' . ucfirst($status) . '</span>';
}

/**
 * Get priority badge HTML
 */
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge badge-info">Low</span>',
        'medium' => '<span class="badge badge-warning">Medium</span>',
        'high' => '<span class="badge badge-danger">High</span>',
        'critical' => '<span class="badge badge-critical">Critical</span>',
    ];
    return $badges[$priority] ?? '<span class="badge">' . ucfirst($priority) . '</span>';
}

/**
 * Get category icon
 */
function getCategoryIcon($category) {
    $icons = [
        'infrastructure' => 'fa-building',
        'it' => 'fa-wifi',
        'hygiene' => 'fa-broom',
        'safety' => 'fa-shield-alt',
        'electrical' => 'fa-bolt',
        'academic' => 'fa-graduation-cap',
        'other' => 'fa-ellipsis-h',
        'books' => 'fa-book',
        'electronics' => 'fa-laptop',
        'furniture' => 'fa-chair',
        'clothing' => 'fa-tshirt',
        'documents' => 'fa-id-card',
        'accessories' => 'fa-gem',
        'technical' => 'fa-code',
        'cultural' => 'fa-music',
        'sports' => 'fa-futbol',
        'workshop' => 'fa-tools',
        'seminar' => 'fa-chalkboard-teacher',
        'notes' => 'fa-sticky-note',
        'slides' => 'fa-file-powerpoint',
        'paper' => 'fa-file-alt',
        'assignment' => 'fa-tasks',
    ];
    return $icons[$category] ?? 'fa-tag';
}

/**
 * Get module color
 */
function getModuleColor($module) {
    $colors = [
        'attendance' => '#4FACFE',
        'resources' => '#43E97B',
        'grievances' => '#F5576C',
        'marketplace' => '#FA709A',
        'events' => '#FEE140',
        'lost_found' => '#A18CD1',
        'mess' => '#F7971E',
        'announcements' => '#30CFD0',
    ];
    return $colors[$module] ?? '#667EEA';
}

/**
 * Truncate text to a given length
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get dashboard stats for different roles
 */
function getDashboardStats($role, $userId = null) {
    $db = getDB();
    $stats = [];
    
    if ($role === 'admin') {
        $stats['total_users'] = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
        $stats['open_issues'] = $db->query("SELECT COUNT(*) FROM grievances WHERE status = 'open' OR status = 'in_progress'")->fetchColumn();
        $stats['active_events'] = $db->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();
        $stats['pending_listings'] = $db->query("SELECT COUNT(*) FROM marketplace_listings WHERE is_approved = 0 AND status = 'active'")->fetchColumn();
        $stats['total_resources'] = $db->query("SELECT COUNT(*) FROM resources")->fetchColumn();
        $stats['total_grievances'] = $db->query("SELECT COUNT(*) FROM grievances")->fetchColumn();
        $stats['resolved_grievances'] = $db->query("SELECT COUNT(*) FROM grievances WHERE status = 'resolved' OR status = 'closed'")->fetchColumn();
        $stats['unread_messages'] = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
    } elseif ($role === 'faculty') {
        $stmt = $db->prepare("SELECT COUNT(*) FROM resources WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        $stats['my_resources'] = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE created_by = ?");
        $stmt->execute([$userId]);
        $stats['my_events'] = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE faculty_id = ?");
        $stmt->execute([$userId]);
        $stats['my_sessions'] = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM announcements WHERE posted_by = ?");
        $stmt->execute([$userId]);
        $stats['my_announcements'] = $stmt->fetchColumn();
    } else {
        // Student
        $stmt = $db->prepare("SELECT COUNT(*) FROM grievances WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['my_issues'] = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM marketplace_listings WHERE seller_id = ?");
        $stmt->execute([$userId]);
        $stats['my_listings'] = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['my_events'] = $stmt->fetchColumn();
        
        // Attendance percentage
        $stmt = $db->prepare("
            SELECT 
                COUNT(ar.id) as attended,
                (SELECT COUNT(*) FROM attendance_sessions WHERE is_active = 0) as total_sessions
            FROM attendance_records ar 
            WHERE ar.student_id = ?
        ");
        $stmt->execute([$userId]);
        $att = $stmt->fetch();
        $stats['attendance'] = $att['total_sessions'] > 0 
            ? round(($att['attended'] / $att['total_sessions']) * 100) 
            : 100;
    }
    
    return $stats;
}

/**
 * Get the current page name for active nav highlighting
 */
function getCurrentPage() {
    $page = basename($_SERVER['PHP_SELF'], '.php');
    return $page;
}

/**
 * Check if current page matches for nav active state
 */
function isCurrentPage($page) {
    return getCurrentPage() === $page ? 'active' : '';
}

// ---------- SECURITY HARDENING ----------

/**
 * Initialize CSRF token in session
 */
function initCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF token field for forms
 */
function csrf_field() {
    $token = initCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token from POST request
 */
function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (empty($token) || !hash_equals($sessionToken, $token)) {
            setFlashMessage('error', 'Invalid security token. Request blocked for safety.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . '/dashboard.php'));
            exit;
        }
    }
}

/**
 * Check rate limit for an action
 * Returns true if action is allowed, false if rate limit exceeded
 */
function check_rate_limit($action, $limit = 5, $timeframe = 60) {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    if (!isset($_SESSION['rate_limits'][$action])) {
        $_SESSION['rate_limits'][$action] = [];
    }
    
    // Clean up old requests outside the timeframe
    $now = time();
    $_SESSION['rate_limits'][$action] = array_filter(
        $_SESSION['rate_limits'][$action],
        function($timestamp) use ($now, $timeframe) {
            return ($now - $timestamp) < $timeframe;
        }
    );
    
    // Check if limit exceeded
    if (count($_SESSION['rate_limits'][$action]) >= $limit) {
        return false;
    }
    
    // Log this attempt
    $_SESSION['rate_limits'][$action][] = $now;
    return true;
}

/**
 * Get remaining rate limit attempts
 */
function get_rate_limit_remaining($action, $limit = 5) {
    if (!isset($_SESSION['rate_limits'][$action])) {
        return $limit;
    }
    return max(0, $limit - count($_SESSION['rate_limits'][$action]));
}

?>
