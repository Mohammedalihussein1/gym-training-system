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
$msg = "";

// ADD workout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ex   = mysqli_real_escape_string($conn, $_POST['exercise']);
    $cal  = (int)$_POST['calories'];
    $dur  = (int)$_POST['duration'];
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    mysqli_query($conn, "INSERT INTO workouts (user_id, exercise, duration, calories, date) VALUES ($uid, '$ex', $dur, $cal, '$date')");
    $msg = "<div class='alert alert-success'>✓ Workout logged successfully!</div>";
}

// DELETE workout
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM workouts WHERE id=$id AND user_id=$uid");
    header("Location: log_workout.php");
    exit;
}

// SEARCH + SORT
$q    = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : "newest";
$order = $sort=="oldest" ? "date ASC" : ($sort=="calories" ? "calories DESC" : "date DESC");
$sql = "SELECT * FROM workouts WHERE user_id=$uid";
if ($q != "") $sql .= " AND exercise LIKE '%$q%'";
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
            <a href="log_workout.php" class="nav-item active">
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
                <div class="page-title">Log Workout</div>
                <div class="page-subtitle">Record your training session</div>
            </div>
            <div class="page-date" id="todayDate"><?= date('l, F j, Y') ?></div>
        </div>

        <?= $msg ?>

        <div class="two-col">

            <!-- LOG FORM -->
            <div class="card">
                <div class="card-title" style="margin-bottom:20px;">New Workout Entry</div>

                <form method="POST" onsubmit="return validateWorkout();">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="wDate" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Exercise Type</label>
                        <select id="exercise" name="exercise" required>
                            <option value="">Select exercise</option>
                            <option value="Push-ups">Push-ups</option>
                            <option value="Pull-ups">Pull-ups</option>
                            <option value="Sit-ups">Sit-ups</option>
                            <option value="Squats">Squats</option>
                            <option value="Running">Running</option>
                            <option value="Cycling">Cycling</option>
                            <option value="Swimming">Swimming</option>
                            <option value="Weight Training">Weight Training</option>
                            <option value="Yoga">Yoga</option>
                            <option value="HIIT">HIIT</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sets</label>
                            <input type="number" id="wSets" placeholder="e.g. 3" min="1" max="50">
                        </div>
                        <div class="form-group">
                            <label>Reps / Duration</label>
                            <input type="text" id="wReps" placeholder="e.g. 20 reps or 30 min">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Calories Burned (kcal)</label>
                        <input type="number" id="calories" name="calories" placeholder="e.g. 250" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" id="duration" name="duration" placeholder="e.g. 45" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Notes (optional)</label>
                        <textarea id="wNotes" placeholder="How did it go?" style="height:80px; resize:none;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary">LOG WORKOUT</button>
                </form>
            </div>

            <!-- WORKOUT HISTORY -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Workout History</div>
                        <div class="card-subtitle">Your recent sessions</div>
                    </div>
                </div>

                <form method="GET" class="search-bar">
                    <input class="search-input" type="text" name="q" placeholder="Search by exercise..." value="<?= htmlspecialchars($q) ?>">
                    <select class="search-select" name="sort">
                        <option value="newest"<?= $sort=='newest'?' selected':'' ?>>Newest First</option>
                        <option value="oldest"<?= $sort=='oldest'?' selected':'' ?>>Oldest First</option>
                        <option value="calories"<?= $sort=='calories'?' selected':'' ?>>Most Calories</option>
                    </select>
                    <button type="submit" class="btn-secondary">Search</button>
                </form>

                <ul class="exercise-list" id="workoutHistoryList">
                    <?php if (mysqli_num_rows($result) == 0): ?>
                    <li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No workouts found.</li>
                    <?php else: while($w = mysqli_fetch_assoc($result)): ?>
                    <li class="exercise-item">
                        <div>
                            <div class="exercise-item-name"><?= htmlspecialchars($w['exercise']) ?></div>
                            <div class="exercise-item-detail"><?= $w['date'] ?> · <?= $w['duration'] ?> min</div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge badge-yellow"><?= $w['calories'] ?> kcal</span>
                            <a href="?delete=<?= $w['id'] ?>" class="btn-danger" style="margin-top:6px; padding:4px 10px; font-size:12px;" onclick="return confirm('Delete?')">Delete</a>
                        </div>
                    </li>
                    <?php endwhile; endif; ?>
                </ul>
            </div>

        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>