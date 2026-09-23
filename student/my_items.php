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
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../assets/css/style.css"><title>My Borrowing</title></head>
<body><div class="page">
<h1>My Borrowing</h1>
<p><a href="browse.php">Browse Equipment</a> | <a href="../logout.php">Logout</a></p>
<?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<table>
<tr><th>Item</th><th>Serial #</th><th>Status</th><th>Reserved</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Action</th></tr>
<?php foreach ($records as $r): ?>
<tr>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= htmlspecialchars($r['serial_number']) ?></td>
<td><?= htmlspecialchars($r['status']) ?></td>
<td><?= htmlspecialchars($r['reservation_time']) ?></td>
<td><?= htmlspecialchars($r['borrow_date'] ?? '—') ?></td>
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
</div></body></html>
