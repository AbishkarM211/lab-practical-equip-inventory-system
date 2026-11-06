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

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newImagePath = handleEquipmentImageUpload($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

        $result = updateEquipmentModel(
            $conn,
            $modelId,
            trim($_POST['name']),
            trim($_POST['description'] ?? ''),
            trim($_POST['category'] ?? ''),
            (float) $_POST['item_value'],
            $newImagePath
        );

        if ($result['success']) {
            setFlash('Equipment model updated.');
header('Location: equipment.php');
            exit;
        }
        $error = $result['message'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    $model = array_merge($model, $_POST);
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Edit Equipment</title></head>
<body>
    <h1>Edit Equipment Model</h1>
    <p><a href="equipment.php">Back to Equipment</a></p>

    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <?php if (!empty($model['image_path'])): ?>
        <p><img src="../<?= htmlspecialchars($model['image_path']) ?>" alt="" style="max-width:150px;"></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="model_id" value="<?= $modelId ?>">
        <label>Name: <input type="text" name="name" value="<?= htmlspecialchars($model['name']) ?>" required></label><br>
        <label>Description: <input type="text" name="description" value="<?= htmlspecialchars($model['description'] ?? '') ?>"></label><br>
        <label>Category: <input type="text" name="category" value="<?= htmlspecialchars($model['category'] ?? '') ?>"></label><br>
        <label>Replacement value (Rs): <input type="number" step="0.01" name="item_value" value="<?= htmlspecialchars($model['item_value']) ?>"></label><br>
        <label>Replace photo: <input type="file" name="image" accept="image/*"></label><br>
        <button type="submit">Save</button>
    </form>
</body>
</html>
