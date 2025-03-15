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

// Get all rooms with joins
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "
        SELECT 
            r.*, 
            d.department_code AS department 
        FROM sms3_rooms r
        LEFT JOIN sms3_departments d ON r.department_id = d.id
    ";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $rooms = array();
        while($row = $result->fetch_assoc()) {
            array_push($rooms, $row);
        }
        echo json_encode($rooms);
    } else {
        echo json_encode(array("message" => "No rooms found."));
    }
}

$conn->close();
?>