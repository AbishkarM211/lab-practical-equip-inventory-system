<?php
require '../config.php';
require '../includes/auth.php';
requireRole('student');

$result = mysqli_query($conn, "SELECT * FROM equipment WHERE quantity > 0 ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head><title>Available Equipment</title></head>
<body>
    <h1>Available Equipment</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>.</p>
    <p><a href="../logout.php">Logout</a></p>

    <table border="1" cellpadding="5">
        <tr><th>Name</th><th>Description</th><th>Quantity</th></tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['quantity'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
