<?php
/**
 * AJAX: Toggle user active status
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isAdmin()) { echo json_encode(['success'=>false]); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$userId = intval($input['user_id'] ?? 0);
if (!$userId) { echo json_encode(['success'=>false]); exit; }
$db = getDB();
$db->prepare("UPDATE users SET is_active = NOT is_active WHERE id=? AND id != ?")->execute([$userId, getCurrentUserId()]);
$status = $db->prepare("SELECT is_active FROM users WHERE id=?");
$status->execute([$userId]);
echo json_encode(['success'=>true, 'is_active'=>(bool)$status->fetchColumn()]);
?>
