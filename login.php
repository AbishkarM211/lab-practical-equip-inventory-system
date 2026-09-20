<?php
require 'config.php';
require 'includes/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser($conn, trim($_POST['username'] ?? ''), $_POST['password'] ?? '');

    if ($result['success']) {
        header('Location: ' . ($result['role'] === 'admin' ? 'admin/dashboard.php' : 'student/browse.php'));
        exit;
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="assets/css/style.css"><title>Log in</title></head>
<body>
    <h1>Lab Inventory — Log in</h1>
    <?php if (isset($_GET['registered'])): ?>
        <p style="color:green;">Account created — you can log in now.</p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Log in</button>
    </form>
    <p><a href="register.php">Need an account? Register</a></p>
    <p style="color:#888;">Demo accounts: admin1 / jdoe, both password "password123"</p>
</body>
</html>
