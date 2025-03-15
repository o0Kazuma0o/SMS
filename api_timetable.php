<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Database connection
$servername = "localhost";
$username = "admi_caps";
$password = "re^AKBzarIgoqxka";
$dbname = "admi_bcp_sms3_admission";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all timetables with joins
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "
        SELECT 
            t.*, 
            r.room_name, 
            s.subject_name, 
            sec.section_name 
        FROM sms3_timetable t
        LEFT JOIN sms3_rooms r ON t.room_id = r.id
        LEFT JOIN sms3_subjects s ON t.subject_id = s.id
        LEFT JOIN sms3_sections sec ON t.section_id = sec.id
    ";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $timetables = array();
        while($row = $result->fetch_assoc()) {
            array_push($timetables, $row);
        }
        echo json_encode($timetables);
    } else {
        echo json_encode(array("message" => "No timetables found."));
    }
}

$conn->close();
?>