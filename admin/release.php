<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$borrowId = (int) ($_GET['borrow_id'] ?? $_POST['borrow_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dueDate = $_POST['due_date'] ?? date('Y-m-d 23:59:59');
    $result = releaseReservation($conn, $borrowId, $dueDate);
    setFlash($result['success'] ? 'Item checked out to student.' : $result['message']);
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../assets/css/style.css"><title>Checkout Item</title></head>
<body><div class="page">
<h1>Checkout Item #<?= $borrowId ?></h1>
<form method="post">
<input type="hidden" name="borrow_id" value="<?= $borrowId ?>">
<p>Due date/time: <input type="datetime-local" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime('+8 hours')) ?>"></p>
<button type="submit">Confirm Checkout</button>
</form>
<p><a href="dashboard.php">Cancel</a></p>
</div></body></html>
