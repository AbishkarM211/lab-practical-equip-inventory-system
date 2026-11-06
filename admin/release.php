<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$borrowId = (int) ($_GET['borrow_id'] ?? $_POST['borrow_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dueDate = $_POST['due_date'] ?? date('Y-m-d 23:59:59');
    $result = releaseReservation($conn, $borrowId, $dueDate);
    $message = $result['success'] ? 'Item released to student.' : $result['message'];
    setFlash($message);
header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Release Item</title></head>
<body>
    <h1>Release Item #<?= $borrowId ?> to Student</h1>
    <form method="post">
        <input type="hidden" name="borrow_id" value="<?= $borrowId ?>">
        <label>Due date/time:
            <input type="datetime-local" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime('+8 hours')) ?>">
        </label><br>
        <button type="submit">Confirm hand-off</button>
    </form>
    <p><a href="dashboard.php">Cancel</a></p>
</body>
</html>
