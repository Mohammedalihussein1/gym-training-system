<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$msg = "";

// SAVE profile changes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $age     = (int)$_POST['age'];
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $height  = (int)$_POST['height'];
    $weight  = (int)$_POST['weight'];
    $program = mysqli_real_escape_string($conn, $_POST['program']);

    mysqli_query($conn, "UPDATE users SET name='$name', age=$age, gender='$gender', height=$height, weight=$weight, program='$program' WHERE id=$uid");
    $_SESSION['name'] = $name;
    $msg = "<div class='alert alert-success'>✓ Profile updated successfully!</div>";
}

// load my data
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));
$ini = strtoupper(substr($me['name'],0,2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro — My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-text">FitTrack</div>
            <div class="sidebar-logo-sub">TRAINING MANAGEMENT</div>
        </div>
        <div class="sidebar-user">
            <div class="avatar" id="sidebarAvatar"><?= $ini ?></div>
            <div>
                <div class="sidebar-user-name" id="sidebarName"><?= htmlspecialchars($me['name']) ?></div>
                <div class="sidebar-user-role">Student</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="log_workout.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                Log Workout
            </a>
            <a href="nutrition.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>
                Nutrition Log
            </a>
            <a href="progress.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                My Progress
            </a>
            <div class="nav-section-title">Profile</div>
            <a href="profile.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                My Profile
            </a>
        </nav>
        <div class="sidebar-bottom">
            <a href="logout.php" class="nav-item btn-secondary" style="width:100%; justify-content:flex-start; border:none; padding:11px 14px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">My Profile</div>
                <div class="page-subtitle">View and update your information</div>
            </div>
        </div>

        <?= $msg ?>

        <div class="two-col">
<!-- EDIT FORM -->
            <div class="card">
                <div class="card-title" style="margin-bottom:20px;">Edit Profile</div>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="editName" name="name" value="<?= htmlspecialchars($me['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Student ID (cannot be changed)</label>
                        <input type="text" id="editId" value="<?= htmlspecialchars($me['student_id']) ?>" disabled style="opacity:0.5; cursor:not-allowed;">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" id="editAge" name="age" value="<?= $me['age'] ?>" min="10" max="100">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select id="editGender" name="gender">
                                <option value="Male"<?= $me['gender']=='Male'?' selected':'' ?>>Male</option>
                                <option value="Female"<?= $me['gender']=='Female'?' selected':'' ?>>Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" id="editHeight" name="height" value="<?= $me['height'] ?>" min="100" max="250">
                        </div>
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" id="editWeight" name="weight" value="<?= $me['weight'] ?>" min="30" max="300">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Program / Department</label>
                        <input type="text" id="editProgram" name="program" value="<?= htmlspecialchars($me['program']) ?>">
                    </div>
                    <button type="submit" class="btn-primary">SAVE CHANGES</button>
                </form>
            </div>

        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>
