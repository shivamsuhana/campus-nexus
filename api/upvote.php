<?php
/**
 * AJAX: Upvote a grievance
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$grievanceId = intval($input['grievance_id'] ?? 0);
if (!$grievanceId) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

$db = getDB();
$userId = getCurrentUserId();

// Check existing
$check = $db->prepare("SELECT id FROM grievance_upvotes WHERE grievance_id=? AND user_id=?");
$check->execute([$grievanceId, $userId]);

if ($check->fetch()) {
    // Remove upvote
    $db->prepare("DELETE FROM grievance_upvotes WHERE grievance_id=? AND user_id=?")->execute([$grievanceId, $userId]);
    $db->prepare("UPDATE grievances SET upvotes = GREATEST(upvotes - 1, 0) WHERE id=?")->execute([$grievanceId]);
} else {
    // Add upvote
    $db->prepare("INSERT INTO grievance_upvotes (grievance_id, user_id) VALUES (?,?)")->execute([$grievanceId, $userId]);
    $db->prepare("UPDATE grievances SET upvotes = upvotes + 1 WHERE id=?")->execute([$grievanceId]);
}

$count = $db->prepare("SELECT upvotes FROM grievances WHERE id=?");
$count->execute([$grievanceId]);
echo json_encode(['success'=>true, 'count'=>$count->fetchColumn()]);
?>
