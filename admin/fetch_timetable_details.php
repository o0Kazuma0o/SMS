<?php
$conn = new mysqli('localhost', 'root', '', 'admission_db');
$timetable_id = $_GET['timetable_id'];

// Fetch timetable details
$result = $conn->query("SELECT s.subject_code as subject, t.day_of_week as day, t.start_time, t.end_time
                        FROM timetable t
                        JOIN subjects s ON t.subject_id = s.id
                        WHERE t.section_id = $timetable_id");

$timetable = [];
while ($row = $result->fetch_assoc()) {
    $timetable[] = $row;
}

echo json_encode($timetable);