<?php
$pageTitle = 'Event Detail';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
initCSRFToken();
$db = getDB();
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT e.*, u.name as creator_name FROM events e JOIN users u ON e.created_by=u.id WHERE e.id=?");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) { header('Location: events.php'); exit; }

// Check registration
$isRegistered = false;
$regCheck = $db->prepare("SELECT id FROM event_registrations WHERE event_id=? AND user_id=?");
$regCheck->execute([$id, getCurrentUserId()]);
$isRegistered = $regCheck->fetch() !== false;

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    verify_csrf_token();
    if (!$isRegistered && $event['registered_count'] < $event['capacity']) {
        $db->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?,?)")->execute([$id, getCurrentUserId()]);
        $db->prepare("UPDATE events SET registered_count = registered_count + 1 WHERE id=?")->execute([$id]);
        setFlashMessage('success','Registered successfully!');
    } elseif ($isRegistered) {
        $db->prepare("DELETE FROM event_registrations WHERE event_id=? AND user_id=?")->execute([$id, getCurrentUserId()]);
        $db->prepare("UPDATE events SET registered_count = GREATEST(registered_count - 1, 0) WHERE id=?")->execute([$id]);
        setFlashMessage('info','Registration cancelled.');
    }
    header("Location: event_detail.php?id=$id"); exit;
}

$attendees = $db->prepare("SELECT u.name, u.avatar FROM event_registrations er JOIN users u ON er.user_id=u.id WHERE er.event_id=? LIMIT 10");
$attendees->execute([$id]);
$attendees = $attendees->fetchAll();
$pct = $event['capacity'] > 0 ? min(100,($event['registered_count']/$event['capacity'])*100) : 0;
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <a href="events.php" class="btn btn-ghost btn-sm" style="margin-bottom:var(--space-md);"><i class="fas fa-arrow-left"></i> Back</a>
    <div style="display:grid;grid-template-columns:1fr 320px;gap:var(--space-xl);">
        <div>
            <?php if ($event['image_path']): ?><img src="<?= SITE_URL ?>/<?= htmlspecialchars($event['image_path']) ?>" alt="" class="detail-image"><?php endif; ?>
            <div class="detail-meta" style="margin-bottom:var(--space-md);">
                <span class="badge badge-primary"><?= ucfirst($event['category']) ?></span>
                <span class="tag"><i class="fas fa-calendar"></i> <?= formatDateTime($event['event_date']) ?></span>
                <span class="tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['venue']) ?></span>
            </div>
            <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-lg);"><?= htmlspecialchars($event['title']) ?></h1>
            <div class="detail-content"><p><?= nl2br(htmlspecialchars($event['description'])) ?></p></div>
        </div>
        <div>
            <div class="card detail-sidebar">
                <h4 style="margin-bottom:var(--space-md);">Registration</h4>
                <div class="event-capacity" style="margin-bottom:var(--space-md);">
                    <span style="font-size:var(--text-sm);font-weight:600;"><?= $event['registered_count'] ?> / <?= $event['capacity'] ?></span>
                    <div class="event-capacity-bar" style="height:8px;"><div class="event-capacity-fill" style="width:<?= $pct ?>%;"></div></div>
                </div>
                <?php if (strtotime($event['event_date']) >= time()): ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="register" value="1">
                    <button type="submit" class="btn <?= $isRegistered ? 'btn-danger' : 'btn-primary' ?> btn-block">
                        <i class="fas fa-<?= $isRegistered ? 'times' : 'check' ?>"></i>
                        <?= $isRegistered ? 'Cancel Registration' : 'Register Now' ?>
                    </button>
                </form>
                <?php else: ?>
                <span class="badge badge-muted">Event has ended</span>
                <?php endif; ?>

                <?php if (!empty($attendees)): ?>
                <h4 style="margin:var(--space-lg) 0 var(--space-sm);">Attendees</h4>
                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                    <?php foreach ($attendees as $a): ?>
                    <img src="<?= getAvatarUrl($a['avatar'], $a['name']) ?>" alt="<?= htmlspecialchars($a['name']) ?>" title="<?= htmlspecialchars($a['name']) ?>" style="width:32px;height:32px;border-radius:50%;border:2px solid var(--bg-card-solid);">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div style="margin-top:var(--space-lg);padding-top:var(--space-md);border-top:1px solid var(--border-glass);">
                    <span class="text-xs text-muted">Organized by</span>
                    <p style="font-size:var(--text-sm);font-weight:500;margin-top:4px;"><?= htmlspecialchars($event['creator_name']) ?></p>
                </div>
                
                <?php if (getCurrentUserId() === $event['created_by'] || isAdmin()): ?>
                <a href="event_edit.php?edit=<?= $id ?>" class="btn btn-secondary btn-block btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-edit"></i> Edit Event</a>
                <form method="POST" action="../delete.php" style="margin-top:var(--space-md);" onsubmit="return confirm('Delete this event? This cannot be undone.');">
                    <input type="hidden" name="type" value="event">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="redirect" value="<?= SITE_URL ?>/modules/events.php">
                    <button type="submit" class="btn btn-danger btn-block btn-sm"><i class="fas fa-trash"></i> Delete Event</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
