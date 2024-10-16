<?php
require('../database.php');
require('../access_control.php'); // Include the file with the checkAccess function
checkAccess('superadmin'); // Ensure only users with the 'admin' role can access this page

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$timetable_id = $_GET['id'];  // Get the timetable ID from the request

if (isset($timetable_id)) {
    // Query to fetch the specific timetable by its ID
    $sql = "SELECT t.id, s.subject_code, t.day_of_week, r.room_name, t.start_time, t.end_time
            FROM sms3_timetable t
            JOIN sms3_subjects s ON t.subject_id = s.id
            JOIN sms3_rooms r ON t.room_id = r.id
            WHERE t.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If a record is found
    if ($result->num_rows > 0) {
        $timetable = $result->fetch_assoc();  // Fetch the single record
        echo json_encode($timetable);         // Return it as JSON
    } else {
        echo json_encode(['error' => 'No timetable found']);  // If no data is found
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No ID provided']);  // Handle error if no ID is passed
}

$conn->close();
