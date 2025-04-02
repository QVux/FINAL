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

// Get game ID from URL
if (isset($_GET['id'])) {
    $game_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT title, comment, image, price FROM games WHERE id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $stmt->bind_result($title, $comment, $image, $price);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Game not found.";
    exit();
}

// Handle checkout button
if (isset($_GET['checkout'])) {
    $item = [
        'name' => $title,
        'price' => $price,
        'quantity' => 1 // Default quantity is 1
    ];

    // Create cart array if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add product to cart
    $_SESSION['cart'][] = $item;

    // Redirect to cart page
    header("Location: /WEBGAME/DATA/cart.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Details</title>
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/review.css">
</head>
<body>
    <div class="game-review">
        <h2><?= $title ?></h2>
        <?php if (!empty($image)): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($image) ?>" alt="Game Image">
        <?php else: ?>
            <p>No Image</p>
        <?php endif; ?>
        <p><?= $comment ?></p>

        <div class="review-actions">
            <a href="/Webgame/index.php" class="continue-shopping-btn">Continue Shopping</a>
            <a href="review.php?id=<?= $game_id ?>&checkout=true" class="checkout-btn">Checkout</a>
        </div>
    </div>
</body>
</html> 