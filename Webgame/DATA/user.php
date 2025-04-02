<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "Webgamestore";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete user
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: user.php");
    exit();
}

// Add or update user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if ($id) {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password_hash =? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $password, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: user.php");
    exit();
}

// Get user list
$result = $conn->query("SELECT id, username, email FROM users");

// Get user information for editing
$edit_user = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_user = $edit_result->fetch_assoc();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="/Webgame/CSS/user.css">
</head>
<body>
    <div class="user-management">
        <h2>User Management</h2>
        <form method="POST" action="user.php">
            <input type="hidden" name="id" value="<?= $edit_user['id'] ?? '' ?>">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>" required>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Save</button>
        </form>
    </div>

    <div class="user-list">
        <h3>User List</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <a href="user.php?edit=<?= $row['id'] ?>">Edit</a> |
                    <a href="user.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <br>
    <button class="back-btn" onclick="location.href='../index.php'">Back to Dashboard</button>

</body>
</html>

<?php $conn->close(); ?>