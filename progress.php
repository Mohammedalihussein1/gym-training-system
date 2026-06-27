<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));
$ini = strtoupper(substr($me['name'],0,2));

// DELETE workout
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM workouts WHERE id=$id AND user_id=$uid");
    header("Location: progress.php");
    exit;
}

// totals
$tot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c, COALESCE(SUM(calories),0) cal, COALESCE(SUM(duration),0) dur, COUNT(DISTINCT date) days FROM workouts WHERE user_id=$uid"));

// search + sort + date
$q    = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : "newest";
$df   = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : "";
$order = $sort=="oldest" ? "date ASC" : ($sort=="calories" ? "calories DESC" : ($sort=="duration" ? "duration DESC" : "date DESC"));
$sql = "SELECT * FROM workouts WHERE user_id=$uid";
if ($q != "")  $sql .= " AND exercise LIKE '%$q%'";
if ($df != "") $sql .= " AND date='$df'";
$sql .= " ORDER BY $order";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
            <a href="progress.php" class="nav-item active">
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

    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">My Progress</div>
                <div class="page-subtitle">Full history of your training sessions</div>
            </div>
            <div class="page-date" id="todayDate"><?= date('l, F j, Y') ?></div>
        </div>

        <!-- OVERALL STATS -->
        <div class="stats-grid">
            <div class="stat-card yellow">
                <div class="stat-icon yellow">🔥</div>
                <div class="stat-value yellow" id="allCalories"><?= $tot['cal'] ?></div>
                <div class="stat-label">Total Calories Burned</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red">💪</div>
                <div class="stat-value red" id="allSessions"><?= $tot['c'] ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">⏱️</div>
                <div class="stat-value blue" id="allMinutes"><?= $tot['dur'] ?></div>
                <div class="stat-label">Total Minutes Trained</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green">📅</div>
                <div class="stat-value green" id="allDays"><?= $tot['days'] ?></div>
                <div class="stat-label">Active Days</div>
            </div>
        </div>

        <!-- FULL TABLE -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">All Workout Sessions</div>
                    <div class="card-subtitle">Sorted by date</div>
                </div>
            </div>

            <form method="GET" class="search-bar">
                <input class="search-input" type="text" name="q" placeholder="Search by exercise..." value="<?= htmlspecialchars($q) ?>">
                <select class="search-select" name="sort">
                    <option value="newest"<?= $sort=='newest'?' selected':'' ?>>Newest First</option>
                    <option value="oldest"<?= $sort=='oldest'?' selected':'' ?>>Oldest First</option>
                    <option value="calories"<?= $sort=='calories'?' selected':'' ?>>Most Calories</option>
                    <option value="duration"<?= $sort=='duration'?' selected':'' ?>>Longest Duration</option>
                </select>
                <input class="search-input" type="date" name="date" value="<?= htmlspecialchars($df) ?>" style="max-width:160px;">
                <button type="submit" class="btn-secondary">Filter</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Exercise</th>
                            <th>Sets</th>
                            <th>Reps / Duration</th>
                            <th>Duration (min)</th>
                            <th>Calories</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="progressTableBody">
                        <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr><td colspan="9" style="text-align:center; color:var(--muted); padding:30px;">No workouts found.</td></tr>
                        <?php else: $i=1; while($w = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td style="color:var(--muted)"><?= $i++ ?></td>
                            <td><?= $w['date'] ?></td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($w['exercise']) ?></span></td>
                            <td>-</td>
                            <td>-</td>
                            <td><?= $w['duration'] ?> min</td>
                            <td><span class="badge badge-yellow"><?= $w['calories'] ?> kcal</span></td>
                            <td style="color:var(--muted); font-size:13px;">—</td>
                            <td><a href="?delete=<?= $w['id'] ?>" class="btn-danger" style="padding:4px 10px; font-size:12px;" onclick="return confirm('Delete?')">Delete</a></td>
                        </tr>
                        <?php endwhile; endif; ?>
                </table>
            </div>

        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>