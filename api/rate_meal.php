<?php
/**
 * AJAX: Rate a meal
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false]); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$meal = $input['meal'] ?? '';
$rating = intval($input['rating'] ?? 0);
if (!in_array($meal, ['breakfast','lunch','snacks','dinner']) || $rating < 1 || $rating > 5) { echo json_encode(['success'=>false]); exit; }
$db = getDB();
$userId = getCurrentUserId();
$exists = $db->prepare("SELECT id FROM mess_ratings WHERE user_id=? AND meal=? AND rating_date=CURDATE()");
$exists->execute([$userId, $meal]);
if ($exists->fetch()) {
    $db->prepare("UPDATE mess_ratings SET rating=? WHERE user_id=? AND meal=? AND rating_date=CURDATE()")->execute([$rating, $userId, $meal]);
} else {
    $db->prepare("INSERT INTO mess_ratings (user_id,meal,rating_date,rating) VALUES (?,?,CURDATE(),?)")->execute([$userId, $meal, $rating]);
}
echo json_encode(['success'=>true]);
?>
