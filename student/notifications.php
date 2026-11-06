<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

$notifications = getNotifications($conn, (int) $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Notifications</title></head>
<body>
    <h1>Notifications</h1>
    <p><a href="browse.php">Back to Browse</a></p>

    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($notifications as $n): ?>
                <li><?= $n['is_read'] ? '' : '<strong>[new]</strong> ' ?>
                    <?= htmlspecialchars($n['message']) ?>
                    <small>(<?= htmlspecialchars($n['created_at']) ?>)</small></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
