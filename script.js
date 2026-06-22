// FITTRACK PRO — script.js
// Handles: auth, workout log, nutrition, progress, admin
// ============================================================

// ---- DEMO USERS (will be replaced by PHP + MySQL later) ----
const DEMO_USERS = [
    { username: 'admin',   password: 'admin123',   role: 'admin',       name: 'Admin',           id: 'ADM001', program: 'Administration', age: 30, height: 175, weight: 70 },
    { username: 'coord',   password: 'coord123',   role: 'coordinator', name: 'Coordinator Ali', id: 'COO001', program: 'Coordination',    age: 35, height: 180, weight: 80 },
    { username: 'student', password: 'student123', role: 'student',     name: 'Muhammad Ali',    id: 'STU001', program: 'Computer Science', age: 21, height: 175, weight: 70 },
];

// ============================================================
// UTILS
// ============================================================
function today() {
    return new Date().toISOString().split('T')[0];
}

function formatDate(d) {
    if (!d) return '';
    const dt = new Date(d + 'T00:00:00');
    return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function calcBMI(weight, height) {
    if (!weight || !height) return '--';
    return (weight / ((height / 100) ** 2)).toFixed(1);
}

function bmiStatus(bmi) {
    const b = parseFloat(bmi);
    if (isNaN(b)) return '—';
    if (b < 18.5) return 'Underweight';
    if (b < 25)   return 'Normal';
    if (b < 30)   return 'Overweight';
    return 'Obese';
}

function initials(name) {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
}

function showDate() {
    const el = document.getElementById('todayDate');
    if (el) el.textContent = new Date().toLocaleDateString('en-GB', { weekday:'long', day:'2-digit', month:'long', year:'numeric' });
}

// ============================================================
// AUTH
// ============================================================
function getUsers() {
    const stored = JSON.parse(localStorage.getItem('ft_users')) || [];
    // Merge with demo users (avoid duplicates)
    const all = [...DEMO_USERS];
    stored.forEach(u => { if (!all.find(a => a.username === u.username)) all.push(u); });
    return all;
}

function saveNewUser(user) {
    const stored = JSON.parse(localStorage.getItem('ft_users')) || [];
    stored.push(user);
    localStorage.setItem('ft_users', JSON.stringify(stored));
}

function getCurrentUser() {
    return JSON.parse(sessionStorage.getItem('ft_current'));
}

function requireLogin(allowedRoles) {
    const user = getCurrentUser();
    if (!user) { window.location.href = 'index.html'; return null; }
    if (allowedRoles && !allowedRoles.includes(user.role)) {
        alert('Access denied.');
        window.location.href = 'index.html';
        return null;
    }
    return user;
}

function logout() {
    sessionStorage.removeItem('ft_current');
    window.location.href = 'index.html';
}

// LOGIN FORM
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const username = document.getElementById('loginUser').value.trim();
        const password = document.getElementById('loginPass').value;
        const users = getUsers();
        const user = users.find(u => u.username === username && u.password === password);
        if (user) {
            sessionStorage.setItem('ft_current', JSON.stringify(user));
            if (user.role === 'admin' || user.role === 'coordinator') {
                window.location.href = 'admin.html';
            } else {
                window.location.href = 'dashboard.html';
            }
        } else {
            document.getElementById('errorMsg').style.display = 'flex';
        }
    });
}

// REGISTER FORM
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const errEl = document.getElementById('errorMsg');
        const name     = document.getElementById('regName').value.trim();
        const id       = document.getElementById('regId').value.trim();
        const username = document.getElementById('regUser').value.trim();
        const password = document.getElementById('regPass').value;
        const age      = parseInt(document.getElementById('regAge').value);
        const gender   = document.getElementById('regGender').value;
        const height   = parseInt(document.getElementById('regHeight').value);
        const weight   = parseInt(document.getElementById('regWeight').value);
        const program  = document.getElementById('regProgram').value.trim();

        // Validation
        if (!name || !username || !password || !id || !program) {
            errEl.textContent = '⚠ Please fill in all required fields.';
            errEl.style.display = 'flex'; return;
        }
        if (password.length < 6) {
            errEl.textContent = '⚠ Password must be at least 6 characters.';
            errEl.style.display = 'flex'; return;
        }
        const users = getUsers();
        if (users.find(u => u.username === username)) {
            errEl.textContent = '⚠ Username already exists.';
            errEl.style.display = 'flex'; return;
        }

        const newUser = { username, password, role: 'student', name, id, program, age, gender, height, weight };
        saveNewUser(newUser);

        errEl.style.display = 'none';
        document.getElementById('successMsg').style.display = 'flex';
        setTimeout(() => window.location.href = 'index.html', 1500);
    });
}

