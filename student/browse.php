<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

$message = getFlash();
$search = trim($_GET['q'] ?? '');
$items = getAvailableItems($conn, $search);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Equipment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page">
        <h1>Available Equipment</h1>
        <p>Logged in as <?= htmlspecialchars($_SESSION['username']) ?> (student) —
        <a href="my_items.php">My Borrowed Items</a> |
        <a href="notifications.php">Notifications</a> |
        <a href="../logout.php">Log out</a></p>

        <?php if ($message): ?>
            <p class="success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="get" class="search-form">
            <input type="search" name="q" value="<?= htmlspecialchars($search) ?>"
                placeholder="Search by item name, category, or serial number">
            <button type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a class="action-link" href="browse.php">Clear</a>
            <?php endif; ?>
        </form>

        <?php if ($search !== ''): ?>
            <p>Showing results for <strong><?= htmlspecialchars($search) ?></strong>.</p>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="card">
                <p>No available equipment<?= $search !== '' ? ' matched your search' : '' ?>.</p>
            </div>
        <?php else: ?>
            <table>
                <tr>
                    <th>Photo</th><th>Item</th><th>Category</th><th>Serial #</th><th>Value</th><th></th>
                </tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <a href="item.php?id=<?= (int) $item['item_id'] ?>">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-thumb">
                                <?php else: ?>
                                    <span class="image-placeholder">No image</span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td><a href="item.php?id=<?= (int) $item['item_id'] ?>"><strong><?= htmlspecialchars($item['name']) ?></strong></a></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><?= htmlspecialchars($item['serial_number']) ?></td>
                        <td>Rs. <?= number_format($item['item_value'], 2) ?></td>
                        <td>
                            <form method="post" action="reserve.php" class="inline-form">
                                <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                <button type="submit">Hold for <?= RESERVATION_HOURS ?>h</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
