<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notifications.php');
    exit;
}

$userId  = (int) ($_POST['user_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($userId > 0 && $message !== '') {
    sendNotification($conn, $userId, $message);
    $msg = 'Notification sent.';
} else {
    $msg = 'Message could not be sent — it was empty.';
}

setFlash($msg);
header('Location: notifications.php');
exit;
