<?php
/**
 * AJAX: Register/unregister for event
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$eventId = intval($input['event_id'] ?? 0);
if (!$eventId) { echo json_encode(['success'=>false]); exit; }
$db = getDB();
$userId = getCurrentUserId();
$check = $db->prepare("SELECT id FROM event_registrations WHERE event_id=? AND user_id=?");
$check->execute([$eventId, $userId]);
if ($check->fetch()) {
    $db->prepare("DELETE FROM event_registrations WHERE event_id=? AND user_id=?")->execute([$eventId, $userId]);
    $db->prepare("UPDATE events SET registered_count = GREATEST(registered_count-1,0) WHERE id=?")->execute([$eventId]);
    echo json_encode(['success'=>true,'action'=>'unregistered']);
} else {
    $db->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?,?)")->execute([$eventId, $userId]);
    $db->prepare("UPDATE events SET registered_count=registered_count+1 WHERE id=?")->execute([$eventId]);
    echo json_encode(['success'=>true,'action'=>'registered']);
}
?>
