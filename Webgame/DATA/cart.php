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

// Get cart data from session
$cart_items = $_SESSION['cart'] ?? [];

// Handle removing product from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_index = $_GET['remove'];
    if (isset($cart_items[$remove_index])) {
        unset($cart_items[$remove_index]);
        // Re-index the array to avoid gaps
        $cart_items = array_values($cart_items);
        $_SESSION['cart'] = $cart_items;
    }
}

// Handle checkout
if (isset($_POST['checkout'])) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Save order information to orders table
    $user_id = $_SESSION['user_id'] ?? null;

    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_price) VALUES (?, NOW(), ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id; // Get the ID of the newly created order
    $stmt->close();

    // Save order details to order_details table
    foreach ($cart_items as $item) {
        $game_id = $item['id']; // Assuming $item['id'] contains game_id
        $quantity = $item['quantity'];
        $subtotal = $item['price'] * $quantity;

        $stmt = $conn->prepare("INSERT INTO order_details (order_id, game_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $game_id, $quantity, $subtotal);
        $stmt->execute();
        $stmt->close();
    }

    // Clear cart after successful checkout
    unset($_SESSION['cart']);

    // Display successful payment message
    $payment_success = true;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/cart.css">
</head>
<body>
    <div class="cart">
        <h2>Your Invoice</h2>
        <?php if (isset($payment_success) && $payment_success === true): ?>
            <p style="color: red;">Payment successful!</p>
        <?php elseif (!empty($cart_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cart_items as $index => $item): ?>
                        <tr>
                            <td><?= $item['name'] ?></td>
                            <td><?= $item['price'] ?> VND</td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= $item['price'] * $item['quantity'] ?> VND</td>
                            <td><a href="cart.php?remove=<?= $index ?>">Remove</a></td>
                        </tr>
                        <?php $total += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total:</td>
                        <td><?= $total ?> VND</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <form method="POST" action="cart.php">
                <input type="hidden" name="checkout" value="true">
                <button type="submit" class="checkout-btn">Checkout</button>
            </form>
        <?php else: ?>
            <p>No products in the cart.</p>
        <?php endif; ?>

        <a href="/Webgame/index.php" class="continue-shopping-btn">Continue Shopping</a>
    </div>
</body>
</html>