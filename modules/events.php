<?php
$pageTitle = 'Events';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();
$events = $db->query("SELECT e.*, u.name as creator_name FROM events e JOIN users u ON e.created_by=u.id ORDER BY e.event_date ASC")->fetchAll();
$upcoming = array_filter($events, fn($e) => strtotime($e['event_date']) >= time());
$past = array_filter($events, fn($e) => strtotime($e['event_date']) < time());
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header-actions">
        <div>
            <div class="module-page-header">
                <div class="module-icon" style="background:rgba(254,225,64,0.12);color:var(--clr-events);"><i class="fas fa-calendar-alt"></i></div>
                <div><h1>Campus Events</h1><p>Discover & register for events</p></div>
            </div>
        </div>
        <?php if (isFacultyOrAdmin()): ?>
        <a href="event_new.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Event</a>
        <?php endif; ?>
    </div>

    <h3 style="margin-bottom:var(--space-lg);">Upcoming Events (<?= count($upcoming) ?>)</h3>
    <?php if (empty($upcoming)): ?>
    <div class="empty-state"><i class="fas fa-calendar-times"></i><h3>No upcoming events</h3></div>
    <?php else: ?>
    <div class="grid-auto">
        <?php foreach ($upcoming as $e): ?>
        <a href="event_detail.php?id=<?= $e['id'] ?>" class="card card-clickable" style="text-decoration:none;color:inherit;">
            <?php if ($e['image_path']): ?><img src="<?= SITE_URL ?>/<?= htmlspecialchars($e['image_path']) ?>" alt="" class="card-image"><?php endif; ?>
            <div style="display:flex;gap:var(--space-md);align-items:flex-start;">
                <div class="event-date-badge">
                    <div class="day"><?= date('d', strtotime($e['event_date'])) ?></div>
                    <div class="month"><?= date('M', strtotime($e['event_date'])) ?></div>
                </div>
                <div>
                    <h4 class="card-title"><?= htmlspecialchars($e['title']) ?></h4>
                    <p style="font-size:var(--text-xs);color:var(--text-muted);margin:4px 0;"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($e['venue']) ?></p>
                    <span class="badge badge-primary"><?= ucfirst($e['category']) ?></span>
                </div>
            </div>
            <div class="card-footer" style="margin-top:var(--space-md);">
                <div class="event-capacity" style="flex:1;">
                    <span style="font-size:var(--text-xs);"><?= $e['registered_count'] ?>/<?= $e['capacity'] ?></span>
                    <div class="event-capacity-bar"><div class="event-capacity-fill" style="width:<?= min(100, ($e['registered_count']/$e['capacity'])*100) ?>%;"></div></div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($past)): ?>
    <h3 style="margin:var(--space-2xl) 0 var(--space-lg);">Past Events</h3>
    <div class="grid-auto">
        <?php foreach (array_slice($past,0,6) as $e): ?>
        <div class="card" style="opacity:0.6;">
            <div style="display:flex;gap:var(--space-md);align-items:center;">
                <div class="event-date-badge" style="background:var(--bg-glass);color:var(--text-muted);width:50px;padding:6px;">
                    <div class="day" style="font-size:16px;"><?= date('d', strtotime($e['event_date'])) ?></div>
                    <div class="month" style="font-size:9px;"><?= date('M', strtotime($e['event_date'])) ?></div>
                </div>
                <div>
                    <h4 style="font-size:var(--text-sm);"><?= htmlspecialchars(truncateText($e['title'],35)) ?></h4>
                    <span class="badge badge-muted">Completed</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
