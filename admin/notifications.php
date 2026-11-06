<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$borrows = getActiveBorrowsForNotify($conn);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Send Notifications</title></head>
<body>
    <h1>Send a Notification</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="../logout.php">Log out</a></p>
    <p>Only students who currently have an item borrowed are listed here.</p>

    <?php if (!empty($_GET['message'])): ?>
        <p style="color:green;"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($borrows)): ?>
        <p>No active borrows right now.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <tr><th>Student</th><th>Item</th><th>Serial #</th><th>Due</th><th>Message</th></tr>
            <?php foreach ($borrows as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['full_name']) ?></td>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td><?= htmlspecialchars($b['serial_number']) ?></td>
                    <td><?= htmlspecialchars($b['due_date']) ?></td>
                    <td>
                        <form method="post" action="send_notification.php">
                            <input type="hidden" name="user_id" value="<?= (int) $b['user_id'] ?>">
                            <input type="hidden" name="borrow_id" value="<?= (int) $b['borrow_id'] ?>">
                            <input type="text" name="message" size="35" required
                                placeholder="e.g. Please return the <?= htmlspecialchars($b['name']) ?> by Friday">
                            <button type="submit">Send</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