// ============================================================
// SIDEBAR INIT
// ============================================================
function initSidebar() {
    const user = getCurrentUser();
    if (!user) return;
    const av = document.getElementById('sidebarAvatar');
    const nm = document.getElementById('sidebarName');
    if (av) av.textContent = initials(user.name);
    if (nm) nm.textContent = user.name;
}

// ============================================================
// WORKOUT LOG
// ============================================================
function getWorkouts(username) {
    return JSON.parse(localStorage.getItem('ft_workouts_' + username)) || [];
}

function saveWorkouts(username, data) {
    localStorage.setItem('ft_workouts_' + username, JSON.stringify(data));
}

const workoutForm = document.getElementById('workoutForm');
if (workoutForm) {
    const user = requireLogin(['student']);
    if (user) {
        initSidebar();
        showDate();
        document.getElementById('wDate').value = today();
        renderWorkoutHistory(user.username);

        workoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const entry = {
                id: Date.now(),
                date:     document.getElementById('wDate').value,
                type:     document.getElementById('wType').value,
                sets:     document.getElementById('wSets').value || '-',
                reps:     document.getElementById('wReps').value || '-',
                calories: parseInt(document.getElementById('wCalories').value),
                duration: parseInt(document.getElementById('wDuration').value),
                notes:    document.getElementById('wNotes').value
            };
            const workouts = getWorkouts(user.username);
            workouts.push(entry);
            saveWorkouts(user.username, workouts);
            workoutForm.reset();
            document.getElementById('wDate').value = today();
            document.getElementById('successMsg').style.display = 'flex';
            setTimeout(() => document.getElementById('successMsg').style.display = 'none', 2000);
            renderWorkoutHistory(user.username);
        });
    }
}
function renderWorkoutHistory(username, filter = '', sort = 'newest') {
    const list = document.getElementById('workoutHistoryList');
    if (!list) return;
    let workouts = getWorkouts(username);

    if (filter) workouts = workouts.filter(w => w.type.toLowerCase().includes(filter.toLowerCase()));
    if (sort === 'newest')   workouts.sort((a,b) => b.date.localeCompare(a.date));
    if (sort === 'oldest')   workouts.sort((a,b) => a.date.localeCompare(b.date));
    if (sort === 'calories') workouts.sort((a,b) => b.calories - a.calories);

    if (workouts.length === 0) {
        list.innerHTML = '<li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No workouts found.</li>';
        return;
    }
    list.innerHTML = workouts.slice().reverse().map(w => `
        <li class="exercise-item">
            <div>
                <div class="exercise-item-name">${w.type}</div>
                <div class="exercise-item-detail">${formatDate(w.date)} · ${w.sets} sets · ${w.reps}</div>
            </div>
            <div style="text-align:right;">
                <span class="badge badge-yellow">${w.calories} kcal</span>
                <div style="font-size:12px; color:var(--muted); margin-top:4px;">${w.duration} min</div>
                <button onclick="deleteWorkout('${w.id}', '${username}')" class="btn-danger" style="margin-top:6px; padding:4px 10px; font-size:12px;">Delete</button>
            </div>
        </li>
    `).join('');
}

function deleteWorkout(id, username) {
    if (!confirm('Delete this workout?')) return;
    let workouts = getWorkouts(username);
    workouts = workouts.filter(w => w.id != id);
    saveWorkouts(username, workouts);
    renderWorkoutHistory(username);
}

function filterWorkouts() {
    const user = getCurrentUser();
    if (!user) return;
    const filter = document.getElementById('searchWorkout').value;
    const sort   = document.getElementById('sortWorkout').value;
    renderWorkoutHistory(user.username, filter, sort);
}

// ============================================================
// NUTRITION LOG
// ============================================================
function getMeals(username) {
    return JSON.parse(localStorage.getItem('ft_meals_' + username)) || [];
}

function saveMeals(username, data) {
    localStorage.setItem('ft_meals_' + username, JSON.stringify(data));
}

