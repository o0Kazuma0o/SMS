<?php
$conn = new mysqli('localhost', 'root', '', 'admission_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $department_code = $_POST['department_code'];
    $department_name = $_POST['department_name'];

    // Insert department if department code is unique
    $stmt = $conn->prepare("INSERT INTO departments (department_code, department_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $department_code, $department_name);
    $stmt->execute();
    $stmt->close();
}

// Delete department
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all departments
$departments = $conn->query("SELECT * FROM departments");


// Add room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $room_name = $_POST['room_name'];
    $capacity = $_POST['capacity'];
    $location = $_POST['location'];

    // Insert room
    $stmt = $conn->prepare("INSERT INTO rooms (room_name, capacity, location) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $room_name, $capacity, $location);
    $stmt->execute();
    $stmt->close();
}

// Delete room
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all rooms
$rooms = $conn->query("SELECT * FROM rooms");

// Add section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $section_number = $_POST['section_number'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $department_id = $_POST['department_id'];

    // Insert section
    $stmt = $conn->prepare("INSERT INTO sections (section_number, year_level, semester, department_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $section_number, $year_level, $semester, $department_id);
    $stmt->execute();
    $stmt->close();
}

// Toggle semester and section number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_semester'])) {
    $section_id = $_POST['section_id'];

    // Get the current semester and section number
    $stmt = $conn->prepare("SELECT semester, section_number FROM sections WHERE id = ?");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $stmt->bind_result($current_semester, $current_section_number);
    $stmt->fetch();
    $stmt->close();

    // Toggle the semester and update the section number by 100
    $new_semester = ($current_semester == '1st') ? '2nd' : '1st';
    $new_section_number = ($new_semester == '1st') ? $current_section_number - 100 : $current_section_number + 100;

    // Update the section with new semester and section number
    $update_stmt = $conn->prepare("UPDATE sections SET semester = ?, section_number = ? WHERE id = ?");
    $update_stmt->bind_param("sii", $new_semester, $new_section_number, $section_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Fetch all sections
$sections = $conn->query("SELECT s.*, d.department_code FROM sections s JOIN departments d ON s.department_id = d.id");

$conn = new mysqli('localhost', 'root', '', 'admission_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $department_id = $_POST['department_id'];

    // Insert subject
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, department_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $subject_code, $subject_name, $department_id);
    $stmt->execute();
    $stmt->close();
}

// Delete subject
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all subjects
$subjects = $conn->query("SELECT s.*, d.department_code FROM subjects s JOIN departments d ON s.department_id = d.id");

// Add timetable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $subject_id = $_POST['subject_id'];
    $section_id = $_POST['section_id'];
    $room_id = $_POST['room_id'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Insert timetable
    $stmt = $conn->prepare("INSERT INTO timetable (subject_id, section_id, room_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $subject_id, $section_id, $room_id, $day_of_week, $start_time, $end_time);
    $stmt->execute();
    $stmt->close();
}

// Fetch all timetables
$timetables = $conn->query("SELECT t.*, s.subject_code, sec.section_number, r.room_name 
                            FROM timetable t 
                            JOIN subjects s ON t.subject_id = s.id 
                            JOIN sections sec ON t.section_id = sec.id 
                            JOIN rooms r ON t.room_id = r.id");

