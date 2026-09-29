<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$message = getFlash();
$models = getEquipmentModelsSummary($conn);
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Equipment Management</title></head>
<body>
<div class="page">
<h1>Equipment Management</h1>
<p><a href="dashboard.php">Dashboard</a> | <a href="add_equipment.php">Add Equipment</a> | <a href="../logout.php">Logout</a></p>
<?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<table>
<tr><th>Photo</th><th>Name</th><th>Category</th><th>Value</th><th>Units</th></tr>
<?php foreach ($models as $m): ?>
<tr>
<td>
<?php if (!empty($m['image_path'])): ?>
    <img src="../<?= htmlspecialchars($m['image_path']) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="item-thumb">
<?php else: ?>
    <span class="image-placeholder">No image</span>
<?php endif; ?>
</td>
<td><?= htmlspecialchars($m['name']) ?></td>
<td><?= htmlspecialchars($m['category']) ?></td>
<td>Rs. <?= number_format($m['item_value'], 2) ?></td>
<td>
    <?= (int) $m['total_units'] ?> total
    (<?= (int) $m['available_units'] ?>/<?= (int) $m['reserved_units'] ?>/<?= (int) $m['borrowed_units'] ?>/<?= (int) $m['damaged_units'] ?>/<?= (int) $m['lost_units'] ?>)
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body>
</html>
