<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

lazyCleanupExpiredReservations($conn);
$message = getFlash();

$result = mysqli_query($conn, "
    SELECT br.borrow_id, br.status, br.reservation_time, br.expiry_time, br.borrow_date, br.due_date,
           u.full_name, em.name, pi.serial_number
    FROM borrow_records br
    JOIN users u ON u.user_id = br.user_id
    JOIN physical_items pi ON pi.item_id = br.item_id
    JOIN equipment_models em ON em.model_id = pi.model_id
    WHERE br.status IN ('reserved', 'borrowed')
    ORDER BY br.created_at DESC
");
$active = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Admin Dashboard</title></head>
<body>
<div class="page">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>.</p>
    <p><a href="equipment.php">Manage Equipment</a> | <a href="students.php">Manage Students</a> | <a href="borrow_records.php">Borrow Records</a> | <a href="../logout.php">Logout</a></p>

    <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <h2>Active Reservations &amp; Borrows</h2>
    <table>
        <tr><th>Borrow #</th><th>Student</th><th>Item</th><th>Serial #</th><th>Status</th><th>Reserved Until / Due</th><th>Action</th></tr>
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
                        <a href="release.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Checkout</a> |
                        <a href="cancel_reservation.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Cancel</a>
                    <?php else: ?>
                        <a href="return.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Return</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
