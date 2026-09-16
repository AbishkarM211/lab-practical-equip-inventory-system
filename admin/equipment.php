<?php
require '../config.php';
require '../includes/auth.php';
requireRole('admin');

$message = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM equipment WHERE equipment_id=$id");
    $message = 'Equipment deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $serial = mysqli_real_escape_string($conn, trim($_POST['serial_number'] ?? ''));
    $status = $_POST['status'] === 'unavailable' ? 'unavailable' : 'available';

    if ($name !== '' && $serial !== '') {
        if (!empty($_POST['equipment_id'])) {
            $id = (int) $_POST['equipment_id'];
            mysqli_query($conn, "UPDATE equipment SET name='$name', description='$description', serial_number='$serial', status='$status' WHERE equipment_id=$id");
            $message = 'Equipment updated.';
        } else {
            mysqli_query($conn, "INSERT INTO equipment (name, description, serial_number, status) VALUES ('$name', '$description', '$serial', '$status')");
            $message = 'Equipment added.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM equipment WHERE equipment_id=$id");
    $edit = mysqli_fetch_assoc($result);
}

$result = mysqli_query($conn, "SELECT * FROM equipment ORDER BY equipment_id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Equipment</title></head>
<body>
    <h1>Equipment Management</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="students.php">Students</a> | <a href="../logout.php">Logout</a></p>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <h2><?= $edit ? 'Edit Equipment' : 'Add Equipment' ?></h2>
    <form method="post">
        <?php if ($edit): ?>
            <input type="hidden" name="equipment_id" value="<?= $edit['equipment_id'] ?>">
        <?php endif; ?>
        <p>Name: <input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>"></p>
        <p>Description: <input type="text" name="description" value="<?= htmlspecialchars($edit['description'] ?? '') ?>"></p>
        <p>Serial number: <input type="text" name="serial_number" value="<?= htmlspecialchars($edit['serial_number'] ?? '') ?>"></p>
        <p>Status:
            <select name="status">
                <option value="available" <?= (($edit['status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
                <option value="unavailable" <?= (($edit['status'] ?? '') === 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </p>
        <button type="submit"><?= $edit ? 'Update' : 'Add' ?></button>
        <?php if ($edit): ?><a href="equipment.php">Cancel</a><?php endif; ?>
    </form>

    <h2>Equipment List</h2>
    <table border="1" cellpadding="5">
        <tr><th>ID</th><th>Name</th><th>Description</th><th>Serial Number</th><th>Status</th><th>Actions</th></tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['equipment_id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['serial_number']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <a href="equipment.php?edit=<?= $row['equipment_id'] ?>">Edit</a>
                    <a href="equipment.php?delete=<?= $row['equipment_id'] ?>" onclick="return confirm('Delete this equipment?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
