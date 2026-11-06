<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$modelId = (int) ($_GET['model_id'] ?? 0);
$model = getEquipmentModel($conn, $modelId);

if (!$model) {
    header('Location: equipment.php?error=' . urlencode('Equipment model not found.'));
    exit;
}

$units = getPhysicalItemsForModel($conn, $modelId);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Units — <?= htmlspecialchars($model['name']) ?></title></head>
<body>
    <h1>Units — <?= htmlspecialchars($model['name']) ?></h1>
    <p><a href="equipment.php">Back to Equipment</a> |
    <a href="add_equipment.php">Add a Unit</a></p>

    <?php if (!empty($_GET['message'])): ?>
        <p style="color:green;"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="6">
        <tr><th>Serial #</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($units as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['serial_number']) ?></td>
                <td><?= htmlspecialchars($u['status']) ?></td>
                <td>
                    <a href="unit_history.php?item_id=<?= (int) $u['item_id'] ?>">History</a>
                    <?php if (in_array($u['status'], ['reserved', 'borrowed'], true)): ?>
                        | <em>Currently <?= htmlspecialchars($u['status']) ?> — manage via Dashboard</em>
                    <?php else: ?>
                        | <a href="edit_unit.php?item_id=<?= (int) $u['item_id'] ?>">Edit</a>
                        | <a href="delete_unit.php?item_id=<?= (int) $u['item_id'] ?>" onclick="return confirm('Delete this unit?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
