<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function registerUser($conn, $username, $password, $email, $fullName, $role = 'student') {
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $fullName = mysqli_real_escape_string($conn, $fullName);

    $check = mysqli_query($conn, "SELECT user_id FROM users WHERE username='$username' OR email='$email'");
    if (mysqli_num_rows($check) > 0) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, password, full_name, email, role) VALUES ('$username', '$password', '$fullName', '$email', '$role')";

    if (mysqli_query($conn, $query)) {
        return ['success' => true];
    }

    return ['success' => false, 'message' => 'Could not create account.'];
}

function loginUser($conn, $username, $password) {
    $username = mysqli_real_escape_string($conn, $username);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    return ['success' => true, 'role' => $user['role']];
}

function setFlash($message) {
    $_SESSION['flash'] = $message;
}

function getFlash() {
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $message;
}

function logoutUser() {
    $_SESSION = [];
    session_destroy();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die('Access denied.');
    }
}
