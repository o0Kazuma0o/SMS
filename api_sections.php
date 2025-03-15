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

// Get all sections with joins
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "
        SELECT 
            sec.*, 
            d.department_code AS department, 
            sem.semester AS semester_label 
        FROM sms3_sections sec
        LEFT JOIN sms3_departments d ON sec.department_id = d.id
        LEFT JOIN sms3_semesters sem ON sec.semester = sem.id
    ";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $sections = array();
        while($row = $result->fetch_assoc()) {
            array_push($sections, $row);
        }
        echo json_encode($sections);
    } else {
        echo json_encode(array("message" => "No sections found."));
    }
}

$conn->close();
?>