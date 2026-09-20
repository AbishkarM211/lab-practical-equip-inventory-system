<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$result = mysqli_query($conn, "
    SELECT br.borrow_id, br.status, br.reservation_time, br.borrow_date, br.due_date, br.return_date,
           u.full_name, em.name, pi.serial_number
    FROM borrow_records br
    JOIN users u ON u.user_id = br.user_id
    JOIN physical_items pi ON pi.item_id = br.item_id
    JOIN equipment_models em ON em.model_id = pi.model_id
    ORDER BY br.created_at DESC
");
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../assets/css/style.css"><title>Borrow Records</title></head>
<body><div class="page">
<h1>Borrow Records</h1>
<p><a href="dashboard.php">Dashboard</a> | <a href="../logout.php">Logout</a></p>
<table>
<tr><th>ID</th><th>Student</th><th>Item</th><th>Serial #</th><th>Status</th><th>Reserved</th><th>Borrowed</th><th>Due</th><th>Returned</th></tr>
<?php foreach ($records as $r): ?>
<tr>
<td><?= (int) $r['borrow_id'] ?></td><td><?= htmlspecialchars($r['full_name']) ?></td><td><?= htmlspecialchars($r['name']) ?></td><td><?= htmlspecialchars($r['serial_number']) ?></td><td><?= htmlspecialchars($r['status']) ?></td>
<td><?= htmlspecialchars($r['reservation_time']) ?></td><td><?= htmlspecialchars($r['borrow_date'] ?? '—') ?></td><td><?= htmlspecialchars($r['due_date'] ?? '—') ?></td><td><?= htmlspecialchars($r['return_date'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
</table>
</div></body></html>
