<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$timetable_id = $_GET['id']; // Get the timetable ID from the request

if (isset($timetable_id)) {
    // Query to fetch the specific timetable details by its ID, including section and branch information
    $sql = "SELECT t.id, 
                   t.subject_id, 
                   s.subject_code, 
                   t.room_id, 
                   r.room_name, 
                   t.day_of_week, 
                   t.start_time, 
                   t.end_time, 
                   sec.branch AS section_branch,
                   sec.department_id
            FROM sms3_timetable t
            JOIN sms3_subjects s ON t.subject_id = s.id
            JOIN sms3_rooms r ON t.room_id = r.id
            JOIN sms3_sections sec ON t.section_id = sec.id
            WHERE t.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If a record is found
    if ($result->num_rows > 0) {
        $timetable = $result->fetch_assoc(); // Fetch the single record
        echo json_encode($timetable);       // Return it as JSON
    } else {
        echo json_encode(['error' => 'No timetable found']); // If no data is found
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No ID provided']); // Handle error if no ID is passed
}

$conn->close();