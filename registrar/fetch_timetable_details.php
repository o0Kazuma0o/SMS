<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

$section_number = $_GET['section_number'];
$department_code = $_GET['department_code'];

// Fetch timetable details only for the specified section and department
$sql = "
    SELECT t.*, s.subject_code, sec.section_number, r.room_name, d.department_code
    FROM sms3_timetable t 
    JOIN sms3_subjects s ON t.subject_id = s.id 
    JOIN sms3_sections sec ON t.section_id = sec.id 
    JOIN sms3_rooms r ON t.room_id = r.id
    JOIN sms3_departments d ON sec.department_id = d.id
    WHERE sec.section_number = ? AND d.department_code = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $section_number, $department_code);
$stmt->execute();
$result = $stmt->get_result();

$timetable = [];
while ($row = $result->fetch_assoc()) {
    $timetable[] = $row;
}

echo json_encode($timetable);
$stmt->close();