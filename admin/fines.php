<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$unpaid = getFines($conn, 'unpaid');
$paid = getFines($conn, 'paid');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Fines</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Fine Management</h1>
    <p>
        <a href="dashboard.php">Dashboard</a> |
        <a href="students.php">Students</a> |
        <a href="reports.php">Reports</a> |
        <a href="../logout.php">Log out</a>
    </p>

    <?php $message = getFlash(); ?>
    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <h2>Unpaid Fines</h2>
    <?php if (!$unpaid): ?>
        <p>No unpaid fines. Everything is cleared.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Student</th><th>Borrow #</th><th>Item</th><th>Reason</th>
                <th>Amount</th><th>Issued</th><th>Action</th>
            </tr>
            <?php foreach ($unpaid as $fine): ?>
                <tr>
                    <td><?= htmlspecialchars($fine['full_name']) ?></td>
                    <td>#<?= (int) $fine['borrow_id'] ?></td>
                    <td><?= htmlspecialchars($fine['item_name']) ?> (<?= htmlspecialchars($fine['serial_number']) ?>)</td>
                    <td><?= htmlspecialchars(ucfirst($fine['fine_reason'])) ?></td>
                    <td>Rs. <?= number_format($fine['fine_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($fine['issued_at']) ?></td>
                    <td>
                        <form method="post" action="mark_fine_paid.php" style="padding:0; margin:0; border:0; background:none;">
                            <input type="hidden" name="fine_id" value="<?= (int) $fine['fine_id'] ?>">
                            <button type="submit">Mark as Paid</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <h2>Paid Fines</h2>
    <?php if (!$paid): ?>
        <p>No paid fines yet.</p>
    <?php else: ?>
        <table>
            <tr><th>Student</th><th>Borrow #</th><th>Reason</th><th>Amount</th><th>Paid At</th></tr>
            <?php foreach ($paid as $fine): ?>
                <tr>
                    <td><?= htmlspecialchars($fine['full_name']) ?></td>
                    <td>#<?= (int) $fine['borrow_id'] ?></td>
                    <td><?= htmlspecialchars(ucfirst($fine['fine_reason'])) ?></td>
                    <td>Rs. <?= number_format($fine['fine_amount'], 2) ?></td>
                    <td class="status-paid"><?= htmlspecialchars($fine['paid_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
