<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

$section_number = $_GET['section_number'];
$department_code = $_GET['department_code'];

// Fetch the section_id based on the section_number and department_code
$sql = "
    SELECT sec.id AS section_id
    FROM sms3_sections sec
    JOIN sms3_departments d ON sec.department_id = d.id
    WHERE sec.section_number = ? AND d.department_code = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $section_number, $department_code);
$stmt->execute();
$stmt->bind_result($section_id);
$stmt->fetch();
$stmt->close();

if (!$section_id) {
    $_SESSION['error_message'] = "Invalid section or department.";
    header('Location: manage_timetable.php');
    exit;
}

// Fetch timetable details for the specified section and department
$sql = "
    SELECT t.*, s.subject_code, sec.section_number, r.room_name, d.department_code
    FROM sms3_timetable t 
    JOIN sms3_subjects s ON t.subject_id = s.id 
    JOIN sms3_sections sec ON t.section_id = sec.id 
    JOIN sms3_rooms r ON t.room_id = r.id
    JOIN sms3_departments d ON sec.department_id = d.id
    WHERE sec.id = ? AND d.department_code = ?
    ORDER BY 
        FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        t.start_time
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $section_id, $department_code);
$stmt->execute();
$result = $stmt->get_result();

$timetable = [];
while ($row = $result->fetch_assoc()) {
    $timetable[] = $row;
}

$stmt->close();

// Respond with section_id and timetable data
$response = [
    'section_id' => $section_id,  // Always return section_id
    'timetable' => $timetable
];

echo json_encode($response);