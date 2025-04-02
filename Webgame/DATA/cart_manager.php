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

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;

// Delete paid order
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];

    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND total_price IS NOT NULL");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: cart_manager.php");
    exit();
}

// Get list of paid orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND total_price IS NOT NULL");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/cartmana.css">
</head>
<body>
    <div class="cart-manager">
        <h2>Paid Order Management</h2>

        <?php if (!empty($orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= $order['order_date'] ?></td>
                            <td><?= $order['total_price'] ?> VND</td>
                            <td>
                                <a href="cart_manager.php?delete_order=<?= $order['id'] ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No paid orders found.</p>
        <?php endif; ?>

        <a href="/Webgame/index.php" class="back-home-btn">Back to Home</a>
    </div>
</body>
</html>