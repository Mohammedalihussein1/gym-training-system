<?php
session_start();
require "db.php";

// only admin and coordinator
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'coordinator')) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'];
$av   = strtoupper(substr($_SESSION['name'],0,2));

// approve / reject application
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    mysqli_query($conn, "UPDATE applications SET status='approved' WHERE id=$id");
    header("Location: admin.php"); exit;
}
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    mysqli_query($conn, "UPDATE applications SET status='rejected' WHERE id=$id");
    header("Location: admin.php"); exit;
}

// stats
$totStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='student'"))['c'];
$totSessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM workouts"))['c'];
$totCal      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(calories),0) c FROM workouts"))['c'];
$totMeals    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM meals"))['c'];

// search + sort students
$q    = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : "az";
$order = $sort=="za" ? "u.name DESC" : ($sort=="sessions" ? "sessions DESC" : "u.name ASC");

// students with session count + calories (multi-table)
$sql = "SELECT u.id, u.name, u.student_id, u.program, u.height, u.weight,
               COUNT(w.id) AS sessions,
               COALESCE(SUM(w.calories),0) AS calories
        FROM users u
        LEFT JOIN workouts w ON w.user_id = u.id
        WHERE u.role='student'";
if ($q != "") $sql .= " AND (u.name LIKE '%$q%' OR u.student_id LIKE '%$q%')";
$sql .= " GROUP BY u.id ORDER BY $order";
$students = mysqli_query($conn, $sql);

// recent activity (JOIN workouts + users)
$recent = mysqli_query($conn, "SELECT w.*, u.name FROM workouts w JOIN users u ON w.user_id=u.id ORDER BY w.date DESC, w.id DESC LIMIT 10");

