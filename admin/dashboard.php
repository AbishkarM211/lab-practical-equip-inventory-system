<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

lazyCleanupExpiredReservations($conn);
sendDueSoonReminders($conn);

$result = mysqli_query($conn, "
    SELECT br.borrow_id, br.status, br.reservation_time, br.expiry_time, br.borrow_date, br.due_date,
        u.full_name, u.user_id, em.name, pi.serial_number, pi.item_id
    FROM borrow_records br
    JOIN users u ON u.user_id = br.user_id
    JOIN physical_items pi ON pi.item_id = br.item_id
    JOIN equipment_models em ON em.model_id = pi.model_id
    WHERE br.status IN ('reserved', 'borrowed')
    ORDER BY FIELD(br.status, 'reserved', 'borrowed'), br.expiry_time
");
$active = mysqli_fetch_all($result, MYSQLI_ASSOC);

$expired = getRecentlyExpiredReservations($conn);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Admin Dashboard</title></head>
<body>
    <h1>Admin Dashboard</h1>
    <p>Logged in as <?= htmlspecialchars($_SESSION['username']) ?> (admin) —
    <a href="equipment.php">Equipment</a> |
    <a href="students.php">Students</a> |
    <a href="notifications.php">Send Notification</a> |
    <a href="borrow_records.php">Borrow Records</a> |
    <a href="fines.php">Fines</a> |
    <a href="log_damage.php">Log Damage/Loss</a> |
    <a href="reports.php">Reports</a> |
    <a href="search.php">Search</a> |
    <a href="../logout.php">Log out</a></p>

    <?php $message = getFlash(); ?>
    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <h2>Active Reservations &amp; Borrows</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>Borrow #</th><th>Student</th><th>Item</th><th>Serial #</th>
            <th>Status</th><th>Reserved Until / Due</th><th>Action</th>
        </tr>
        <?php foreach ($active as $r): ?>
            <tr>
                <td><?= (int) $r['borrow_id'] ?></td>
                <td><?= htmlspecialchars($r['full_name']) ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['serial_number']) ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td><?= htmlspecialchars($r['status'] === 'reserved' ? $r['expiry_time'] : $r['due_date']) ?></td>
                <td>
                    <?php if ($r['status'] === 'reserved'): ?>
                        <a href="release.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Release to student</a> |
                        <a href="cancel_reservation.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Cancel</a>
                    <?php else: ?>
                        <a href="return.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Process return</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Recently Expired Reservations (no-shows)</h2>
    <table border="1" cellpadding="6">
        <tr><th>Borrow #</th><th>Student</th><th>Item</th><th>Serial #</th><th>Expired At</th></tr>
        <?php foreach ($expired as $r): ?>
            <tr>
                <td><?= (int) $r['borrow_id'] ?></td>
                <td><?= htmlspecialchars($r['full_name']) ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['serial_number']) ?></td>
                <td><?= htmlspecialchars($r['expiry_time']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
