<?php
session_start();
include '../db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $error = "Email or Username already exists!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $username, $email, $hash);
        if ($stmt_insert->execute()) {
            $_SESSION['user_id'] = $stmt_insert->insert_id;
            $_SESSION['username'] = $username;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Registration failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up | ImageShare</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="signup.css">
</head>
<body>
<div class="container min-vh-100 d-flex justify-content-center align-items-center">
    <div class="card p-4" style="max-width: 380px; width: 100%;">
        <form method="POST" action="signup.php">
        <div class="text-center mb-3">
            <div class="logo">ImageShare</div>
            <h4 class="mt-2">Create your account</h4>
            <p class="text-muted">Share your creativity with the world</p>
        </div>
        <?php if($error): ?>
            <div class="alert alert-danger p-2 text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required minlength="8">
                <button class="btn btn-outline-secondary" id="togglePassword" type="button">Show</button>
            </div>
            <small>Use 8 or more letters, numbers and symbols</small>
        </div>
        <button type="submit" class="btn btn-dark w-100 mb-3 mt-3">Continue</button>
        <p class="text-center text-muted">
            Already a member? 
            <a href="login.php" class="fw-bold text-decoration-none">Log in</a>
        </p>
        </form>
    </div>
</div>
<script>
$("#togglePassword").click(function(){
    if($("#password").attr("type") == "password"){
        $("#password").attr("type","text");
        $(this).text("Hide");
    }else{
        $("#password").attr("type","password");
        $(this).text("Show");
    }
});
</script>
</body>
</html>
