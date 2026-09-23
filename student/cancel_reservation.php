<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

$borrowId = (int) ($_GET['borrow_id'] ?? $_POST['borrow_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = cancelStudentReservation($conn, (int) $_SESSION['user_id'], $borrowId);
    setFlash($result['success'] ? 'Reservation cancelled.' : $result['message']);
    header('Location: my_items.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Cancel Reservation</title></head>
<body><div class="page">
<h1>Cancel Reservation</h1>
<p>Are you sure you want to cancel this reservation?</p>
<form method="post">
<input type="hidden" name="borrow_id" value="<?= $borrowId ?>">
<button type="submit">Confirm Cancellation</button>
</form>
<p><a href="my_items.php">Go back</a></p>
</div></body></html>
