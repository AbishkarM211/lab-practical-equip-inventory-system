<?php
require '../config.php';
require '../includes/auth.php';
require '../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $result = deleteStudent($conn, (int) $_POST['delete_id']);

    $_SESSION['flash_message'] = $result['success']
        ? 'Student account deleted successfully.'
        : $result['message'];

    header('Location: students.php');
    exit;
}

$students = getAllStudents($conn);
$message = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Manage Students</title>
</head>
<body>
    <h1>Students</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="../logout.php">Log out</a></p>

    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Active Borrows</th>
            <th>Unpaid Fines</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['full_name']) ?></td>
                <td><?= htmlspecialchars($s['username']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
                <td><?= $s['is_active'] ? 'Active' : 'Inactive' ?></td>
                <td><?= (int) $s['active_borrows'] ?></td>
                <td><?= (int) $s['unpaid_fines'] ?></td>
                <td>
                    <a href="edit_student.php?user_id=<?= (int) $s['user_id'] ?>">Edit</a>
                    |
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this student account?');">
                        <input type="hidden" name="delete_id" value="<?= (int) $s['user_id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
