<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));

$calBurned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(calories),0) c FROM workouts WHERE user_id=$uid AND date=CURDATE()"))['c'];
$wkWeek    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM workouts WHERE user_id=$uid"))['c'];
$calIn     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(calories),0) c FROM meals WHERE user_id=$uid AND date=CURDATE()"))['c'];

$todayWk = mysqli_query($conn, "SELECT * FROM workouts WHERE user_id=$uid AND date=CURDATE() ORDER BY id DESC");
$todayMl = mysqli_query($conn, "SELECT * FROM meals WHERE user_id=$uid AND date=CURDATE() ORDER BY id DESC");

$ini = strtoupper(substr($me['name'],0,2));
$wkPercent  = min(100, $wkWeek * 20);
$calWeekTot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(calories),0) c FROM workouts WHERE user_id=$uid"))['c'];
$calPercent = min(100, round($calWeekTot / 3500 * 100));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro — Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app-layout">

    <!-- SIDEBAR -->
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
            <a href="dashboard.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="log_workout.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                Log Workout
            </a>
            <a href="nutrition.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>
                Nutrition Log
            </a>
            <a href="progress.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                My Progress
            </a>

            <div class="nav-section-title">Profile</div>
            <a href="profile.php" class="nav-item">
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

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Dashboard</div>
                <div class="page-subtitle" id="welcomeMsg">Welcome back, <?= htmlspecialchars($me['name']) ?>!</div>
            </div>
            <div class="page-date" id="todayDate"><?= date('l, F j, Y') ?></div>
        </div>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card yellow">
                <div class="stat-icon yellow">🔥</div>
                <div class="stat-value yellow" id="totalCalories"><?= $calBurned ?></div>
                <div class="stat-label">Calories Burned Today</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red">💪</div>
                <div class="stat-value red" id="totalWorkouts"><?= $wkWeek ?></div>
                <div class="stat-label">Workouts This Week</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">⚖️</div>
                <div class="stat-value blue" id="userBMI">--</div>
                <div class="stat-label">Current BMI</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green">🥗</div>
                <div class="stat-value green" id="caloriesIn"><?= $calIn ?></div>
                <div class="stat-label">Calories Consumed Today</div>
            </div>
        </div>

        <div class="two-col">

           <!-- TODAY'S WORKOUTS -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Today's Workouts</div>
                        <div class="card-subtitle">Logged exercises for today</div>
                    </div>
                    <a href="log_workout.php" class="btn-secondary" style="font-size:13px; padding:8px 16px;">+ Log</a>
                </div>
               <ul class="exercise-list" id="todayWorkoutList">
    <?php if (mysqli_num_rows($todayWk) == 0): ?>
    <li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No workouts logged today. <a href="log_workout.php" style="color:var(--accent);">Log now →</a></li>
    <?php else: while($w = mysqli_fetch_assoc($todayWk)): ?>
    <li style="padding:12px 0; border-bottom:1px solid var(--border);">
        <strong><?= htmlspecialchars($w['exercise']) ?></strong> — <?= $w['duration'] ?> min, <?= $w['calories'] ?> kcal
    </li>
    <?php endwhile; endif; ?>
</ul>
            </div>
 <!-- TODAY'S NUTRITION -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Today's Nutrition</div>
                        <div class="card-subtitle">Meals and calories consumed</div>
                    </div>
                    <a href="nutrition.php" class="btn-secondary" style="font-size:13px; padding:8px 16px;">+ Add</a>
                </div>
                <ul class="exercise-list" id="todayNutritionList">
    <?php if (mysqli_num_rows($todayMl) == 0): ?>
    <li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No meals logged today. <a href="nutrition.php" style="color:var(--accent);">Log now →</a></li>
    <?php else: while($m = mysqli_fetch_assoc($todayMl)): ?>
    <li style="padding:12px 0; border-bottom:1px solid var(--border);">
        <strong><?= htmlspecialchars($m['name']) ?></strong> (<?= $m['type'] ?>) — <?= $m['calories'] ?> kcal
    </li>
    <?php endwhile; endif; ?>
</ul>
            </div>

        </div>

        <!-- PROFILE SUMMARY & WEEKLY GOAL -->
        <div class="two-col">

            <div class="card">
                <div class="card-title" style="margin-bottom:16px;">My Profile</div>
                <div class="profile-img-wrap" id="dashAvatar"><?= $ini ?></div>
                <table style="width:100%; font-size:14px;">
                    <tr>
                        <td style="color:var(--muted); padding:8px 0; border-bottom:1px solid var(--border);">Full Name</td>
                       <td style="text-align:right; padding:8px 0; border-bottom:1px solid var(--border); font-weight:500;" id="profileName"><?= htmlspecialchars($me['name']) ?></td>
                    </tr>
                    <tr>
                        <td style="color:var(--muted); padding:8px 0; border-bottom:1px solid var(--border);">Student ID</td>
                        <td style="text-align:right; padding:8px 0; border-bottom:1px solid var(--border);" id="profileId"><?= htmlspecialchars($me['student_id']) ?></td>
                    </tr>
                    <tr>
                        <td style="color:var(--muted); padding:8px 0; border-bottom:1px solid var(--border);">Height</td>
                        <td style="text-align:right; padding:8px 0; border-bottom:1px solid var(--border);" id="profileHeight"><?= htmlspecialchars($me['height']) ?> cm</td>
                    </tr>
                    <tr>
                        <td style="color:var(--muted); padding:8px 0;">Weight</td>
                        <td style="text-align:right; padding:8px 0; font-weight:500;" id="profileWeight"><?= htmlspecialchars($me['weight']) ?> kg</td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <div class="card-title" style="margin-bottom:16px;">Weekly Goals</div>

                <p style="font-size:13px; color:var(--muted); margin-bottom:6px;">Workouts (target: 5/week)</p>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="workoutGoalBar" style=width:<?= $wkPercent ?>%></div>
                </div>
                <p style="font-size:12px; color:var(--muted); margin-top:4px; margin-bottom:20px;" id="workoutGoalText"><?= $wkWeek ?> / 5 sessions</p>

                <p style="font-size:13px; color:var(--muted); margin-bottom:6px;">Calories Burned (target: 3,500 kcal/week)</p>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="calorieGoalBar" style=width:<?= $calPercent ?>%; background: linear-gradient(90deg, var(--accent2), #ff8a00);"></div>
                </div>
                <p style="font-size:12px; color:var(--muted); margin-top:4px;" id="calorieGoalText"><?= $calWeekTot ?> / 3,500 kcal</p>

                <div class="divider"></div>

                <div style="font-size:13px; color:var(--muted);">BMI Status: <span id="bmiStatus" style="color:var(--accent); font-weight:500;">—</span></div>
            </div>

        </div>

    </main>
</div>
<script src="script.js"></script>
</body>
</html> 