// FitTrack - form validation only
// PHP + MySQL now handles login, register, and all data.

function validateLogin() {
    var u = document.getElementById('loginUser').value.trim();
    var p = document.getElementById('loginPass').value;
    if (u.length < 3) { alert('Username must be at least 3 characters.'); return false; }
    if (p.length < 1) { alert('Please enter password.'); return false; }
    return true;
}

function validateRegister() {
    var n = document.getElementById('regName').value.trim();
    var s = document.getElementById('regId').value.trim();
    var u = document.getElementById('regUser').value.trim();
    var p = document.getElementById('regPass').value;
    if (n.length < 2) { alert('Enter your full name.'); return false; }
    if (s.length < 2) { alert('Enter your Student ID.'); return false; }
    if (u.length < 3) { alert('Username must be at least 3 characters.'); return false; }
    if (p.length < 6) { alert('Password must be at least 6 characters.'); return false; }
    return true;
}

function validateWorkout() {
    var ex  = document.getElementById('exercise').value.trim();
    var dur = parseInt(document.getElementById('duration').value);
    var cal = parseInt(document.getElementById('calories').value);
    if (ex.length < 2) { alert('Enter exercise name.'); return false; }
    if (!dur || dur < 1) { alert('Duration must be at least 1 minute.'); return false; }
    if (isNaN(cal) || cal < 0) { alert('Calories cannot be negative.'); return false; }
    return true;
}

function validateMeal() {
    var n = document.getElementById('mealName').value.trim();
    var c = parseInt(document.getElementById('mealCal').value);
    if (n.length < 2) { alert('Enter meal name.'); return false; }
    if (isNaN(c) || c < 0) { alert('Calories cannot be negative.'); return false; }
    return true;
}