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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $hashed_password);
                $stmt->fetch();
                
                if (password_verify($password, $hashed_password)) {
                    // Save information to SESSION
                    $_SESSION["user_id"] = $user_id;
                    $_SESSION["username"] = $username;

                    // Determine role (admin or member)
                    if (stripos($username, "admin") === 0) {
                        $_SESSION["role"] = "admin";
                    } else {
                        $_SESSION["role"] = "member";
                    }

                    header("Location: ../index.php"); // Redirect after successful login
                    exit();
                } else {
                    echo "<p style='color: red;'>Incorrect password!</p>";
                }
            } else {
                echo "<p style='color: red;'>Account does not exist!</p>";
            }
            
            $stmt->close();
        } else {
            echo "<p style='color: red;'>Query error!</p>";
        }
    } else {
        echo "<p style='color: red;'>Please fill in all information!</p>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Webgame/CSS/Login.css">
    <title>Login</title>
</head>
<body>
<div class="Login">
    <h2>Login</h2>
    <form method="POST" action="Login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Login</button>
    </form>
</div>

<div class="register-container">
    <p class="register-link">Don't have an account? <a href="/Webgame/SignUp/Signup.php">Register now</a></p>
</div>
</body>
</html>