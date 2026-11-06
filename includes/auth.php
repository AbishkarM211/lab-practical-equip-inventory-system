<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function registerUser(mysqli $conn, string $username, string $password, string $email, string $fullName, string $phone, string $role = 'student'): array
{
    if ($username === '' || $password === '' || $email === '' || $fullName === '' || $phone === '') {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        return ['success' => false, 'message' => 'Phone number must be exactly 10 digits.'];
    }
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? OR email = ? OR phone = ?");
    mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $phone);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($result)) {
        return ['success' => false, 'message' => 'That username or email is already registered.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'ssssss', $username, $hash, $fullName, $email, $phone, $role);
    mysqli_stmt_execute($stmt);

    return ['success' => true, 'user_id' => (int) mysqli_insert_id($conn)];
}

function loginUser(mysqli $conn, string $username, string $password): array
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'This account has been deactivated. Contact an admin.'];
    }

    $_SESSION['user_id']  = (int) $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    return ['success' => true, 'role' => $user['role']];
}

function setFlash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function getFlash(): ?string
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $message;
}

function logoutUser(): void
{
    $_SESSION = [];
    session_destroy();
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die('Access denied: this page is ' . $role . '-only.');
    }
}
