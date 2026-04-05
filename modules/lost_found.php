<?php
$pageTitle = 'Lost & Found';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();

$lostItems = $db->query("SELECT lf.*, u.name as user_name, u.avatar FROM lost_found lf JOIN users u ON lf.user_id=u.id WHERE lf.type='lost' ORDER BY lf.created_at DESC")->fetchAll();
$foundItems = $db->query("SELECT lf.*, u.name as user_name, u.avatar FROM lost_found lf JOIN users u ON lf.user_id=u.id WHERE lf.type='found' ORDER BY lf.created_at DESC")->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header-actions">
        <div>
            <div class="module-page-header">
                <div class="module-icon" style="background:rgba(161,140,209,0.12);color:var(--clr-lost-found);"><i class="fas fa-search-location"></i></div>
                <div><h1>Lost & Found</h1><p>Report lost items or help return found ones</p></div>
            </div>
        </div>
        <a href="lost_found_new.php" class="btn btn-primary"><i class="fas fa-plus"></i> Report Item</a>
    </div>

    <div class="lf-split">
        <!-- Lost Column -->
        <div>
            <div class="lf-column-header lost">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Lost Items (<?= count($lostItems) ?>)</h3>
            </div>
            <?php if (empty($lostItems)): ?>
            <div class="empty-state" style="padding:var(--space-xl);"><p>No lost items reported</p></div>
            <?php else: ?>
            <?php foreach ($lostItems as $item): ?>
            <div class="card" style="margin-bottom:var(--space-md);">
                <?php if ($item['image_path']): ?>
                <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['image_path']) ?>" alt="" class="card-image" style="height:150px;">
                <?php endif; ?>
                <div class="card-header">
                    <h4 class="card-title" style="font-size:var(--text-base);"><?= htmlspecialchars($item['title']) ?></h4>
                    <?= getStatusBadge($item['status']) ?>
                </div>
                <p style="font-size:var(--text-sm);color:var(--text-secondary);margin:var(--space-sm) 0;"><?= htmlspecialchars(truncateText($item['description'], 80)) ?></p>
                <div class="grievance-meta">
                    <span><i class="fas fa-<?= getCategoryIcon($item['category']) ?>"></i> <?= ucfirst($item['category']) ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(truncateText($item['location'], 25)) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= formatDate($item['item_date']) ?></span>
                </div>
                <div class="card-footer">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <img src="<?= getAvatarUrl($item['avatar'], $item['user_name']) ?>" alt="" style="width:22px;height:22px;border-radius:50%;">
                        <span style="font-size:var(--text-xs);"><?= htmlspecialchars($item['user_name']) ?></span>
                    </div>
                    <span style="font-size:var(--text-xs);color:var(--text-muted);"><?= timeAgo($item['created_at']) ?></span>
                </div>
                <?php if (getCurrentUserId() === $item['user_id'] || isAdmin()): ?>
                <a href="lost_found_edit.php?edit=<?= $item['id'] ?>" class="btn btn-secondary btn-sm btn-block" style="margin-bottom:var(--space-sm);"><i class="fas fa-edit"></i> Edit</a>
                <form method="POST" action="../delete.php" style="margin-top:var(--space-sm);" onsubmit="return confirm('Delete this item? This cannot be undone.');">
                    <input type="hidden" name="type" value="lost_found">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= SITE_URL ?>/modules/lost_found.php">
                    <button type="submit" class="btn btn-danger btn-sm btn-block"><i class="fas fa-trash"></i> Delete</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Found Column -->
        <div>
            <div class="lf-column-header found">
                <i class="fas fa-check-circle"></i>
                <h3>Found Items (<?= count($foundItems) ?>)</h3>
            </div>
            <?php if (empty($foundItems)): ?>
            <div class="empty-state" style="padding:var(--space-xl);"><p>No found items reported</p></div>
            <?php else: ?>
            <?php foreach ($foundItems as $item): ?>
            <div class="card" style="margin-bottom:var(--space-md);">
                <?php if ($item['image_path']): ?>
                <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['image_path']) ?>" alt="" class="card-image" style="height:150px;">
                <?php endif; ?>
                <div class="card-header">
                    <h4 class="card-title" style="font-size:var(--text-base);"><?= htmlspecialchars($item['title']) ?></h4>
                    <?= getStatusBadge($item['status']) ?>
                </div>
                <p style="font-size:var(--text-sm);color:var(--text-secondary);margin:var(--space-sm) 0;"><?= htmlspecialchars(truncateText($item['description'], 80)) ?></p>
                <div class="grievance-meta">
                    <span><i class="fas fa-<?= getCategoryIcon($item['category']) ?>"></i> <?= ucfirst($item['category']) ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(truncateText($item['location'], 25)) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= formatDate($item['item_date']) ?></span>
                </div>
                <div class="card-footer">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <img src="<?= getAvatarUrl($item['avatar'], $item['user_name']) ?>" alt="" style="width:22px;height:22px;border-radius:50%;">
                        <span style="font-size:var(--text-xs);"><?= htmlspecialchars($item['user_name']) ?></span>
                    </div>
                    <span style="font-size:var(--text-xs);color:var(--text-muted);"><?= timeAgo($item['created_at']) ?></span>
                </div>
                <?php if (getCurrentUserId() === $item['user_id'] || isAdmin()): ?>
                <a href="lost_found_edit.php?edit=<?= $item['id'] ?>" class="btn btn-secondary btn-sm btn-block" style="margin-bottom:var(--space-sm);"><i class="fas fa-edit"></i> Edit</a>
                <form method="POST" action="../delete.php" style="margin-top:var(--space-sm);" onsubmit="return confirm('Delete this item? This cannot be undone.');">
                    <input type="hidden" name="type" value="lost_found">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= SITE_URL ?>/modules/lost_found.php">
                    <button type="submit" class="btn btn-danger btn-sm btn-block"><i class="fas fa-trash"></i> Delete</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
