<?php
require('../database.php');

$timetable_id = $_GET['timetable_id'];

// Fetch timetable details
$result = $conn->query("SELECT t.*, s.subject_code, sec.section_number, r.room_name, d.department_code
    FROM sms3_timetable t 
    JOIN sms3_subjects s ON t.subject_id = s.id 
    JOIN sms3_sections sec ON t.section_id = sec.id 
    JOIN sms3_rooms r ON t.room_id = r.id
    JOIN sms3_departments d ON sec.department_id = d.id");
    


$timetable = [];
while ($row = $result->fetch_assoc()) {
    $timetable[] = $row;
}

echo json_encode($timetable);