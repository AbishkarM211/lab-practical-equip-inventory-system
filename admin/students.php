<?php
require '../config.php';
require '../includes/auth.php';
requireRole('admin');

$message = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id AND role='student'");
    $message = 'Student deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));

    if ($name !== '' && $email !== '') {
        if (!empty($_POST['user_id'])) {
            $id = (int) $_POST['user_id'];
            mysqli_query($conn, "UPDATE users SET full_name='$name', email='$email' WHERE user_id=$id AND role='student'");
            $message = 'Student updated.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$id AND role='student'");
    $edit = mysqli_fetch_assoc($result);
}

$result = mysqli_query($conn, "SELECT user_id, username, full_name, email FROM users WHERE role='student' ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Students</title></head>
<body>
    <h1>Student Management</h1>
    <p><a href="dashboard.php">Dashboard</a> | <a href="equipment.php">Equipment</a> | <a href="../logout.php">Logout</a></p>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($edit): ?>
        <h2>Edit Student</h2>
        <form method="post">
            <input type="hidden" name="user_id" value="<?= $edit['user_id'] ?>">
            <p>Username: <?= htmlspecialchars($edit['username']) ?></p>
            <p>Full name: <input type="text" name="full_name" value="<?= htmlspecialchars($edit['full_name']) ?>"></p>
            <p>Email: <input type="email" name="email" value="<?= htmlspecialchars($edit['email']) ?>"></p>
            <button type="submit">Update</button>
            <a href="students.php">Cancel</a>
        </form>
    <?php endif; ?>

    <h2>Students</h2>
    <table border="1" cellpadding="5">
        <tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Actions</th></tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['user_id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <a href="students.php?edit=<?= $row['user_id'] ?>">Edit</a>
                    <a href="students.php?delete=<?= $row['user_id'] ?>" onclick="return confirm('Delete this student?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