const nutritionForm = document.getElementById('nutritionForm');
if (nutritionForm) {
    const user = requireLogin(['student']);
    if (user) {
        initSidebar();
        showDate();
        document.getElementById('nDate').value = today();
        renderNutritionStats(user.username);
        renderMealHistory(user.username);

        nutritionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const meal = {
                id:       Date.now(),
                date:     document.getElementById('nDate').value,
                mealType: document.getElementById('nMealType').value,
                food:     document.getElementById('nFood').value,
                calories: parseInt(document.getElementById('nCalories').value),
                protein:  parseInt(document.getElementById('nProtein').value) || 0,
                carbs:    parseInt(document.getElementById('nCarbs').value)    || 0,
                fats:     parseInt(document.getElementById('nFats').value)     || 0
            };
            const meals = getMeals(user.username);
            meals.push(meal);
            saveMeals(user.username, meals);
            nutritionForm.reset();
            document.getElementById('nDate').value = today();
            document.getElementById('successMsg').style.display = 'flex';
            setTimeout(() => document.getElementById('successMsg').style.display = 'none', 2000);
            renderNutritionStats(user.username);
            renderMealHistory(user.username);
        });
    }
}

function renderNutritionStats(username) {
    const todayStr = today();
    const meals = getMeals(username).filter(m => m.date === todayStr);
    const totalCal  = meals.reduce((s, m) => s + m.calories, 0);
    const totalProt = meals.reduce((s, m) => s + m.protein, 0);
    const totalCarb = meals.reduce((s, m) => s + m.carbs, 0);
    const el1 = document.getElementById('totalCalIn');
    const el2 = document.getElementById('totalProtein');
    const el3 = document.getElementById('totalCarbs');
    if (el1) el1.textContent = totalCal;
    if (el2) el2.textContent = totalProt + 'g';
    if (el3) el3.textContent = totalCarb + 'g';
}

function renderMealHistory(username, filter = '', typeFilter = 'all') {
    const list = document.getElementById('mealHistoryList');
    if (!list) return;
    let meals = getMeals(username);
    if (filter)             meals = meals.filter(m => m.food.toLowerCase().includes(filter.toLowerCase()));
    if (typeFilter !== 'all') meals = meals.filter(m => m.mealType === typeFilter);
    meals.sort((a,b) => b.date.localeCompare(a.date));

    if (meals.length === 0) {
        list.innerHTML = '<li style="color:var(--muted); font-size:14px; text-align:center; padding:20px 0;">No meals found.</li>';
        return;
    }
    list.innerHTML = meals.map(m => `
        <li class="exercise-item">
            <div>
                <div class="exercise-item-name">${m.food}</div>
                <div class="exercise-item-detail">${formatDate(m.date)} · ${m.mealType}</div>
                <div class="exercise-item-detail">Protein: ${m.protein}g · Carbs: ${m.carbs}g · Fats: ${m.fats}g</div>
            </div>
            <div style="text-align:right;">
                <span class="badge badge-green">${m.calories} kcal</span>
                <button onclick="deleteMeal('${m.id}', '${username}')" class="btn-danger" style="margin-top:6px; padding:4px 10px; font-size:12px;">Delete</button>
            </div>
        </li>
    `).join('');
}

function deleteMeal(id, username) {
    if (!confirm('Delete this meal?')) return;
    let meals = getMeals(username);
    meals = meals.filter(m => m.id != id);
    saveMeals(username, meals);
    renderNutritionStats(username);
    renderMealHistory(username);
}

