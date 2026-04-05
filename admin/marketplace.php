<?php
$pageTitle = 'Admin - Marketplace';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$db = getDB();
$listings = $db->query("SELECT l.*, u.name as seller_name FROM marketplace_listings l JOIN users u ON l.seller_id=u.id ORDER BY l.is_approved ASC, l.created_at DESC")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['listing_id']);
    if (isset($_POST['approve'])) { $db->prepare("UPDATE marketplace_listings SET is_approved=1 WHERE id=?")->execute([$id]); setFlashMessage('success','Listing approved!'); }
    if (isset($_POST['reject'])) { $db->prepare("UPDATE marketplace_listings SET status='removed' WHERE id=?")->execute([$id]); setFlashMessage('info','Listing removed.'); }
    header('Location: marketplace.php'); exit;
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header"><h1>🛒 Moderate Marketplace</h1></div>
    <div class="card"><div class="table-container"><table class="table">
        <thead><tr><th>Item</th><th>Price</th><th>Seller</th><th>Category</th><th>Status</th><th>Approved</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($listings as $l): ?>
        <tr>
            <td><strong><?= htmlspecialchars(truncateText($l['title'],30)) ?></strong></td>
            <td class="listing-price" style="font-size:var(--text-sm);"><?= number_format($l['price']) ?></td>
            <td style="font-size:var(--text-sm);"><?= htmlspecialchars($l['seller_name']) ?></td>
            <td><span class="tag"><?= ucfirst($l['category']) ?></span></td>
            <td><?= getStatusBadge($l['status']) ?></td>
            <td><?= $l['is_approved'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-warning">Pending</span>' ?></td>
            <td style="display:flex;gap:4px;">
                <?php if (!$l['is_approved'] && $l['status']==='active'): ?>
                <form method="POST"><input type="hidden" name="listing_id" value="<?= $l['id'] ?>"><button name="approve" class="btn btn-success btn-sm" style="padding:4px 8px;"><i class="fas fa-check"></i></button></form>
                <form method="POST"><input type="hidden" name="listing_id" value="<?= $l['id'] ?>"><button name="reject" class="btn btn-danger btn-sm" style="padding:4px 8px;"><i class="fas fa-times"></i></button></form>
                <?php else: ?>—<?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
