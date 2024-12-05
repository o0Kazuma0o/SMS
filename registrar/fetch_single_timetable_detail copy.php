<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

header('Content-Type: application/json'); // Ensure correct response format

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$timetable_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($timetable_id) {
    $sql = "SELECT t.id, 
                   t.subject_id, 
                   s.subject_code, 
                   t.room_id, 
                   r.room_name, 
                   t.day_of_week, 
                   t.start_time, 
                   t.end_time, 
                   sec.department_id
            FROM sms3_timetable t
            JOIN sms3_subjects s ON t.subject_id = s.id
            JOIN sms3_rooms r ON t.room_id = r.id
            JOIN sms3_sections sec ON t.section_id = sec.id
            WHERE t.id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("SQL preparation failed: " . $conn->error);
        echo json_encode(['error' => 'Internal server error']);
        exit;
    }

    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $timetable = $result->fetch_assoc();
        echo json_encode($timetable);
    } else {
        error_log("No timetable found for ID: $timetable_id");
        echo json_encode(['error' => 'No timetable found']);
    }

    $stmt->close();
} else {
    error_log("Invalid or missing timetable ID");
    echo json_encode(['error' => 'No ID provided']);
}

$conn->close();