function filterMeals() {
    const user = getCurrentUser();
    if (!user) return;
    const filter = document.getElementById('searchMeal').value;
    const type   = document.getElementById('filterMealType').value;
    renderMealHistory(user.username, filter, type);
}
// DASHBOARD
// ============================================================
const dashboardPage = document.getElementById('todayDate');
if (dashboardPage && document.getElementById('totalCalories')) {
    const user = requireLogin(['student']);
    if (user) {
        initSidebar();
        showDate();

        // Profile info
        document.getElementById('welcomeMsg').textContent = 'Welcome back, ' + user.name + '!';
        document.getElementById('profileName').textContent   = user.name;
        document.getElementById('profileId').textContent     = user.id;
        document.getElementById('profileHeight').textContent = user.height + ' cm';
        document.getElementById('profileWeight').textContent = user.weight + ' kg';
        document.getElementById('dashAvatar').textContent    = initials(user.name);

        const bmi = calcBMI(user.weight, user.height);
        document.getElementById('userBMI').textContent   = bmi;
        document.getElementById('bmiStatus').textContent = bmiStatus(bmi);

        // Today's stats
        const todayStr   = today();
        const workouts   = getWorkouts(user.username).filter(w => w.date === todayStr);
        const meals      = getMeals(user.username).filter(m => m.date === todayStr);
        const calBurned  = workouts.reduce((s, w) => s + w.calories, 0);
        const calIn      = meals.reduce((s, m) => s + m.calories, 0);

        document.getElementById('totalCalories').textContent = calBurned;
        document.getElementById('caloriesIn').textContent    = calIn;

        // Weekly workouts
        const now = new Date();
        const weekStart = new Date(now);
        weekStart.setDate(now.getDate() - now.getDay());
        const allWorkouts = getWorkouts(user.username);
        const weekWorkouts = allWorkouts.filter(w => new Date(w.date) >= weekStart);
        document.getElementById('totalWorkouts').textContent = weekWorkouts.length;

        // Goals
        const wPct = Math.min((weekWorkouts.length / 5) * 100, 100);
        const weekCal = weekWorkouts.reduce((s, w) => s + w.calories, 0);
        const cPct = Math.min((weekCal / 3500) * 100, 100);
        document.getElementById('workoutGoalBar').style.width  = wPct + '%';
        document.getElementById('calorieGoalBar').style.width  = cPct + '%';
        document.getElementById('workoutGoalText').textContent = weekWorkouts.length + ' / 5 sessions';
        document.getElementById('calorieGoalText').textContent = weekCal + ' / 3,500 kcal';

        // Today's workouts
        const wList = document.getElementById('todayWorkoutList');
        if (workouts.length > 0) {
            wList.innerHTML = workouts.map(w => `
                <li class="exercise-item">
                    <div>
                        <div class="exercise-item-name">${w.type}</div>
                        <div class="exercise-item-detail">${w.sets} sets · ${w.reps}</div>
                    </div>
                    <span class="badge badge-yellow">${w.calories} kcal</span>
                </li>
            `).join('');
        }

        // Today's nutrition
        const nList = document.getElementById('todayNutritionList');
        if (meals.length > 0) {
            nList.innerHTML = meals.map(m => `
                <li class="exercise-item">
                    <div>
                        <div class="exercise-item-name">${m.food}</div>
                        <div class="exercise-item-detail">${m.mealType}</div>
                    </div>
                    <span class="badge badge-green">${m.calories} kcal</span>
                </li>
            `).join('');
        }
    }
}

// PROGRESS PAGE
// ============================================================
const progressPage = document.getElementById('allCalories');
if (progressPage) {
    const user = requireLogin(['student']);
    if (user) {
        initSidebar();
        showDate();
        renderProgressTable(user.username);
    }
}

function renderProgressTable(username, filter = '', sort = 'newest', dateFilter = '') {
    let workouts = getWorkouts(username);

    // Overall stats
    const totalCal  = workouts.reduce((s, w) => s + w.calories, 0);
    const totalMin  = workouts.reduce((s, w) => s + w.duration, 0);
    const uniqueDays = [...new Set(workouts.map(w => w.date))].length;
    const el1 = document.getElementById('allCalories');
    const el2 = document.getElementById('allSessions');
    const el3 = document.getElementById('allMinutes');
    const el4 = document.getElementById('allDays');
    if (el1) el1.textContent = totalCal;
    if (el2) el2.textContent = workouts.length;
    if (el3) el3.textContent = totalMin;
    if (el4) el4.textContent = uniqueDays;

    if (filter)     workouts = workouts.filter(w => w.type.toLowerCase().includes(filter.toLowerCase()));
    if (dateFilter) workouts = workouts.filter(w => w.date === dateFilter);
    if (sort === 'newest')   workouts.sort((a,b) => b.date.localeCompare(a.date));
    if (sort === 'oldest')   workouts.sort((a,b) => a.date.localeCompare(b.date));
    if (sort === 'calories') workouts.sort((a,b) => b.calories - a.calories);
    if (sort === 'duration') workouts.sort((a,b) => b.duration - a.duration);

    const tbody = document.getElementById('progressTableBody');
    if (!tbody) return;
    if (workouts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:var(--muted); padding:30px;">No workouts found.</td></tr>';
        return;
    }
    tbody.innerHTML = workouts.map((w, i) => `
        <tr>
            <td style="color:var(--muted)">${i + 1}</td>
            <td>${formatDate(w.date)}</td>
            <td><span class="badge badge-blue">${w.type}</span></td>
            <td>${w.sets}</td>
            <td>${w.reps}</td>
            <td>${w.duration} min</td>
            <td><span class="badge badge-yellow">${w.calories} kcal</span></td>
            <td style="color:var(--muted); font-size:13px;">${w.notes || '—'}</td>
            <td><button onclick="deleteWorkout('${w.id}', '${username}')" class="btn-danger" style="padding:4px 10px; font-size:12px;">Delete</button></td>
        </tr>
    `).join('');
}

