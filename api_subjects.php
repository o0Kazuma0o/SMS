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

// Get all subjects with joins
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "
        SELECT 
            s.*, 
            d.department_code AS department 
        FROM sms3_subjects s
        LEFT JOIN sms3_departments d ON s.department_id = d.id
    ";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $subjects = array();
        while($row = $result->fetch_assoc()) {
            array_push($subjects, $row);
        }
        echo json_encode($subjects);
    } else {
        echo json_encode(array("message" => "No subjects found."));
    }
}

$conn->close();
?>