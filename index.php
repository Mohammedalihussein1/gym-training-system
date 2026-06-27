<?php
session_start();
require "db.php";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'student') { header("Location: dashboard.php"); }
    else { header("Location: admin.php"); }
    exit;
}

$error = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = md5($_POST['password']);
    $res  = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    if (mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role']    = $row['role'];
        $_SESSION['name']    = $row['name'];
        if (isset($_POST['remember'])) setcookie("fit_user", $row['username'], time()+60*60*24*30, "/");
        if ($row['role'] == 'student') { header("Location: dashboard.php"); }
        else { header("Location: admin.php"); }
        exit;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro — Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">

    <!-- LEFT PANEL -->
    <div class="auth-left">
        <div class="auth-brand">
            FitTrack
            <span>TRAINING MANAGEMENT</span>
        </div>
        <p class="auth-tagline">
            Track your workouts, monitor your progress, and achieve your fitness goals every single day.
        </p>
        <div class="auth-stats">
            <div>
                <div class="auth-stat-num">100+</div>
                <div class="auth-stat-label">Students</div>
            </div>
            <div>
                <div class="auth-stat-num">500+</div>
                <div class="auth-stat-label">Sessions</div>
            </div>
            <div>
                <div class="auth-stat-num">30+</div>
                <div class="auth-stat-label">Exercises</div>
            </div>
        </div>
    </div>
 <!-- RIGHT PANEL -->
    <div class="auth-right">
        <div class="auth-form-box">

            <h2>Welcome Back</h2>
            <p>Sign in to your training account</p>

           <?php if ($error): ?>
<div class="alert alert-error">
    ⚠ Invalid username or password.
</div>
<?php endif; ?>

            <form method="POST" onsubmit="return validateLogin();">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="loginUser" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="loginPass" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:8px;">
    <input type="checkbox" name="remember" style="width:auto;">
    <label style="margin:0;">Remember me</label>
</div>
                <button type="submit" class="btn-primary">LOGIN</button>
            </form>

            <div class="auth-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>

            <div style="margin-top: 32px; padding: 16px; background: var(--bg2); border-radius: 10px; border: 1px solid var(--border);">
                <p style="font-size: 12px; color: var(--muted); margin-bottom: 10px; letter-spacing: 1px; text-transform: uppercase;">Demo Accounts</p>
                <p style="font-size: 13px; margin-bottom: 6px;">👤 Admin: <span style="color:var(--accent)">admin</span> / <span style="color:var(--accent)">admin123</span></p>
                <p style="font-size: 13px; margin-bottom: 6px;">🎓 Coordinator: <span style="color:var(--accent)">coord</span> / <span style="color:var(--accent)">coord123</span></p>
                <p style="font-size: 13px;">🏋️ Student: <span style="color:var(--accent)">student</span> / <span style="color:var(--accent)">student123</span></p>
            </div>

        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
