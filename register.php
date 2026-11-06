<?php
require 'config.php';
require 'includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'student/browse.php'));
    exit;
}

$error = $_GET['error'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser(
        $conn,
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? '',
        trim($_POST['email'] ?? ''),
        trim($_POST['full_name'] ?? ''),
        trim($_POST['phone'] ?? ''),
        'student'
    );

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
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

    <p id="message"></p>

    <form id="registerForm" method="post">
        <label>Full name:
            <input type="text" name="full_name">
        </label>

        <label>Username:
            <input type="text" name="username">
        </label>

        <label>Email:
            <input type="email" name="email">
        </label>

        <label>Phone number:
            <input type="text" name="phone" maxlength="10" inputmode="numeric">
        </label>

        <label>Password:
            <input type="password" name="password">
        </label>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Already have an account? Log in</a></p>

    <script>
    const form = document.getElementById('registerForm');
    const phone = form.phone;
    const message = document.getElementById('message');

    phone.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        message.textContent = 'Registering...';
        message.style.color = '';

        try {
            const response = await fetch('register.php', {
                method: 'POST',
                body: new FormData(form)
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = 'login.php?registered=1';
                return;
            }

            window.location.href = 'register.php?error=' + encodeURIComponent(result.message);
        } catch (error) {
            message.textContent = 'Something went wrong. Please try again.';
            message.style.color = 'red';
        }
    });
    </script>
</body>
</html>
