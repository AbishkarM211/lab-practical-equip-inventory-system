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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = deletePhysicalItem($conn, $itemId);
    $param = $result['success'] ? 'message' : 'error';
    $msg   = $result['success'] ? 'Unit deleted.' : $result['message'];
    header('Location: equipment_units.php?model_id=' . (int) $item['model_id'] . '&' . $param . '=' . urlencode($msg));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Delete Unit</title></head>
<body>
    <h1>Delete unit "<?= htmlspecialchars($item['serial_number']) ?>"?</h1>
    <form method="post">
        <input type="hidden" name="item_id" value="<?= $itemId ?>">
        <button type="submit">Yes, delete this unit</button>
    </form>
    <p><a href="equipment_units.php?model_id=<?= (int) $item['model_id'] ?>">Never mind, go back</a></p>
</body>
</html>
