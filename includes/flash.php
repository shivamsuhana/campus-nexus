<?php
/**
 * CampusNexus — Flash Message Handler
 * Session-based success/error/warning/info messages
 */

/**
 * Set a flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message HTML
 */
function renderFlashMessage() {
    $flash = getFlashMessage();
    if (!$flash) return '';
    
    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    ];
    
    $icon = $icons[$flash['type']] ?? 'fa-info-circle';
    $type = htmlspecialchars($flash['type']);
    $message = htmlspecialchars($flash['message']);
    
    return <<<HTML
    <div class="flash-message flash-{$type}" id="flashMessage">
        <div class="flash-content">
            <i class="fas {$icon}"></i>
            <span>{$message}</span>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    HTML;
}
?>
