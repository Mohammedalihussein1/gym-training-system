CREATE DATABASE IF NOT EXISTS fittrack;
USE fittrack;

DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS workouts;
DROP TABLE IF EXISTS meals;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50) UNIQUE,
    password  VARCHAR(50),
    role      VARCHAR(20),
    name      VARCHAR(100),
    student_id VARCHAR(20) UNIQUE,
    email     VARCHAR(100)
);

CREATE TABLE workouts (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT,
    exercise  VARCHAR(100),
    duration  INT,
    calories  INT,
    date      DATE
);

CREATE TABLE meals (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT,
    name      VARCHAR(100),
    type      VARCHAR(20),
    calories  INT,
    date      DATE
);

CREATE TABLE programs (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    name   VARCHAR(100),
    coach  VARCHAR(100)
);

CREATE TABLE applications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT,
    program_id  INT,
    status      VARCHAR(20) DEFAULT 'pending',
    date        DATE
);

INSERT INTO users (username, password, role, name, student_id, email) VALUES
('admin',   '0192023a7bbd73250516f069df18b500', 'admin',       'System Admin',    'ADM001', 'admin@fit.com'),
('coord',   'ca58303368b17874228d4c6e4d57c0d6', 'coordinator', 'Coordinator Ali', 'COO001', 'coord@fit.com'),
('student', 'ad6a280417a0f533d8b670c61667e1a0', 'student',     'Hama Karim',      'STU001', 'hama@fit.com'),
('sara',    'ad6a280417a0f533d8b670c61667e1a0', 'student',     'Sara Ahmed',      'STU002', 'sara@fit.com');

INSERT INTO programs (name, coach) VALUES
('Strength Training', 'Coach Mike'),
('Cardio Bootcamp',   'Coach Lisa'),
('Yoga & Flexibility','Coach Maya'),
('Boxing Basics',     'Coach Rocky');

INSERT INTO workouts (user_id, exercise, duration, calories, date) VALUES
(3, 'Bench Press', 45, 280, CURDATE()),
(3, 'Running',     30, 320, CURDATE()),
(4, 'Yoga',        60, 200, CURDATE());

INSERT INTO meals (user_id, name, type, calories, date) VALUES
(3, 'Oatmeal',       'breakfast', 350, CURDATE()),
(3, 'Chicken salad', 'lunch',     480, CURDATE());

INSERT INTO applications (user_id, program_id, status, date) VALUES
(3, 1, 'pending',  CURDATE()),
(4, 3, 'approved', CURDATE());