<?php
session_start();
include '../db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            $token = bin2hex(random_bytes(32));
            $update_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $token, $user['id']);
            $update_stmt->execute();
            
            setcookie('remember_token', $token, time() + (86400 * 7), "/");
            
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | ImageShare</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="logo">ImageShare</h2>
        <h3>Welcome back</h3>
        <p class="subtitle">Log in to your account</p>

        <?php if($error): ?>
            <p style="color:red; text-align:center;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label>Email address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <div class="password-box">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <span onclick="togglePassword()">Show</span>
            </div>

            <button type="submit">Log in</button>
        </form>

        <p class="bottom-text">
            Don’t have an account?
            <a href="signup.php">Sign up</a>
        </p>
    </div>
</div>

<script src="login.js"></script>
</body>
</html>