
<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationId = (int) ($_POST['notification_id'] ?? 0);
    $isRead = (int) ($_POST['is_read'] ?? -1);

    if ($notificationId > 0 && ($isRead === 0 || $isRead === 1)) {
        updateNotificationStatus(
            $conn,
            $notificationId,
            $userId,
            $isRead
        );
    }

    header('Location: notifications.php');
    exit;
}

$notifications = getNotifications($conn, $userId);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Notifications</title>
</head>
<body>
    <h1>Notifications</h1>
    <p><a href="browse.php">Back to Browse</a></p>
    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($notifications as $n): ?>
                <li>
                    <?php if (!$n['is_read']): ?>
                        <strong>[new]</strong>
                    <?php endif; ?>
                    <?= htmlspecialchars($n['message']) ?>
                    <small>
                        (<?= htmlspecialchars($n['created_at']) ?>)
                    </small>

                    <form method="POST" action="notifications.php">
                        <input type="hidden" name="notification_id" value="<?= (int) $n['notification_id'] ?>">
                        <input type="hidden" name="is_read" value="<?= $n['is_read'] ? 0 : 1 ?>">
                        <button type="submit">
                            <?= $n['is_read'] ? 'Mark as Unread' : 'Mark as Read'?>
                        </button>
                    </form>
                </li>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
</body>
</html>
