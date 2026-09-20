<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

$message = getFlash();
$items = getAvailableItems($conn);
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Available Equipment</title></head>
<body><div class="page">
<h1>Available Equipment</h1>
<p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>.</p>
<p><a href="my_items.php">My Borrowing</a> | <a href="../logout.php">Logout</a></p>
<?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<table>
<tr><th>Item</th><th>Category</th><th>Serial Number</th><th>Action</th></tr>
<?php foreach ($items as $item): ?>
<tr>
<td><?= htmlspecialchars($item['name']) ?></td><td><?= htmlspecialchars($item['category']) ?></td><td><?= htmlspecialchars($item['serial_number']) ?></td>
<td><form method="post" action="reserve.php" class="inline-form"><input type="hidden" name="item_id" value="<?= $item['item_id'] ?>"><button type="submit">Reserve</button></form></td>
</tr>
<?php endforeach; ?>
</table>
</div></body></html>
