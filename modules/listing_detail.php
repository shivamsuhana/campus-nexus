<?php
$pageTitle = 'Listing Detail';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT l.*, u.name as seller_name, u.email as seller_email, u.avatar, u.department FROM marketplace_listings l JOIN users u ON l.seller_id=u.id WHERE l.id=?");
$stmt->execute([$id]);
$listing = $stmt->fetch();
if (!$listing) { setFlashMessage('error','Listing not found.'); header('Location: marketplace.php'); exit; }
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <a href="marketplace.php" class="btn btn-ghost btn-sm" style="margin-bottom:var(--space-md);"><i class="fas fa-arrow-left"></i> Back</a>
    <div style="display:grid;grid-template-columns:1fr 320px;gap:var(--space-xl);">
        <div>
            <?php if ($listing['image_path']): ?>
            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($listing['image_path']) ?>" alt="" class="detail-image">
            <?php endif; ?>
            <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-md);"><?= htmlspecialchars($listing['title']) ?></h1>
            <p class="listing-price" style="font-size:var(--text-3xl);margin-bottom:var(--space-lg);"><?= number_format($listing['price']) ?></p>
            <div class="detail-meta" style="margin-bottom:var(--space-lg);">
                <?= getStatusBadge($listing['status']) ?>
                <span class="tag"><i class="fas fa-<?= getCategoryIcon($listing['category']) ?>"></i> <?= ucfirst($listing['category']) ?></span>
                <span class="tag"><i class="fas fa-star"></i> <?= ucfirst(str_replace('_',' ',$listing['condition_status'])) ?></span>
            </div>
            <div class="detail-content"><p><?= nl2br(htmlspecialchars($listing['description'])) ?></p></div>
        </div>
        <div>
            <div class="card detail-sidebar">
                <h4 style="margin-bottom:var(--space-lg);">Seller Info</h4>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:var(--space-lg);">
                    <img src="<?= getAvatarUrl($listing['avatar'], $listing['seller_name']) ?>" alt="" style="width:48px;height:48px;border-radius:50%;">
                    <div>
                        <p style="font-weight:600;"><?= htmlspecialchars($listing['seller_name']) ?></p>
                        <p style="font-size:var(--text-xs);color:var(--text-muted);"><?= htmlspecialchars($listing['department']) ?></p>
                    </div>
                </div>
                <a href="mailto:<?= htmlspecialchars($listing['seller_email']) ?>" class="btn btn-primary btn-block"><i class="fas fa-envelope"></i> Contact Seller</a>
                <?php if (getCurrentUserId() === $listing['seller_id'] || isAdmin()): ?>
                <a href="marketplace_edit.php?edit=<?= $id ?>" class="btn btn-secondary btn-block btn-sm" style="margin-top:var(--space-sm);"><i class="fas fa-edit"></i> Edit Listing</a>
                <form method="POST" action="../delete.php" style="margin-top:var(--space-sm);" onsubmit="return confirm('Delete this listing? This cannot be undone.');">
                    <input type="hidden" name="type" value="listing">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="redirect" value="<?= SITE_URL ?>/modules/marketplace.php">
                    <button type="submit" class="btn btn-danger btn-block btn-sm"><i class="fas fa-trash"></i> Delete Listing</button>
                </form>
                <?php endif; ?>
                <p style="font-size:var(--text-xs);color:var(--text-muted);margin-top:var(--space-md);text-align:center;">Posted <?= timeAgo($listing['created_at']) ?></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
