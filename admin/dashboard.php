<?php
require '../config.php';
require '../includes/auth.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title></head>
<body>
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>.</p>

    <p><a href="equipment.php">Manage Equipment</a></p>
    <p><a href="../logout.php">Logout</a></p>
</body>
</html>
