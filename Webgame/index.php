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

// Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check login & authorization
if (isset($_SESSION['name'])) {
    if (strpos($_SESSION['name'], "admin") === 0) {
        $_SESSION['role'] = "admin";
    } else {
        $_SESSION['role'] = "member";
    }
}

// Get category list
$categories = $conn->query("SELECT * FROM categories");

// Handle game filtering by category
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Handle search
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';
if (!empty($search_term)) {
    $search_query = "WHERE games.title LIKE '%" . $conn->real_escape_string($search_term) . "%'";
}

if ($category_filter > 0) {
    $category_query = ($search_query == '') ? "WHERE games.category_id = ?" : "AND games.category_id = ?";
    $stmt = $conn->prepare("SELECT games.*, categories.name as category_name FROM games 
                                    LEFT JOIN categories ON games.category_id = categories.id 
                                    $search_query $category_query");
    if($search_query == ''){
        $stmt->bind_param("i", $category_filter);
    }else{
        $stmt->bind_param("i", $category_filter);
    }
} else {
    $stmt = $conn->prepare("SELECT games.*, categories.name as category_name FROM games 
                                    LEFT JOIN categories ON games.category_id = categories.id 
                                    $search_query");
}

$stmt->execute();
$games = $stmt->get_result();

// Handle purchase
if (isset($_POST['add_to_cart'])) {
    $game_id = $_POST['game_id'];

    // Get game information
    $stmt = $conn->prepare("SELECT title, price FROM games WHERE id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $game = $result->fetch_assoc();
    $stmt->close();

    $item = [
        'name' => $game['title'],
        'price' => $game['price'],
        'quantity' => 1 // Default quantity is 1
    ];

    // Create cart array if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add product to cart
    $_SESSION['cart'][] = $item;

    // Redirect to cart page
    header("Location: DATA/cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Web Game</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<header>
    <div class="header-left">
        <div class="logo">
            <a href="index.php"><img src="img/logo.png" alt="Web Game"></a>
        </div>
        <div class="search-bar">
            <form method="GET" action="index.php">
                <input type="text" name="search" placeholder="Search game..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>
    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Hello, <?= $_SESSION['name'] ?? "User" ?> 
            (<?= $_SESSION['role'] ?? "member" ?>)</span>
            <a href="index.php?logout=true"><button>Logout</button></a>
        <?php else: ?>
            <a href="Login/Login.php"><button>Login</button></a>
            <a href="SignUp/Signup.php"><button>Register</button></a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <div class="sidebar">
        <h3>Game Categories</h3>
        <ul>
            <li><a href="index.php">All</a></li>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <li><a href="index.php?category=<?= $cat['id'] ?>"><?= $cat['name'] ?></a></li>
            <?php endwhile; ?>
        </ul>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="admin-panel">
                <h3>Admin Panel</h3>
                <a href="DATA/user.php"><button>User Management</button></a>
                <a href="DATA/product.php"><button>Product Management</button></a>
                <a href="DATA/cart_manager.php"><button>Order Management</button></a>
            </div>
        <?php endif; ?>

    </div>

    <div class="content">
        <h2>Game List</h2>
        <?php if ($games->num_rows > 0): ?>
            <?php while ($game = $games->fetch_assoc()): ?>
                <div class="game">
                    <?php if (!empty($game['image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($game['image']) ?>" alt="Game Image">
                    <?php else: ?>
                        <p>No Image</p>
                    <?php endif; ?>
                    <h3><?= $game['title'] ?></h3>
                    <a href="review.php?id=<?= $game['id'] ?>" ><button>View More</button></a>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="add_to_cart" value="true">
                        <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                        <button type="submit">Buy Now</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No games in this category.</p>
        <?php endif; ?>
    </div>
</div>
<footer>
    <p>BTEC</p>
</footer>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>