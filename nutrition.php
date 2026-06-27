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

// ADD a meal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $food = mysqli_real_escape_string($conn, $_POST['food']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $cal  = (int)$_POST['calories'];
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    mysqli_query($conn, "INSERT INTO meals (user_id, name, type, calories, date) VALUES ($uid, '$food', '$type', $cal, '$date')");
    $msg = "<div class='alert alert-success'>✓ Meal logged successfully!</div>";
}

// DELETE a meal
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM meals WHERE id=$id AND user_id=$uid");
    header("Location: nutrition.php");
    exit;
}

// SEARCH + FILTER
$q  = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$tf = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : "all";
$sql = "SELECT * FROM meals WHERE user_id=$uid";
if ($q != "")     $sql .= " AND name LIKE '%$q%'";
if ($tf != "all") $sql .= " AND type='$tf'";
$sql .= " ORDER BY date DESC, id DESC";
$result = mysqli_query($conn, $sql);

// today's total calories
$totalCal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(calories),0) c FROM meals WHERE user_id=$uid AND date=CURDATE()"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro — Nutrition Log</title>
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
            <a href="dashboard.html" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="log_workout.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                Log Workout
            </a>
            <a href="nutrition.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>
                Nutrition Log
            </a>
            <a href="progress.html" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    
                 My Progress
            </a>
            <div class="nav-section-title">Profile</div>
            <a href="profile.html" class="nav-item">
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
                <div class="page-title">Nutrition Log</div>
                <div class="page-subtitle">Track what you eat today</div>
            </div>
            <div class="page-date" id="todayDate"><?= date('l, F j, Y') ?></div>
        </div>

        <!-- SUMMARY STATS -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card green">
                <div class="stat-icon green">🥗</div>
                <div class="stat-value green" id="totalCalIn"><?= $totalCal ?></div>
                <div class="stat-label">Total Calories In (kcal)</div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-icon yellow">🍗</div>
                <div class="stat-value yellow" id="totalProtein">0g</div>
                <div class="stat-label">Total Protein</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">🍞</div>
                <div class="stat-value blue" id="totalCarbs">0g</div>
                <div class="stat-label">Total Carbs</div>
            </div>
        </div>

        <?= $msg ?>

        <div class="two-col">

            <!-- LOG FORM -->
            <div class="card">
                <div class="card-title" style="margin-bottom:20px;">Log a Meal</div>

                <form method="POST" onsubmit="return validateMeal();">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="nDate" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Meal Type</label>
                        <select id="nMealType" name="type" required>
                            <option value="">Select meal</option>
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Dinner">Dinner</option>
                            <option value="Snack">Snack</option>
                            <option value="Pre-workout">Pre-workout</option>
                            <option value="Post-workout">Post-workout</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Food Description</label>
                        <input type="text" id="mealName" name="food" placeholder="e.g. Grilled chicken with rice" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Calories (kcal)</label>
                            <input type="number" id="mealCal" name="calories" placeholder="e.g. 450" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>Protein (g)</label>
                            <input type="number" id="nProtein" placeholder="e.g. 30" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Carbs (g)</label>
                            <input type="number" id="nCarbs" placeholder="e.g. 60" min="0">
                        </div>
                        <div class="form-group">
                            <label>Fats (g)</label>
                            <input type="number" id="nFats" placeholder="e.g. 10" min="0">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">LOG MEAL</button>
                </form>
            </div>
            <!-- MEAL HISTORY -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Meal History</div>
                        <div class="card-subtitle">All your logged meals</div>
                    </div>
                </div>

                <form method="GET" class="search-bar">
                    <input class="search-input" type="text" name="q" placeholder="Search by food..." value="<?= htmlspecialchars($q) ?>">
                    <select class="search-select" name="type">
                        <option value="all"<?= $tf=='all'?' selected':'' ?>>All Meals</option>
                        <option value="Breakfast"<?= $tf=='Breakfast'?' selected':'' ?>>Breakfast</option>
                        <option value="Lunch"<?= $tf=='Lunch'?' selected':'' ?>>Lunch</option>
                        <option value="Dinner"<?= $tf=='Dinner'?' selected':'' ?>>Dinner</option>
                        <option value="Snack"<?= $tf=='Snack'?' selected':'' ?>>Snack</option>
                    </select>
                    <button type="submit" class="btn-secondary">Search</button>
                </form>

                <ul class="exercise-list" id="mealHistoryList">
                    <?php if (mysqli_num_rows($result) == 0): ?>
                    <li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No meals found.</li>
                    <?php else: while($m = mysqli_fetch_assoc($result)): ?>
                    <li class="exercise-item">
                        <div>
                            <div class="exercise-item-name"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="exercise-item-detail"><?= $m['date'] ?> · <?= $m['type'] ?></div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge badge-green"><?= $m['calories'] ?> kcal</span>
                            <a href="?delete=<?= $m['id'] ?>" class="btn-danger" style="margin-top:6px; padding:4px 10px; font-size:12px;" onclick="return confirm('Delete?')">Delete</a>
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
