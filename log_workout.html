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
            <div class="avatar" id="sidebarAvatar">MU</div>
            <div>
                <div class="sidebar-user-name" id="sidebarName">Student</div>
                <div class="sidebar-user-role">Student</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.html" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="log_workout.html" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                Log Workout
            </a>
            <a href="nutrition.html" class="nav-item">
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
            <button class="nav-item btn-secondary" onclick="logout()" style="width:100%; justify-content:flex-start; border:none; padding:11px 14px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </button>
        </div>
    </aside>

    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Log Workout</div>
                <div class="page-subtitle">Record your training session</div>
            </div>
            <div class="page-date" id="todayDate"></div>
        </div>

        <div id="successMsg" class="alert alert-success" style="display:none;">✓ Workout logged successfully!</div>
        <div id="errorMsg" class="alert alert-error" style="display:none;"></div>

        <div class="two-col">

            <!-- LOG FORM -->
            <div class="card">
                <div class="card-title" style="margin-bottom:20px;">New Workout Entry</div>

                <form id="workoutForm">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="wDate" required>
                    </div>
                    <div class="form-group">
                        <label>Exercise Type</label>
                        <select id="wType" required>
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
                        <input type="number" id="wCalories" placeholder="e.g. 250" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" id="wDuration" placeholder="e.g. 45" min="1" required>
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

                <div class="search-bar">
                    <input class="search-input" type="text" id="searchWorkout" placeholder="Search by exercise..." oninput="filterWorkouts()">
                    <select class="search-select" id="sortWorkout" onchange="filterWorkouts()">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="calories">Most Calories</option>
                    </select>
                </div>

                <ul class="exercise-list" id="workoutHistoryList">
                    <li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No workouts logged yet.</li>
                </ul>
            </div>

        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>