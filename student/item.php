<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

$itemId = (int) ($_GET['id'] ?? 0);
$item = getItem($conn, $itemId);
$message = getFlash();

if (!$item) {
    http_response_code(404);
    die('Item not found.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($item['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page">
        <p><a href="browse.php">← Back to Browse</a></p>

        <?php if ($message): ?>
            <p class="success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="item-detail card">
            <div>
                <?php if (!empty($item['image_path'])): ?>
                    <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-large-image">
                <?php else: ?>
                    <div class="large-image-placeholder">No image available</div>
                <?php endif; ?>
            </div>

            <div>
                <p class="eyebrow"><?= htmlspecialchars($item['category']) ?></p>
                <h1><?= htmlspecialchars($item['name']) ?></h1>
                <p class="item-description"><?= nl2br(htmlspecialchars($item['description'] ?? 'No description available.')) ?></p>
                <p><strong>Serial number:</strong> <?= htmlspecialchars($item['serial_number']) ?></p>
                <p><strong>Value:</strong> Rs. <?= number_format($item['item_value'], 2) ?></p>

                <?php if ($item['status'] === 'available'): ?>
                    <form method="post" action="reserve.php">
                        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                        <button type="submit">Reserve this item for <?= RESERVATION_HOURS ?>h</button>
                    </form>
                <?php else: ?>
                    <p class="warning">This physical item is currently <?= htmlspecialchars($item['status']) ?>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
