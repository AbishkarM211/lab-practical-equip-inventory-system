<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$borrowId = (int) ($_GET['borrow_id'] ?? $_POST['borrow_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = returnItem($conn, $borrowId);
    setFlash($result['success'] ? 'Item returned successfully.' : $result['message']);
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../assets/css/style.css"><title>Return Item</title></head>
<body><div class="page">
<h1>Return Item — Borrow #<?= $borrowId ?></h1>
<p>Confirm that the item has been returned.</p>
<form method="post"><input type="hidden" name="borrow_id" value="<?= $borrowId ?>"><button type="submit">Confirm Return</button></form>
<p><a href="dashboard.php">Cancel</a></p>
</div></body></html>
