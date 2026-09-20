<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$message = getFlash();
$models = getEquipmentModels($conn);
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Equipment</title></head>
<body>
<div class="page">
<h1>Equipment Management</h1>
<p><a href="dashboard.php">Dashboard</a> | <a href="add_equipment.php">Add Equipment</a> | <a href="../logout.php">Logout</a></p>
<?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<table>
<tr><th>Name</th><th>Category</th><th>Description</th><th>Units</th></tr>
<?php foreach ($models as $m): ?>
<?php $units = getPhysicalItemsForModel($conn, $m['model_id']); ?>
<tr>
<td><?= htmlspecialchars($m['name']) ?></td>
<td><?= htmlspecialchars($m['category']) ?></td>
<td><?= htmlspecialchars($m['description']) ?></td>
<td>
<?php foreach ($units as $u): ?>
    <?= htmlspecialchars($u['serial_number']) ?> (<?= htmlspecialchars($u['status']) ?>)<br>
<?php endforeach; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body>
</html>
