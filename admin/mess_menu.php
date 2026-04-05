<?php
$pageTitle = 'Admin - Mess Menu';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();
$db = getDB();
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$meals = ['breakfast','lunch','snacks','dinner'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_day']) && isset($_POST['delete_meal'])) {
        // Delete menu item
        $db->prepare("DELETE FROM mess_menu WHERE day=? AND meal=?")
            ->execute([$_POST['delete_day'], $_POST['delete_meal']]);
        setFlashMessage('success','Menu item deleted!'); header('Location: mess_menu.php'); exit;
    }
    
    $day = $_POST['day']; $meal = $_POST['meal']; $items = sanitize($_POST['items']);
    if (!empty($items)) { // Only update if items is not empty
        $exists = $db->prepare("SELECT id FROM mess_menu WHERE day=? AND meal=?");
        $exists->execute([$day, $meal]);
        if ($exists->fetch()) {
            $db->prepare("UPDATE mess_menu SET items=?, admin_id=? WHERE day=? AND meal=?")->execute([$items, getCurrentUserId(), $day, $meal]);
        } else {
            $db->prepare("INSERT INTO mess_menu (day,meal,items,admin_id) VALUES (?,?,?,?)")->execute([$day, $meal, $items, getCurrentUserId()]);
        }
        setFlashMessage('success','Menu updated!'); header('Location: mess_menu.php'); exit;
    }
}
$menu = $db->query("SELECT * FROM mess_menu ORDER BY FIELD(day,'monday','tuesday','wednesday','thursday','friday','saturday','sunday'), FIELD(meal,'breakfast','lunch','snacks','dinner')")->fetchAll();
$menuMap = [];
foreach ($menu as $m) { $menuMap[$m['day']][$m['meal']] = $m['items']; }
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="page-header"><h1>🍽️ Manage Mess Menu</h1></div>
    <div class="card" style="max-width:800px;">
        <form method="POST">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Day *</label>
                    <select name="day" class="form-control"><?php foreach ($days as $d): ?><option value="<?= $d ?>"><?= ucfirst($d) ?></option><?php endforeach; ?></select>
                </div>
                <div class="form-group"><label class="form-label">Meal *</label>
                    <select name="meal" class="form-control"><?php foreach ($meals as $m): ?><option value="<?= $m ?>"><?= ucfirst($m) ?></option><?php endforeach; ?></select>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Menu Items</label>
                <textarea name="items" class="form-control" rows="3" placeholder="e.g., Rice, Dal, Paneer, Roti, Salad"></textarea>
                <p class="form-help">Leave empty and save to clear the menu for this day/meal</p>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Menu</button>
        </form>
    </div>
    
    <!-- Current Menu -->
    <h3 style="margin:var(--space-xl) 0 var(--space-lg);">Current Menu Overview</h3>
    <div class="card"><div class="table-container"><table class="table">
        <thead><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Snacks</th><th>Dinner</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($days as $d): ?>
        <tr>
            <td><strong><?= ucfirst($d) ?></strong></td>
            <?php foreach ($meals as $m): ?>
            <td style="font-size:var(--text-xs);"><?= htmlspecialchars(truncateText($menuMap[$d][$m] ?? '—', 40)) ?></td>
            <?php endforeach; ?>
            <td style="display:flex;gap:4px;flex-wrap:wrap;">
                <?php foreach ($meals as $m): ?>
                    <?php if (!empty($menuMap[$d][$m])): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Clear <?= ucfirst($m) ?> for <?= ucfirst($d) ?>?');">
                        <input type="hidden" name="delete_day" value="<?= $d ?>">
                        <input type="hidden" name="delete_meal" value="<?= $m ?>">
                        <button type="submit" class="btn btn-ghost btn-sm text-danger" title="Clear <?= ucfirst($m) ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                <?php endforeach; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
