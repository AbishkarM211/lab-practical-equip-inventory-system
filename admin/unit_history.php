<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$itemId = (int) ($_GET['item_id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT pi.item_id, pi.serial_number, pi.status, pi.created_at, pi.model_id, em.name AS model_name, em.category, em.description FROM physical_items pi JOIN equipment_models em ON em.model_id = pi.model_id WHERE pi.item_id = ?");

mysqli_stmt_bind_param($stmt, "i", $itemId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$unit = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$unit) {
    header('Location: equipment.php?error=' . urlencode('Equipment unit not found.'));
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT br.borrow_id, br.status, br.reservation_time, br.expiry_time, br.borrow_date, br.due_date, br.return_date, u.full_name, u.username FROM borrow_records br JOIN users u ON u.user_id = br.user_id WHERE br.item_id = ? ORDER BY COALESCE(br.borrow_date, br.reservation_time) DESC, br.borrow_id DESC");

mysqli_stmt_bind_param($stmt, "i", $itemId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$history = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);

$totalBorrows = 0;
$totalReturns = 0;
$totalDuration = 0;
$durationCount = 0;
$latestBorrower = null;

foreach ($history as $record) {
    if ($record['borrow_date'] !== null) {
        $totalBorrows++;
        if ($latestBorrower === null) {
            $latestBorrower = $record['full_name'];
        }
    }

    if (
        $record['borrow_date'] !== null &&
        $record['return_date'] !== null
    ) {
        $totalReturns++;
        $duration = strtotime($record['return_date'])- strtotime($record['borrow_date']);
        if ($duration >= 0) {
            $totalDuration += $duration;
            $durationCount++;
        }
    }
}

$averageDuration = $durationCount > 0 ? round($totalDuration / $durationCount / 86400, 1) : null;

$damageHistory = getDamageLossHistoryForItem($conn, $itemId);

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Unit History — <?= htmlspecialchars($unit['serial_number']) ?></title>
</head>
<body>
    <h1>Equipment Unit History</h1>
    <p>
        <a href="equipment_units.php?model_id=<?= (int) $unit['model_id'] ?>"> Back to Units</a> | <a href="equipment.php">Equipment</a> | <a href="dashboard.php">Dashboard</a>
    </p>
    <hr>

    <h2>Equipment Information</h2>
    <p>
        <strong>Equipment:</strong> <?= htmlspecialchars($unit['model_name']) ?>
    </p>

    <p>
        <strong>Serial Number:</strong> <?= htmlspecialchars($unit['serial_number']) ?>
    </p>

    <p>
        <strong>Category:</strong> <?= htmlspecialchars($unit['category'] ?? 'Uncategorized') ?>
    </p>

    <p>
        <strong>Current Status:</strong> <?= htmlspecialchars($unit['status']) ?>
    </p>

    <p>
        <strong>Added On:</strong> <?= htmlspecialchars($unit['created_at']) ?>
    </p>

    <?php if (!empty($unit['description'])): ?>
        <p>
            <strong>Description:</strong>
            <?= nl2br(htmlspecialchars($unit['description'])) ?>
        </p>
    <?php endif; ?>

    <hr>
    <h2>Usage Statistics</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Total Borrowings</th>
            <th>Completed Returns</th>
            <th>Average Borrowing Duration</th>
            <th>Most Recent Borrower</th>
        </tr>

        <tr>
            <td><?= $totalBorrows ?></td>
            <td><?= $totalReturns ?></td>
            <td>
                <?= $averageDuration !== null ? $averageDuration . ' days' : 'N/A'
                ?>
            </td>
            <td>
                <?= htmlspecialchars($latestBorrower ?? 'None') ?>
            </td>
        </tr>
    </table>
    <hr>

    <h2>Borrowing History</h2>

    <?php if (empty($history)): ?>
        <p>This equipment unit has no borrowing history yet.</p>
    <?php else: ?>

        <table border="1" cellpadding="6">
            <tr>
                <th>Borrow #</th>
                <th>Student</th>
                <th>Reserved At</th>
                <th>Borrowed At</th>
                <th>Due Date</th>
                <th>Returned At</th>
                <th>Status</th>
            </tr>
            <?php foreach ($history as $record): ?>
                <tr>
                    <td><?= (int) $record['borrow_id'] ?></td>
                    <td>
                        <?= htmlspecialchars($record['full_name']) ?>
                        <small>(<?= htmlspecialchars($record['username']) ?>)
                        </small>
                    </td>
                    <td>
                        <?= htmlspecialchars($record['reservation_time']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($record['borrow_date'] ?? '—') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($record['due_date'] ?? '—') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($record['return_date'] ?? '—') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($record['status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>


<h2>Damage / Loss History</h2>

<?php if (empty($damageHistory)): ?>
    <p>No damage or loss records have been recorded for this unit.</p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Log #</th>
            <th>Date</th>
            <th>Reason</th>
            <th>Value</th>
            <th>Notes</th>
            <th>Related Borrow #</th>
            <th>Logged By</th>
        </tr>

        <?php foreach ($damageHistory as $d): ?>
            <tr>
                <td><?= (int) $d['log_id'] ?></td>
                <td><?= htmlspecialchars($d['logged_at']) ?></td>
                <td><?= htmlspecialchars(ucfirst($d['reason'])) ?></td>
                <td>Rs. <?= number_format($d['item_value'], 2) ?></td>
                <td><?= htmlspecialchars($d['notes'] ?? '') ?></td>
                <td>
                    <?= $d['borrow_id'] !== null ? (int) $d['borrow_id'] : '—' ?>
                </td>
                <td><?= htmlspecialchars($d['logged_by_name']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>
