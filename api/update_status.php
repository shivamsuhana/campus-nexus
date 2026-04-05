<?php
/**
 * AJAX: Update grievance/listing status (admin only)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isAdmin()) { echo json_encode(['success'=>false,'message'=>'Admin only']); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? '';
$id = intval($input['id'] ?? 0);
$status = $input['status'] ?? '';
if (!$id || !$status) { echo json_encode(['success'=>false]); exit; }
$db = getDB();
if ($type === 'grievance') {
    $resolved = $status === 'resolved' ? ', resolved_at=NOW()' : '';
    $db->prepare("UPDATE grievances SET status=? $resolved WHERE id=?")->execute([$status, $id]);
} elseif ($type === 'listing') {
    $db->prepare("UPDATE marketplace_listings SET status=? WHERE id=?")->execute([$status, $id]);
}
echo json_encode(['success'=>true]);
?>
