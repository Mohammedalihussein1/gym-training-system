<?php
session_start();
require "db.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $user    = mysqli_real_escape_string($conn, $_POST['username']);
    $pass    = md5($_POST['password']);
    $age     = (int)$_POST['age'];
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $height  = (int)$_POST['height'];
    $weight  = (int)$_POST['weight'];
    $program = mysqli_real_escape_string($conn, $_POST['program']);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$user' OR student_id='$student_id'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username or Student ID already exists.";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password, role, name, student_id, age, gender, height, weight, program)
                             VALUES ('$user', '$pass', 'student', '$name', '$student_id', $age, '$gender', $height, $weight, '$program')");
        $success = "Account created! You can login now.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro — Register</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="auth-page">

        <!-- LEFT PANEL -->
        <div class="auth-left">
            <div class="auth-brand">
                FitTrack
                <span>JOIN THE TEAM</span>
            </div>
            <p class="auth-tagline">
                Create your account and start tracking your practical training sessions today.
            </p>
            <div class="auth-stats">
                <div>
                    <div class="auth-stat-num">BMI</div>
                    <div class="auth-stat-label">Tracking</div>
                </div>
                <div>
                    <div class="auth-stat-num">Daily</div>
                    <div class="auth-stat-label">Log</div>
                </div>
                <div>
                    <div class="auth-stat-num">Live</div>
                    <div class="auth-stat-label">Reports</div>
                </div>
            </div>
        </div>
        <!-- RIGHT PANEL -->
        <div class="auth-right">
            <div class="auth-form-box" style="max-width: 480px;">

                <h2>Create Account</h2>
                <p>Fill in your details to register</p>

                <?php if ($success): ?>
<div class="alert alert-success">✓ <?= $success ?> <a href="index.php" style="color:var(--accent);">Login</a></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error">⚠ <?= $error ?></div>
<?php endif; ?>
                <div id="errorMsg" class="alert alert-error" style="display:none;"></div>

               <form method="POST">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label>Student ID</label>
                           <input type="text" id="regId" name="student_id" placeholder="e.g. STU001" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="regUser" name="username" placeholder="Choose a username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="regPass" name="password" placeholder="At least 6 characters" required minlength="6">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" id="regAge" name="age" placeholder="Age" min="10" max="100" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select id="regGender" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">
                                    Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" id="regHeight" name="height" placeholder="e.g. 175" min="100" max="250" required>
                        </div>
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" id="regWeight" name="weight" placeholder="e.g. 70" min="30" max="300" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Program / Department</label>
                        <input type="text" id="regProgram" name="program" placeholder="e.g. Computer Science" required>
                    </div>

                    <button type="submit" class="btn-primary">CREATE ACCOUNT</button>
                </form>

                <div class="auth-link">
                    Already have an account? <a href="index.php">Login here</a>
                </div>

            </div>
        </div>

    </div>

    <script src="script.js"></script>

</body>

</html>