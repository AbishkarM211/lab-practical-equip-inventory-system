<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelChoice = $_POST['model_choice'] ?? 'existing';
    $modelId = 0;
    $serialNumber = trim($_POST['serial_number'] ?? '');

    if ($serialNumber === '') {
        $error = 'Serial number is required.';
    }

    if ($error === '' && $modelChoice === 'new') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $itemValue = (float) ($_POST['item_value'] ?? 0);
        $imagePath = handleEquipmentImageUpload($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

        if ($name === '') {
            $error = 'Name is required.';
        } else {
            $safeName = mysqli_real_escape_string($conn, $name);
            $safeDescription = mysqli_real_escape_string($conn, $description);
            $safeCategory = mysqli_real_escape_string($conn, $category);
            $safeImagePath = $imagePath ? "'" . mysqli_real_escape_string($conn, $imagePath) . "'" : 'NULL';

            if (mysqli_query($conn, "INSERT INTO equipment_models (name, description, category, item_value, image_path) VALUES ('$safeName', '$safeDescription', '$safeCategory', $itemValue, $safeImagePath)")) {
                $modelId = mysqli_insert_id($conn);
            } else {
                $error = 'Could not create the equipment model.';
            }
        }
    } else {
        $modelId = (int) ($_POST['existing_model_id'] ?? 0);
        if ($modelId <= 0) {
            $error = 'Please select an equipment model.';
        }
    }

    if ($error === '') {
        $safeSerial = mysqli_real_escape_string($conn, $serialNumber);
        if (mysqli_query($conn, "INSERT INTO physical_items (model_id, serial_number, status) VALUES ($modelId, '$safeSerial', 'available')")) {
            $success = 'Equipment added.';
        } else {
            if ($modelChoice === 'new' && $modelId > 0) {
                mysqli_query($conn, "DELETE FROM equipment_models WHERE model_id = $modelId");
            }
            $error = 'Could not add the serial number. It may already exist.';
        }
    }
}

$models = mysqli_fetch_all(mysqli_query($conn, "SELECT model_id, name FROM equipment_models ORDER BY name"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Add Equipment</title></head>
<body>
<div class="page">
<h1>Add Equipment</h1>
<p><a href="equipment.php">Back to Equipment</a> | <a href="dashboard.php">Dashboard</a></p>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<p>
    <label><input type="radio" name="model_choice" value="existing" checked> Add a unit of an existing model:</label>
    <select name="existing_model_id">
        <?php foreach ($models as $m): ?>
            <option value="<?= (int) $m['model_id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
        <?php endforeach; ?>
    </select>
</p>
<p>
    <label><input type="radio" name="model_choice" value="new"> Create a new model:</label><br>
    Name: <input type="text" name="name"><br>
    Description: <input type="text" name="description"><br>
    Category: <input type="text" name="category"><br>
    Replacement value (Rs): <input type="number" step="0.01" name="item_value" value="0.00"><br>
    Photo (optional, new models only): <input type="file" name="image" accept="image/*">
</p>
<p>Serial number for this physical unit: <input type="text" name="serial_number"></p>
<button type="submit">Add Equipment</button>
</form>
</div>
</body>
</html>
