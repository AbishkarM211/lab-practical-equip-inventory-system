<?php
$conn = mysqli_connect('localhost', 'root', '', 'lab_inventory');

if (!$conn) {
    die('Database connection failed.');
}

mysqli_set_charset($conn, 'utf8mb4');

define('RESERVATION_HOURS', 2);
