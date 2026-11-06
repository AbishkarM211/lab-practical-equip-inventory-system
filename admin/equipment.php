<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$models = getEquipmentModelsSummary($conn);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Manage Equipment</title></head>
<body>
    <h1>Equipment Models</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="add_equipment.php">Add Equipment</a> | <a href="../logout.php">Log out</a></p>

    <?php if (!empty($_GET['message'])): ?>
        <p style="color:green;"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="6">
        <tr>
            <th>Photo</th><th>Name</th><th>Category</th><th>Value</th>
            <th>Units (avail/reserved/borrowed/damaged/lost)</th><th>Actions</th>
        </tr>
        <?php foreach ($models as $m): ?>
            <tr>
                <td>
                    <?php if (!empty($m['image_path'])): ?>
                        <img src="../<?= htmlspecialchars($m['image_path']) ?>" alt="" style="max-width:60px;max-height:60px;">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['category']) ?></td>
                <td>Rs. <?= number_format($m['item_value'], 2) ?></td>
                <td>
                    <?= (int) $m['total_units'] ?> total
                    (<?= (int) $m['available_units'] ?>/<?= (int) $m['reserved_units'] ?>/<?= (int) $m['borrowed_units'] ?>/<?= (int) $m['damaged_units'] ?>/<?= (int) $m['lost_units'] ?>)
                </td>
                <td>
                    <a href="equipment_units.php?model_id=<?= (int) $m['model_id'] ?>">Manage Units</a> |
                    <a href="edit_equipment.php?model_id=<?= (int) $m['model_id'] ?>">Edit</a> |
                    <a href="delete_equipment.php?model_id=<?= (int) $m['model_id'] ?>" onclick="return confirm('Delete this equipment model?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