// applications (JOIN applications + users + programs)
$apps = mysqli_query($conn, "SELECT a.*, u.name AS student, u.student_id, p.name AS program
                             FROM applications a
                             JOIN users u ON a.user_id=u.id
                             JOIN programs p ON a.program_id=p.id
                             ORDER BY a.status, a.date DESC");
?>
<!DOCTYPE html>  
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
<div class="app-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-text">FitTrack</div>
            <div class="sidebar-logo-sub">ADMIN PANEL</div>
        </div>
        <div class="sidebar-user">
            <div class="avatar" style="background:var(--accent2);"><?= $av ?></div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                <div class="sidebar-user-role"><span class="admin-badge"><?= strtoupper($role) ?></span></div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Admin</div>
            <a href="admin.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Overview
            </a>
            <a href="admin.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Manage Students
            </a>
            <a href="profile.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Reports
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
                <div class="page-title">Admin Overview</div>
                <div class="page-subtitle">System-wide statistics</div>
            </div>
            <div class="page-date" id="todayDate"><?= date('l, F j, Y') ?></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card yellow">
                <div class="stat-icon yellow">👥</div>
                <div class="stat-value yellow" id="totalStudents"><?= $totStudents ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red">🏋️</div>
                <div class="stat-value red" id="totalSessions"><?= $totSessions ?></div>
                <div class="stat-label">Total Sessions Logged</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">🔥</div>
                <div class="stat-value blue" id="totalCalBurned"><?= $totCal ?></div>
                <div class="stat-label">Total Calories Burned</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green">🥗</div>
                <div class="stat-value green" id="totalMeals"><?= $totMeals ?></div>
                <div class="stat-label">Total Meals Logged</div>
            </div>
        </div>

        <!-- ALL STUDENTS TABLE -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">All Students</div>
                    <div class="card-subtitle">Registered students overview</div>
                </div>
                <a href="profile.php" class="btn-secondary" style="font-size:13px;">My Profile →</a>
            </div>

            <form method="GET" class="search-bar">
                <input class="search-input" type="text" name="q" placeholder="Search by name or ID..." value="<?= htmlspecialchars($q) ?>">
                <select class="search-select" name="sort">
                    <option value="az"<?= $sort=='az'?' selected':'' ?>>A → Z</option>
                    <option value="za"<?= $sort=='za'?' selected':'' ?>>Z → A</option>
                    <option value="sessions"<?= $sort=='sessions'?' selected':'' ?>>Most Sessions</option>
                </select>
                <button type="submit" class="btn-secondary">Search</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Student ID</th>
                            <th>Program</th>
                            <th>Sessions</th>
                            <th>Calories Burned</th>
                            <th>BMI</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="adminStudentTable">
                        <?php if (mysqli_num_rows($students) == 0): ?>
                        <tr><td colspan="8" style="text-align:center; color:var(--muted); padding:30px;">No students found.</td></tr>
                        <?php else: $i=1; while($s = mysqli_fetch_assoc($students)):
                            $bmi = ($s['height'] > 0) ? round($s['weight'] / (($s['height']/100)*($s['height']/100)), 1) : '-';
                            $status = '-';
                            if ($bmi != '-') {
                                if ($bmi < 18.5) $status = 'Underweight';
                                elseif ($bmi < 25) $status = 'Normal';
                                elseif ($bmi < 30) $status = 'Overweight';
                                else $status = 'Obese';
                            }
                        ?>
                        <tr>
                            <td style="color:var(--muted)"><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td style="color:var(--muted)"><?= htmlspecialchars($s['student_id']) ?></td>
                            <td><?= htmlspecialchars($s['program']) ?></td>
                            <td><span class="badge badge-blue"><?= $s['sessions'] ?></span></td>
                            <td><span class="badge badge-yellow"><?= $s['calories'] ?> kcal</span></td>
                            <td><?= $bmi ?></td>
                            <td><span class="badge badge-green"><?= $status ?></span></td>
                        </tr>
                        <?php endwhile; endif; ?>
                </table>
            </div>
        </div>

        <!-- RECENT ACTIVITY -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Recent Activity</div>
                    <div class="card-subtitle">Latest workout logs across all students</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exercise</th>
                            <th>Date</th>
                            <th>Calories</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody id="recentActivityTable">
                        <?php if (mysqli_num_rows($recent) == 0): ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--muted); padding:30px;">No activity yet.</td></tr>
                        <?php else: while($r = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($r['exercise']) ?></span></td>
                            <td><?= $r['date'] ?></td>
                            <td><span class="badge badge-yellow"><?= $r['calories'] ?> kcal</span></td>
                            <td><?= $r['duration'] ?> min</td>
                        </tr>
                        <?php endwhile; endif; ?>
                </table>
            </div>
        </div>

        <!-- PROGRAM APPLICATIONS (approve / reject) -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Program Applications</div>
                    <div class="card-subtitle">Approve or reject student applications</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Student</th><th>Student ID</th><th>Program</th><th>Date</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($apps) == 0): ?>
                        <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:30px;">No applications.</td></tr>
                        <?php else: while($a = mysqli_fetch_assoc($apps)):
                            $cls = $a['status']=='approved'?'badge-green':($a['status']=='rejected'?'badge-red':'badge-yellow'); ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['student']) ?></strong></td>
                            <td style="color:var(--muted)"><?= htmlspecialchars($a['student_id']) ?></td>
                            <td><?= htmlspecialchars($a['program']) ?></td>
                            <td><?= $a['date'] ?></td>
                            <td><span class="badge <?= $cls ?>"><?= strtoupper($a['status']) ?></span></td>
                            <td>
                                <?php if ($a['status'] == 'pending'): ?>
                                    <a href="?approve=<?= $a['id'] ?>" class="btn-secondary" style="font-size:12px; padding:5px 10px;">Approve</a>
                                    <a href="?reject=<?= $a['id'] ?>" class="btn-danger" style="font-size:12px; padding:5px 10px;">Reject</a>
                                <?php else: echo '—'; endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>