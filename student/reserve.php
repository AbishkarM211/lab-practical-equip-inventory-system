<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse.php');
    exit;
}

$itemId = (int) ($_POST['item_id'] ?? 0);
$result = reserveItem($conn, (int) $_SESSION['user_id'], $itemId);

$message = $result['success']
    ? 'Reservation created. Pick up the item before ' . $result['expiry_time'] . '.'
    : $result['message'];

setFlash($message);
header('Location: browse.php');
exit;
