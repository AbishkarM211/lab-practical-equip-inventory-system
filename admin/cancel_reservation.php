<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$borrowId = (int) ($_GET['borrow_id'] ?? $_POST['borrow_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = cancelReservation($conn, $borrowId);
    setFlash($result['success'] ? 'Reservation cancelled.' : $result['message']);
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../assets/css/style.css"><title>Cancel Reservation</title></head>
<body><div class="page">
<h1>Cancel Reservation #<?= $borrowId ?>?</h1>
<p>This will make the reserved item available again.</p>
<form method="post"><input type="hidden" name="borrow_id" value="<?= $borrowId ?>"><button type="submit">Confirm Cancellation</button></form>
<p><a href="dashboard.php">Go back</a></p>
</div></body></html>
