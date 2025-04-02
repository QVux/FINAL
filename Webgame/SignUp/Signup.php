<?php
session_start();

$host = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$database = "Webgamestore"; // Database name

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Check if username contains only letters and numbers
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            echo "<script>alert('Username can only contain letters and numbers!');</script>";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!'); window.location.href = '/Webgame/Login/Login.php';</script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        }
    } else {
        echo "<script>alert('Please fill in all information!');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Webgame/CSS/signup.css">
    <title>Account Registration</title>
</head>
<body>
    <div class="signup-form">
        <h2>Account Registration</h2>
        <form method="POST" action="SignUp.php">
            <label for="username">Username:</label>
            <input type="text" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Register</button>
            <button class="back-button" onclick="window.location.href='/Webgame/Login/Login.php'">Back to Login</button>
        </form>
    </div>
</body>
</html>