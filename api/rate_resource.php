<?php
/**
 * AJAX: Rate a resource
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$resourceId = intval($input['resource_id'] ?? 0);
$rating = intval($input['rating'] ?? 0);

if (!$resourceId || $rating < 1 || $rating > 5) { echo json_encode(['success'=>false,'message'=>'Invalid data']); exit; }

$db = getDB();
$userId = getCurrentUserId();

// Upsert rating
$exists = $db->prepare("SELECT id FROM resource_ratings WHERE resource_id=? AND user_id=?");
$exists->execute([$resourceId, $userId]);

if ($exists->fetch()) {
    $db->prepare("UPDATE resource_ratings SET rating=? WHERE resource_id=? AND user_id=?")->execute([$rating, $resourceId, $userId]);
} else {
    $db->prepare("INSERT INTO resource_ratings (resource_id, user_id, rating) VALUES (?,?,?)")->execute([$resourceId, $userId, $rating]);
}

// Update avg
$avg = $db->prepare("SELECT ROUND(AVG(rating),2) as avg_r, COUNT(*) as cnt FROM resource_ratings WHERE resource_id=?");
$avg->execute([$resourceId]);
$result = $avg->fetch();
$db->prepare("UPDATE resources SET avg_rating=?, rating_count=? WHERE id=?")->execute([$result['avg_r'], $result['cnt'], $resourceId]);

echo json_encode(['success'=>true, 'avg_rating'=>$result['avg_r'], 'count'=>$result['cnt']]);
?>
