<?php
require('../database.php');
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$studentId = $data['student_id'];
$sectionId = $data['section_id'];

// Retrieve up to 8 timetable IDs for the selected section
$stmt = $conn->prepare("SELECT id FROM sms3_timetable WHERE section_id = ? LIMIT 8");
$stmt->bind_param("i", $sectionId);
$stmt->execute();
$result = $stmt->get_result();
$timetableIds = array_column($result->fetch_all(MYSQLI_ASSOC), 'id');

// Prepare the insert for pending_enrollment
$columns = implode(', ', array_map(fn($i) => "timetable_$i", range(1, count($timetableIds))));
$placeholders = implode(', ', array_fill(0, count($timetableIds), '?'));
$query = "INSERT INTO pending_enrollment (student_id, section_id, $columns) VALUES (?, ?, $placeholders)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii" . str_repeat("i", count($timetableIds)), $studentId, $sectionId, ...$timetableIds);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Enrollment successful!']);
} else {
    echo json_encode(['message' => 'Enrollment failed.']);
}
$stmt->close();