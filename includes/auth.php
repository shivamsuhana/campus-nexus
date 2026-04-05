<?php
/**
 * CampusNexus — Authentication Helpers
 * Session management, auth guards, role checks
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user name
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Get current user data array
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'department' => $_SESSION['user_department'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? null,
    ];
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

/**
 * Check if current user is faculty
 */
function isFaculty() {
    return getCurrentUserRole() === 'faculty';
}

/**
 * Check if current user is student
 */
function isStudent() {
    return getCurrentUserRole() === 'student';
}

/**
 * Check if current user is faculty or admin
 */
function isFacultyOrAdmin() {
    return isFaculty() || isAdmin();
}

/**
 * Require login — redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page.');
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Require faculty or admin role
 */
function requireFacultyOrAdmin() {
    requireLogin();
    if (!isFacultyOrAdmin()) {
        setFlashMessage('error', 'Access denied. Faculty or admin privileges required.');
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Require student role
 */
function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        setFlashMessage('error', 'Access denied. Student privileges required.');
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Require faculty role
 */
function requireFaculty() {
    requireLogin();
    if (!isFaculty()) {
        setFlashMessage('error', 'Access denied. Faculty privileges required.');
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Set user session data after login
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_department'] = $user['department'];
    $_SESSION['user_avatar'] = $user['avatar'];
}

/**
 * Destroy user session (logout)
 */
function destroyUserSession() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

/**
 * Check remember me cookie and auto-login
 */
function checkRememberMe() {
    if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
        require_once __DIR__ . '/../config/database.php';
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = ? AND is_active = 1");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        
        if ($user) {
            setUserSession($user);
            // Update last login
            $update = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update->execute([$user['id']]);
        } else {
            // Invalid token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
}

// Auto-check remember me on every page load
checkRememberMe();
?>
