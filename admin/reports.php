<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$summary = getReportSummary($conn);
$overdue = getOverdueBorrows($conn);
$statuses = ['available', 'reserved', 'borrowed', 'damaged', 'lost'];
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Reports</title></head>
<body>
    <h1>Combined Report</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="search.php">Search</a> | <a href="../logout.php">Log out</a></p>

    <h2>Inventory</h2>
    <ul>
        <li>Total physical units: <?= $summary['total_units'] ?></li>
        <?php foreach ($statuses as $s): ?>
            <li><?= ucfirst($s) ?>: <?= (int) ($summary['units_by_status'][$s] ?? 0) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Students</h2>
    <ul>
        <li>Active accounts: <?= $summary['students_active'] ?></li>
        <li>Deactivated accounts: <?= $summary['students_inactive'] ?></li>
    </ul>

    <h2>Fines</h2>
    <ul>
        <li>Unpaid: <?= $summary['fines_unpaid_count'] ?> fine(s), totaling $<?= number_format($summary['fines_unpaid_amount'], 2) ?></li>
        <li>Paid: <?= $summary['fines_paid_count'] ?> fine(s), totaling $<?= number_format($summary['fines_paid_amount'], 2) ?></li>
    </ul>

    <h2>Overdue Borrows (<?= $summary['overdue_count'] ?>)</h2>
    <?php if (empty($overdue)): ?>
        <p>Nothing overdue right now.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <tr><th>Borrow #</th><th>Student</th><th>Item</th><th>Serial #</th><th>Was Due</th></tr>
            <?php foreach ($overdue as $o): ?>
                <tr>
                    <td><?= (int) $o['borrow_id'] ?></td>
                    <td><?= htmlspecialchars($o['full_name']) ?></td>
                    <td><?= htmlspecialchars($o['name']) ?></td>
                    <td><?= htmlspecialchars($o['serial_number']) ?></td>
                    <td><?= htmlspecialchars($o['due_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
