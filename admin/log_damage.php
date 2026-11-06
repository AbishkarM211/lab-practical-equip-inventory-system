<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $reason = $_POST['reason'] ?? '';
    $notes  = trim($_POST['notes'] ?? '');

    $result = logDamageOrLoss($conn, $itemId, $reason, $notes, (int) $_SESSION['user_id']);

    if ($result['success']) {
        $success = 'Logged. The unit no longer shows up as available to students.';
    } else {
        $error = $result['message'];
    }
}

$availableItems = getAvailableItemsForDamageLog($conn);
$log = getDamageLossLog($conn);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Log Damage / Loss</title></head>
<body>
    <h1>Log Damage / Loss</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="equipment.php">Equipment</a> | <a href="../logout.php">Log out</a></p>
    <p>For flagging an item as damaged/lost <em>outside</em> the normal return flow —
    e.g. found broken during an inventory audit. Returns processed on the
    Dashboard already log here automatically.</p>

    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <?php if (empty($availableItems)): ?>
        <p>No available units to flag right now.</p>
    <?php else: ?>
        <form method="post">
            <label>Unit:
                <select name="item_id" required>
                    <?php foreach ($availableItems as $i): ?>
                        <option value="<?= (int) $i['item_id'] ?>">
                            <?= htmlspecialchars($i['name']) ?> — <?= htmlspecialchars($i['serial_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label><br>
            <label><input type="radio" name="reason" value="damaged" checked> Damaged</label>
            <label><input type="radio" name="reason" value="lost"> Lost</label><br>
            <label>Notes: <input type="text" name="notes" size="50" placeholder="What happened?"></label><br>
            <button type="submit">Log it</button>
        </form>
    <?php endif; ?>

    <h2>Recent Log</h2>
    <table border="1" cellpadding="6">
        <tr><th>Date</th><th>Item</th><th>Serial #</th><th>Value</th><th>Reason</th><th>Notes</th><th>Logged By</th></tr>
        <?php foreach ($log as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['logged_at']) ?></td>
                <td><?= htmlspecialchars($l['item_name']) ?></td>
                <td><?= htmlspecialchars($l['serial_number']) ?></td>
                <td>Rs. <?= number_format($l['item_value'], 2) ?></td>
                <td><?= htmlspecialchars($l['reason']) ?></td>
                <td><?= htmlspecialchars($l['notes']) ?></td>
                <td><?= htmlspecialchars($l['logged_by_name']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
