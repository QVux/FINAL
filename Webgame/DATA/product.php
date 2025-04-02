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

// Delete game
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM games WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: product.php");
        exit();
    } else {
        echo "Error deleting game: " . $stmt->error;
    }
    $stmt->close();
}

// Add/Edit game
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $category_id = $_POST['category_id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $genre = $_POST['genre'];
    $comment = $_POST['comment'];

    // Handle image upload
    $image = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if ($id) {
        if ($image) {
            $stmt = $conn->prepare("UPDATE games SET category_id=?, title=?, price=?, genre=?, comment=?, image=? WHERE id=?");
            $stmt->bind_param("isdssbi", $category_id, $title, $price, $genre, $comment, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE games SET category_id=?, title=?, price=?, genre=?, comment=? WHERE id=?");
            $stmt->bind_param("isdssi", $category_id, $title, $price, $genre, $comment, $id);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO games (category_id, title, price, genre, comment, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssb", $category_id, $title, $price, $genre, $comment, $image);
    }
    $stmt->send_long_data(5, $image);
    $stmt->execute();
    $stmt->close();
    header("Location: product.php");
    exit();
}

// Get list of games
$result = $conn->query("SELECT games.*, categories.name as category_name FROM games LEFT JOIN categories ON games.category_id = categories.id");
$categories = $conn->query("SELECT * FROM categories");

// Get game data for editing
$edit_game = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM games WHERE id = $id");
    if ($edit_result->num_rows > 0) {
        $edit_game = $edit_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/product.css">
    <title>Game Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #218838;
            text-align: center;
        }
        img {
            max-width: 100px; /* Adjust as needed */
            height: auto;
        }
    </style>
</head>
<body>
    <div class="game-management">
        <h2><?php echo isset($edit_game) ? 'Edit Game' : 'Game Management'; ?></h2>
        <form method="POST" action="product.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $edit_game['id'] ?? ''; ?>">
            <label>Category:</label>
            <select name="category_id" required>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?php echo (isset($edit_game) && $edit_game['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?= $cat['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <label>Game Title:</label>
            <input type="text" name="title" value="<?php echo $edit_game['title'] ?? ''; ?>" required>
            <label>Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo $edit_game['price'] ?? ''; ?>" required>
            <label>Game Image:</label>
            <input type="file" name="image" accept="image/*">
            <label>Comment:</label>
            <textarea name="comment"><?php echo $edit_game['comment'] ?? ''; ?></textarea>
            <button type="submit"><?php echo isset($edit_game) ? 'Update' : 'Save'; ?></button>
        </form>
    </div>

    <div class="game-list">
    <h3>Game List</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>CATEGORY</th>
                <th>TITLE</th>
                <th>PRICE</th>
                <th>IMAGE</th>
                <th>COMMENT</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['category_name'] ?></td>
                <td><?= $row['title'] ?></td>
                <td><?= $row['price'] ?></td>
                <td>
                    <?php if (!empty($row['image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image']) ?>" alt="Game Image">
                    <?php else: ?>
                        No Image
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['comment']) ?></td>
                <td>
                    <button href="product.php?edit=<?= $row['id'] ?>">Edit</button>
                    <button href="product.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete?');">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
    <a href="/Webgame/index.php" class="back-home-btn">Back to Home</a>

</body>
</html>
<?php $conn->close(); ?>