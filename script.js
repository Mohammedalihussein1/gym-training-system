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
