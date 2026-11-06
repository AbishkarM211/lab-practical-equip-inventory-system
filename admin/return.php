<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$borrowId = (int) ($_GET['borrow_id'] ?? $_POST['borrow_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $condition = $_POST['condition'] ?? 'good';
    $result = returnItem($conn, $borrowId, $condition, (int) $_SESSION['user_id']);

    if ($result['success']) {
        $parts = array_map(
            fn($f) => "{$f['reason']}: \${$f['amount']}",
            $result['fines']
        );
        $message = 'Return processed.' . ($parts ? ' Fines issued — ' . implode(', ', $parts) . '.' : ' No fines.');
    } else {
        $message = $result['message'];
    }

    setFlash($message);
header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Process Return</title></head>
<body>
    <h1>Process Return — Borrow #<?= $borrowId ?></h1>
    <p>Physically inspect the item, then select its condition.</p>
    <form method="post">
        <input type="hidden" name="borrow_id" value="<?= $borrowId ?>">
        <label><input type="radio" name="condition" value="good" checked> Good condition</label><br>
        <label><input type="radio" name="condition" value="damaged"> Damaged</label><br>
        <label><input type="radio" name="condition" value="lost"> Lost</label><br>
        <button type="submit">Confirm</button>
    </form>
    <p><a href="dashboard.php">Cancel</a></p>
</body>
</html>
