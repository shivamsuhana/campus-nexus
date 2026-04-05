<?php
$pageTitle = 'Marketplace';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$where = "WHERE l.status = 'active' AND l.is_approved = 1";
$params = [];
if ($category) { $where .= " AND l.category = ?"; $params[] = $category; }
if ($search) { $where .= " AND (l.title LIKE ? OR l.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $db->prepare("SELECT l.*, u.name as seller_name, u.avatar FROM marketplace_listings l JOIN users u ON l.seller_id=u.id $where ORDER BY l.created_at DESC");
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="page-content with-sidebar">
    <div class="page-header-actions">
        <div>
            <div class="module-page-header">
                <div class="module-icon" style="background:rgba(250,112,154,0.12);color:var(--clr-marketplace);"><i class="fas fa-shopping-bag"></i></div>
                <div><h1>Campus Marketplace</h1><p>Buy & sell used items within campus</p></div>
            </div>
        </div>
        <a href="marketplace_new.php" class="btn btn-primary"><i class="fas fa-plus"></i> Sell Item</a>
    </div>

    <div class="filter-bar">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key==='Enter'){let u='marketplace.php?search='+this.value;window.location=u;}">
        </div>
        <select onchange="window.location='marketplace.php?category='+this.value">
            <option value="">All Categories</option>
            <option value="books" <?= $category==='books'?'selected':'' ?>>📚 Books</option>
            <option value="electronics" <?= $category==='electronics'?'selected':'' ?>>💻 Electronics</option>
            <option value="furniture" <?= $category==='furniture'?'selected':'' ?>>🪑 Furniture</option>
            <option value="clothing" <?= $category==='clothing'?'selected':'' ?>>👕 Clothing</option>
            <option value="other" <?= $category==='other'?'selected':'' ?>>📦 Other</option>
        </select>
    </div>

    <?php if (empty($listings)): ?>
    <div class="empty-state"><i class="fas fa-store-slash"></i><h3>No listings found</h3><p>Be the first to list an item!</p><a href="marketplace_new.php" class="btn btn-primary">Sell Item</a></div>
    <?php else: ?>
    <div class="grid-auto">
        <?php foreach ($listings as $l): ?>
        <a href="listing_detail.php?id=<?= $l['id'] ?>" class="card card-clickable" style="text-decoration:none;color:inherit;">
            <?php if ($l['image_path']): ?>
            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($l['image_path']) ?>" alt="" class="card-image">
            <?php else: ?>
            <div class="card-image" style="background:var(--bg-glass);display:flex;align-items:center;justify-content:center;"><i class="fas fa-<?= getCategoryIcon($l['category']) ?>" style="font-size:48px;color:var(--text-muted);"></i></div>
            <?php endif; ?>
            <h4 class="card-title"><?= htmlspecialchars(truncateText($l['title'], 40)) ?></h4>
            <p class="listing-price"><?= number_format($l['price']) ?></p>
            <div class="card-footer">
                <span class="tag"><i class="fas fa-<?= getCategoryIcon($l['category']) ?>"></i> <?= ucfirst($l['category']) ?></span>
                <span class="listing-condition"><i class="fas fa-star"></i> <?= ucfirst(str_replace('_',' ',$l['condition_status'])) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