function filterProgress() {
    const user = getCurrentUser();
    if (!user) return;
    const filter = document.getElementById('searchProgress').value;
    const sort   = document.getElementById('sortProgress').value;
    const date   = document.getElementById('filterDate').value;
    renderProgressTable(user.username, filter, sort, date);
}

// ============================================================
// ADMIN PAGE
// ============================================================
const adminPage = document.getElementById('totalStudents');
if (adminPage) {
    const user = requireLogin(['admin', 'coordinator']);
    if (user) {
        showDate();
        renderAdminOverview();
    }
}

function getAllStudentsData() {
    const stored = JSON.parse(localStorage.getItem('ft_users')) || [];
    const allStudents = [...DEMO_USERS.filter(u => u.role === 'student'), ...stored.filter(u => u.role === 'student')];
    return allStudents;
}

function renderAdminOverview(filter = '', sort = 'az') {
    const students = getAllStudentsData();
    const el = document.getElementById('totalStudents');
    if (el) el.textContent = students.length;

    let totalSessions = 0, totalCal = 0, totalMeals = 0;
    const allActivity = [];

    students.forEach(s => {
        const workouts = getWorkouts(s.username);
        const meals    = getMeals(s.username);
        totalSessions += workouts.length;
        totalCal      += workouts.reduce((sum, w) => sum + w.calories, 0);
        totalMeals    += meals.length;
        workouts.forEach(w => allActivity.push({ student: s.name, ...w }));
    });

    const el2 = document.getElementById('totalSessions');
    const el3 = document.getElementById('totalCalBurned');
    const el4 = document.getElementById('totalMeals');
    if (el2) el2.textContent = totalSessions;
    if (el3) el3.textContent = totalCal;
    if (el4) el4.textContent = totalMeals;

    // Students table
    let list = students.map(s => {
        const workouts = getWorkouts(s.username);
        const cal = workouts.reduce((sum, w) => sum + w.calories, 0);
        return { ...s, sessionCount: workouts.length, calBurned: cal };
    });

    if (filter) list = list.filter(s => s.name.toLowerCase().includes(filter.toLowerCase()) || s.id.toLowerCase().includes(filter.toLowerCase()));
    if (sort === 'az')       list.sort((a,b) => a.name.localeCompare(b.name));
    if (sort === 'za')       list.sort((a,b) => b.name.localeCompare(a.name));
    if (sort === 'sessions') list.sort((a,b) => b.sessionCount - a.sessionCount);

    const tbody = document.getElementById('adminStudentTable');
    if (tbody) {
        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:var(--muted); padding:30px;">No students found.</td></tr>';
        } else {
            tbody.innerHTML = list.map((s, i) => {
                const bmi = calcBMI(s.weight, s.height);
                const status = bmiStatus(bmi);
                const statusClass = status === 'Normal' ? 'badge-green' : status === 'Overweight' ? 'badge-yellow' : 'badge-red';
                return `<tr>
                    <td style="color:var(--muted)">${i+1}</td>
                    <td><strong>${s.name}</strong></td>
                    <td style="color:var(--muted)">${s.id}</td>
                    <td>${s.program}</td>
                    <td><span class="badge badge-blue">${s.sessionCount}</span></td>
                    <td><span class="badge badge-yellow">${s.calBurned} kcal</span></td>
                    <td>${bmi}</td>
                    <td><span class="badge ${statusClass}">${status}</span></td>
                </tr>`;
            }).join('');
        }
    }

    // Recent activity
    allActivity.sort((a,b) => b.date.localeCompare(a.date));
    const recentTbody = document.getElementById('recentActivityTable');
    if (recentTbody) {
        if (allActivity.length === 0) {
            recentTbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--muted); padding:30px;">No activity yet.</td></tr>';
        } else {
            recentTbody.innerHTML = allActivity.slice(0, 10).map(a => `
                <tr>
                    <td><strong>${a.student}</strong></td>
                    <td><span class="badge badge-blue">${a.type}</span></td>
                    <td>${formatDate(a.date)}</td>
                    <td><span class="badge badge-yellow">${a.calories} kcal</span></td>
                    <td>${a.duration} min</td>
                </tr>
            `).join('');
        }
    }
}

function filterAdminStudents() {
    const filter = document.getElementById('searchStudent').value;
    const sort   = document.getElementById('sortStudent').value;
    renderAdminOverview(filter, sort);
}
