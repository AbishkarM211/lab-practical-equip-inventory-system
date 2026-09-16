<?php
require 'config.php';
require 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser(
        $conn,
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? '',
        trim($_POST['email'] ?? ''),
        trim($_POST['full_name'] ?? '')
    );

    if ($result['success']) {
        header('Location: login.php?registered=1');
        exit;
    }

    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html>
<head><title>Lab Inventory - Register</title></head>
<body>
    <h1>Create Student Account</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>Full name: <input type="text" name="full_name"></p>
        <p>Username: <input type="text" name="username"></p>
        <p>Email: <input type="email" name="email"></p>
        <p>Password: <input type="password" name="password"></p>
        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Back to login</a></p>
</body>
</html>
