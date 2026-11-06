<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: fines.php');
    exit;
}

$fineId = (int) ($_POST['fine_id'] ?? 0);
$message = markFinePaid($conn, $fineId)
    ? 'Fine marked as paid.'
    : 'Fine was not found or has already been paid.';

setFlash($message);
header('Location: fines.php');
exit;
