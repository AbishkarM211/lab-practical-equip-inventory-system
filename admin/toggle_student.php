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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setStudentActive($conn, $userId, !$student['is_active']);
    $msg = $student['is_active'] ? 'Student deactivated.' : 'Student reactivated.';
    setFlash($msg);
header('Location: students.php');
    exit;
}

$action = $student['is_active'] ? 'Deactivate' : 'Reactivate';
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/style.css"><title><?= $action ?> Student</title></head>
<body>
    <h1><?= $action ?> "<?= htmlspecialchars($student['full_name']) ?>"?</h1>
    <?php if ($student['is_active']): ?>
        <p>They won't be able to log in until reactivated. Existing borrow records and fines are unaffected.</p>
    <?php else: ?>
        <p>They'll be able to log in again immediately.</p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="user_id" value="<?= $userId ?>">
        <button type="submit">Yes, <?= strtolower($action) ?> this account</button>
    </form>
    <p><a href="students.php">Never mind, go back</a></p>
</body>
</html>
