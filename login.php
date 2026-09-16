<?php
require 'config.php';
require 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser($conn, trim($_POST['username'] ?? ''), $_POST['password'] ?? '');

    if ($result['success']) {
        if ($result['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: student/browse.php');
        }
        exit;
    }

    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html>
<head><title>Lab Inventory - Login</title></head>
<body>
    <h1>Lab Inventory System</h1>
    <h2>Login</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <p style="color:green;">Registration successful. Please login.</p>
    <?php endif; ?>

    <form method="post">
        <p>Username: <input type="text" name="username"></p>
        <p>Password: <input type="password" name="password"></p>
        <button type="submit">Login</button>
    </form>

    <p><a href="register.php">Register as a student</a></p>
</body>
</html>
