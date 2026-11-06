<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

$userId = (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
$student = getStudent($conn, $userId);

if (!$student) {
    setFlash('Student not found.');
header('Location: students.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateStudentProfile($conn, $userId, trim($_POST['full_name']), trim($_POST['email']), trim($_POST['phone'] ?? ''));

    if ($result['success']) {
        setFlash('Student profile updated.');
header('Location: students.php');
        exit;
    }
    $error = $result['message'];
    $student['full_name'] = $_POST['full_name'];
    $student['email'] = $_POST['email'];
    $student['phone'] = $_POST['phone'] ?? '';
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title>Edit Student</title></head>
<body>
    <h1>Edit Student</h1>
    <p><a href="students.php">Back to Students</a></p>

    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="user_id" value="<?= $userId ?>">
        <p>Username: <?= htmlspecialchars($student['username']) ?> (not editable)</p>
        <label>Full name: <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required></label><br>
        <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required></label><br>
        <label>Phone: <input type="text" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" maxlength="10" inputmode="numeric" required></label><br>
        <button type="submit">Save</button>
    </form>
</body>
</html>
