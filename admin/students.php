<?php
require '../config.php';
require '../includes/auth.php';
requireRole('admin');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id AND role='student'");
    $message = 'Student deleted.';
}
$result = mysqli_query($conn, "SELECT user_id, username, full_name, email FROM users WHERE role='student' ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"><title>Students</title></head>
<body>
<div class="page">
<h1>Student Management</h1>
<p><a href="dashboard.php">Dashboard</a> | <a href="../logout.php">Logout</a></p>
<?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<table>
<tr><th>Username</th><th>Full Name</th><th>Email</th><th>Action</th></tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
<td><?= htmlspecialchars($row['username']) ?></td>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><form method="post" class="inline-form" onsubmit="return confirm('Delete this student?');"><input type="hidden" name="delete_id" value="<?= $row['user_id'] ?>"><button type="submit">Delete</button></form></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
