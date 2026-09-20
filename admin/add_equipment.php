<?php
require '../config.php';
require '../includes/auth.php';
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $serial = trim($_POST['serial_number'] ?? '');

    if ($name === '' || $serial === '') {
        $error = 'Name and serial number are required.';
    } else {
        $name = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $category = mysqli_real_escape_string($conn, $category);
        $serial = mysqli_real_escape_string($conn, $serial);

        if (mysqli_query($conn, "INSERT INTO equipment_models (name, description, category) VALUES ('$name', '$description', '$category')")) {
            $modelId = mysqli_insert_id($conn);
            if (mysqli_query($conn, "INSERT INTO physical_items (model_id, serial_number) VALUES ($modelId, '$serial')")) {
                $success = 'Equipment added.';
            } else {
                mysqli_query($conn, "DELETE FROM equipment_models WHERE model_id = $modelId");
                $error = 'Could not add the serial number. It may already exist.';
            }
        } else {
            $error = 'Could not add equipment.';
        }
    }
}
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
<form method="post">
<p>Name: <input type="text" name="name"></p>
<p>Description: <input type="text" name="description"></p>
<p>Category: <input type="text" name="category"></p>
<p>Serial number: <input type="text" name="serial_number"></p>
<button type="submit">Add Equipment</button>
</form>
</div>
</body>
</html>
