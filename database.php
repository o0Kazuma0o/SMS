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

    // Redirect to manage_departments.php
    header('Location: manage_departments.php');
    exit;
}

// Delete department
if (isset($_GET['delete_department_id'])) {
    $delete_id = $_GET['delete_department_id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to manage_departments.php
    header('Location: manage_departments.php');
    exit;
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

    // Redirect to manage_rooms.php
    header('Location: manage_rooms.php');
    exit;
}

// Delete room
if (isset($_GET['delete_room_id'])) {
    $delete_id = $_GET['delete_room_id'];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to manage_rooms.php
    header('Location: manage_rooms.php');
    exit;
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

    // After form submission, redirect to the same page using GET method to prevent form resubmission
    header("Location: manage_sections.php");
    exit;
}

// Delete section
if (isset($_GET['delete_section_id'])) {
    $delete_id = $_GET['delete_section_id'];

    // Delete the section from the database
    $stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to avoid re-submission
    header("Location: manage_sections.php");
    exit;
}

// Function to update all sections in DB
function toggleAllSections($conn) {
    // Fetch all sections
    $result = $conn->query("SELECT id, section_number, semester FROM sections");
    
    while ($section = $result->fetch_assoc()) {
        $current_section_number = $section['section_number'];
        $current_semester = $section['semester'];

        // Toggle the semester and update the section number by 100
        if ($current_semester == '1st') {
            $new_semester = '2nd';
            $new_section_number = $current_section_number + 100; // Increment by 100 for 2nd semester
        } else {
            $new_semester = '1st';
            $new_section_number = $current_section_number - 100; // Decrement by 100 for 1st semester
        }

        // Update the section in the database
        $stmt = $conn->prepare("UPDATE sections SET section_number = ?, semester = ? WHERE id = ?");
        $stmt->bind_param("isi", $new_section_number, $new_semester, $section['id']);
        $stmt->execute();
        $stmt->close();
    }
}

// Toggle semester using AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_toggle_semester'])) {
    toggleAllSections($conn);
    exit; // Important: Prevent further output for the AJAX response
}

// Fetch all sections
$sections = $conn->query("SELECT s.*, d.department_code FROM sections s JOIN departments d ON s.department_id = d.id");


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

    // Redirect to manage_subjects.php
    header('Location: manage_subjects.php');
    exit;
}

// Delete subject
if (isset($_GET['delete_subject_id'])) {
    $delete_id = $_GET['delete_subject_id'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to manage_subjects.php
    header('Location: manage_subjects.php');
    exit;
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

    // Redirect to manage_timetable.php
    header('Location: manage_timetable.php');
    exit;
}

// Delete timetable
if (isset($_GET['delete_timetable_id'])) {
    $delete_id = $_GET['delete_timetable_id'];
    
    // Prepare the delete statement for timetable
    $stmt = $conn->prepare("DELETE FROM timetable WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to manage_timetable.php after deletion
    header('Location: manage_timetable.php');
    exit;
}

// Fetch all timetables
$timetables = $conn->query("SELECT t.*, s.subject_code, sec.section_number, r.room_name 
                            FROM timetable t 
                            JOIN subjects s ON t.subject_id = s.id 
                            JOIN sections sec ON t.section_id = sec.id 
                            JOIN rooms r ON t.room_id = r.id");

