<?php
$pageTitle = 'Mess Menu & Feedback';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();
$db = getDB();

$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$currentDay = strtolower(date('l'));
$selectedDay = $_GET['day'] ?? $currentDay;
$mealIcons = ['breakfast'=>'☀️','lunch'=>'🌤️','snacks'=>'🍿','dinner'=>'🌙'];

// Get menu for selected day
$menu = $db->prepare("SELECT * FROM mess_menu WHERE day = ? ORDER BY FIELD(meal,'breakfast','lunch','snacks','dinner')");
$menu->execute([$selectedDay]);
$menu = $menu->fetchAll();

// Get today's ratings by current user
$myRatings = [];
$stmt = $db->prepare("SELECT meal, rating FROM mess_ratings WHERE user_id = ? AND rating_date = CURDATE()");
$stmt->execute([getCurrentUserId()]);
foreach ($stmt->fetchAll() as $r) { $myRatings[$r['meal']] = $r['rating']; }

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_meal'])) {
    verify_csrf_token();
    $meal = $_POST['meal'];
    $rating = intval($_POST['rating']);
    $feedback = sanitize($_POST['feedback'] ?? '');
    
    // Upsert
    $exists = $db->prepare("SELECT id FROM mess_ratings WHERE user_id=? AND meal=? AND rating_date=CURDATE()");
    $exists->execute([getCurrentUserId(), $meal]);
    if ($exists->fetch()) {
        $db->prepare("UPDATE mess_ratings SET rating=?, feedback=? WHERE user_id=? AND meal=? AND rating_date=CURDATE()")->execute([$rating, $feedback, getCurrentUserId(), $meal]);
    } else {
        $db->prepare("INSERT INTO mess_ratings (user_id, meal, rating_date, rating, feedback) VALUES (?,?,CURDATE(),?,?)")->execute([getCurrentUserId(), $meal, $rating, $feedback]);
    }
    setFlashMessage('success', ucfirst($meal) . ' rated!');
    header("Location: mess.php?day=$selectedDay"); exit;
}

// Average ratings for today
$avgRatings = [];
$stmt = $db->query("SELECT meal, ROUND(AVG(rating),1) as avg_r, COUNT(*) as cnt FROM mess_ratings WHERE rating_date = CURDATE() GROUP BY meal");
foreach ($stmt->fetchAll() as $r) { $avgRatings[$r['meal']] = $r; }
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="module-page-header">
        <div class="module-icon" style="background:rgba(247,151,30,0.12);color:var(--clr-mess);"><i class="fas fa-utensils"></i></div>
        <div><h1>Mess Menu & Feedback</h1><p>View daily menu and rate your meals</p></div>
    </div>

    <!-- Day Tabs -->
    <div class="mess-day-tabs">
        <?php foreach ($days as $i => $d): ?>
        <a href="mess.php?day=<?= $d ?>" class="mess-day-tab <?= $selectedDay === $d ? 'active' : '' ?>" style="text-decoration:none;">
            <?= substr($dayNames[$i], 0, 3) ?>
            <?php if ($d === $currentDay): ?><span style="font-size:8px;display:block;">Today</span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <h2 style="margin-bottom:var(--space-lg);"><?= ucfirst($selectedDay) ?>'s Menu</h2>

    <?php if (empty($menu)): ?>
    <div class="empty-state"><i class="fas fa-utensils"></i><h3>No menu available</h3><p><?= isAdmin() ? 'Add menu items from the admin panel.' : 'Menu not yet updated for this day.' ?></p></div>
    <?php else: ?>
    <?php foreach ($menu as $m): ?>
    <div class="meal-card">
        <div class="meal-card-header">
            <div class="meal-type">
                <span style="font-size:24px;"><?= $mealIcons[$m['meal']] ?? '🍽️' ?></span>
                <span><?= ucfirst($m['meal']) ?></span>
            </div>
            <?php if (isset($avgRatings[$m['meal']])): ?>
            <div class="meal-rating">
                <div class="star-display">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= round($avgRatings[$m['meal']]['avg_r']) ? 'filled' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span style="font-size:var(--text-sm);font-weight:600;"><?= $avgRatings[$m['meal']]['avg_r'] ?></span>
                <span style="font-size:var(--text-xs);color:var(--text-muted);">(<?= $avgRatings[$m['meal']]['cnt'] ?>)</span>
            </div>
            <?php endif; ?>
        </div>
        <div class="meal-items"><?= htmlspecialchars($m['items']) ?></div>
        
        <!-- Rate this meal -->
        <?php if ($selectedDay === $currentDay): ?>
        <div style="margin-top:var(--space-md);padding-top:var(--space-md);border-top:1px solid var(--border-glass);">
            <form method="POST" style="display:flex;gap:var(--space-md);align-items:flex-end;flex-wrap:wrap;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="rate_meal" value="1">
                <input type="hidden" name="meal" value="<?= $m['meal'] ?>">
                <div style="flex:0 0 auto;">
                    <label class="form-label" style="font-size:var(--text-xs);">Your Rating</label>
                    <select name="rating" class="form-control" style="width:auto;padding:8px 12px;">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= ($myRatings[$m['meal']] ?? 0) == $i ? 'selected' : '' ?>><?= str_repeat('⭐', $i) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="flex:1;min-width:200px;">
                    <input type="text" name="feedback" class="form-control" placeholder="Quick feedback (optional)" style="padding:8px 12px;">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-star"></i> Rate</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
