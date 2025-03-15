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

// Get all academic years
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM sms3_academic_years";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $academic_years = array();
        while($row = $result->fetch_assoc()) {
            array_push($academic_years, $row);
        }
        echo json_encode($academic_years);
    } else {
        echo json_encode(array("message" => "No academic years found."));
    }
}

$conn->close();
?>