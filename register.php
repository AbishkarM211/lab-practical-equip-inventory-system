<?php
require 'config.php';
require 'includes/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser(
        $conn,
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? '',
        trim($_POST['email'] ?? ''),
        trim($_POST['full_name'] ?? ''),
        'student'
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
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Register</title>
</head>
<body>
    <h1>Create an account</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Full name:
            <input type="text" name="full_name" required>
        </label>

        <label>Username:
            <input type="text" name="username" required>
        </label>

        <label>Email:
            <input type="email" name="email" required>
        </label>

        <label>Password:
            <input type="password" name="password" required>
        </label>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Already have an account? Log in</a></p>
</body>
</html>
