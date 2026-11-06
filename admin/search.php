<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$query = trim($_GET['q'] ?? '');
$results = $query !== '' ? searchAll($conn, $query) : null;
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Search</title></head>
<body>
    <h1>Search</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="reports.php">Reports</a> | <a href="../logout.php">Log out</a></p>

    <form method="get">
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Equipment name, serial #, student name/email, or borrow #" size="40">
        <button type="submit">Search</button>
    </form>

    <?php if ($results !== null): ?>

        <h2>Equipment Models (<?= count($results['equipment']) ?>)</h2>
        <?php if ($results['equipment']): ?>
            <ul>
                <?php foreach ($results['equipment'] as $e): ?>
                    <li><a href="equipment_units.php?model_id=<?= (int) $e['model_id'] ?>"><?= htmlspecialchars($e['name']) ?></a>
                        — <?= htmlspecialchars($e['category']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><p>No matches.</p><?php endif; ?>

        <h2>Physical Units (<?= count($results['units']) ?>)</h2>
        <?php if ($results['units']): ?>
            <ul>
                <?php foreach ($results['units'] as $u): ?>
                    <li><?= htmlspecialchars($u['serial_number']) ?> — <?= htmlspecialchars($u['name']) ?>
                        (<?= htmlspecialchars($u['status']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><p>No matches.</p><?php endif; ?>

        <h2>Students (<?= count($results['users']) ?>)</h2>
        <?php if ($results['users']): ?>
            <ul>
                <?php foreach ($results['users'] as $u): ?>
                    <li>
                        <?php if ($u['role'] === 'student'): ?>
                            <a href="edit_student.php?user_id=<?= (int) $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?></a>
                        <?php else: ?>
                            <?= htmlspecialchars($u['full_name']) ?>
                        <?php endif; ?>
                        — <?= htmlspecialchars($u['email']) ?> (<?= htmlspecialchars($u['role']) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><p>No matches.</p><?php endif; ?>

        <h2>Borrow Records</h2>
        <?php if ($results['borrows']): ?>
            <ul>
                <?php foreach ($results['borrows'] as $b): ?>
                    <li>Borrow #<?= (int) $b['borrow_id'] ?> — <?= htmlspecialchars($b['full_name']) ?>,
                        <?= htmlspecialchars($b['name']) ?> (<?= htmlspecialchars($b['status']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><p>No matches (only searched if your query was a number).</p><?php endif; ?>

    <?php endif; ?>
</body>
</html>
