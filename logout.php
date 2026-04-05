<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Destroy the current session completely
destroyUserSession();

// Redirect to login with a query parameter for the success message
// (Session-based flash is unreliable after session_destroy)
header('Location: ' . SITE_URL . '/login.php?logout=1');
exit;
?>
