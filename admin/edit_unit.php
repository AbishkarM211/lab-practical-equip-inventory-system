<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$itemId = (int) ($_GET['item_id'] ?? $_POST['item_id'] ?? 0);
$item = getPhysicalItem($conn, $itemId);

if (!$item) {
    header('Location: equipment.php?error=' . urlencode('Unit not found.'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updatePhysicalItem($conn, $itemId, trim($_POST['serial_number']), $_POST['status']);

    if ($result['success']) {
        setFlash('Unit updated.');
        header('Location: equipment_units.php?model_id=' . (int) $item['model_id']);
        exit;
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Edit Unit</title></head>
<body>
    <h1>Edit Unit</h1>
    <p><a href="equipment_units.php?model_id=<?= (int) $item['model_id'] ?>">Back to Units</a></p>

    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="item_id" value="<?= $itemId ?>">
        <label>Serial number: <input type="text" name="serial_number" value="<?= htmlspecialchars($item['serial_number']) ?>" required></label><br>
        <label>Status:
            <select name="status">
                <option value="available" <?= $item['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="damaged" <?= $item['status'] === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                <option value="lost" <?= $item['status'] === 'lost' ? 'selected' : '' ?>>Lost</option>
            </select>
        </label><br>
        <p style="color:#888;">(Reserved/borrowed are managed automatically through the borrow flow, not here.)</p>
        <button type="submit">Save</button>
    </form>
</body>
</html>
