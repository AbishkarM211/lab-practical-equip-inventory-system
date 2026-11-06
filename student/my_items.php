<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

lazyCleanupExpiredReservations($conn);
$message = getFlash();
$userId = (int) $_SESSION['user_id'];
$result = mysqli_query($conn, "
    SELECT br.borrow_id, br.status, br.reservation_time, br.expiry_time, br.borrow_date, br.due_date, br.return_date,
        em.name, pi.serial_number
    FROM borrow_records br
    JOIN physical_items pi ON pi.item_id = br.item_id
    JOIN equipment_models em ON em.model_id = pi.model_id
    WHERE br.user_id = $userId
    ORDER BY br.created_at DESC
");
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);
$result = mysqli_query($conn, "
    SELECT f.fine_amount, f.fine_reason, br.borrow_id
    FROM fines f
    JOIN borrow_records br ON br.borrow_id = f.borrow_id
    WHERE br.user_id = $userId AND f.payment_status = 'unpaid'
    ORDER BY f.created_at DESC
");

$unpaidFines = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>My Borrowed Items</title></head>
<body>
    <h1>My Borrowed Items</h1>
    <p><a href="browse.php">Back to Browse</a> | <a href="../logout.php">Log out</a></p>
    <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($unpaidFines): ?>
        <h2 style="color:red;">Unpaid Fines</h2>
        <ul>
            <?php foreach ($unpaidFines as $f): ?>
                <li>Borrow #<?= (int) $f['borrow_id'] ?> — <?= htmlspecialchars($f['fine_reason']) ?>:
                    $<?= number_format($f['fine_amount'], 2) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <table border="1" cellpadding="6">
        <tr>
            <th>Item</th><th>Serial #</th><th>Status</th>
            <th>Reserved</th><th>Due</th><th>Returned</th><th>Action</th>
        </tr>
        <?php foreach ($records as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['serial_number']) ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td><?= htmlspecialchars($r['reservation_time']) ?></td>
                <td><?= htmlspecialchars($r['due_date'] ?? '—') ?></td>
                <td><?= htmlspecialchars($r['return_date'] ?? '—') ?></td>
                <td>                
                    <?php if ($r['status'] === 'reserved'): ?>
                        <a href="cancel_reservation.php?borrow_id=<?= (int) $r['borrow_id'] ?>">Cancel</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
