<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$modelId = (int) ($_GET['model_id'] ?? $_POST['model_id'] ?? 0);
$model = getEquipmentModel($conn, $modelId);

if (!$model) {
    header('Location: equipment.php?error=' . urlencode('Equipment model not found.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = deleteEquipmentModel($conn, $modelId);
    $param = $result['success'] ? 'message' : 'error';
    $msg   = $result['success'] ? 'Equipment model deleted.' : $result['message'];
    header('Location: equipment.php?' . $param . '=' . urlencode($msg));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Delete Equipment</title></head>
<body>
    <h1>Delete "<?= htmlspecialchars($model['name']) ?>"?</h1>
    <p>This only works if no physical units of this model exist — remove them first via "Manage Units" if needed.</p>
    <form method="post">
        <input type="hidden" name="model_id" value="<?= $modelId ?>">
        <button type="submit">Yes, delete this model</button>
    </form>
    <p><a href="equipment.php">Never mind, go back</a></p>
</body>
</html>
