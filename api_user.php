<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
$servername = "localhost";
$username = "admi_caps";
$password = "re^AKBzarIgoqxka";
$dbname = "admi_bcp_sms3_admission";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT username, name, role, phone, email FROM sms3_user";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = [];
        while ($row = $result->fetch_assoc()) {
            $user[] = $row;
        }
        echo json_encode($user);
    } else {
        echo json_encode(["message" => "No user found."]);
    }
}

// Add a new student and send it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Invalid JSON data."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO sms3_user (username, name, role, phone, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $data['username'], $data['name'], $data['role'], $data['phone'], $data['email']);
    
    if ($stmt->execute()) {
        $stmt->close();

        // Send student data to SIS API
        $mis_api_url = "https://sis.bcpsms3.com/api/student"; // Adjust SIS API URL
        $ch = curl_init($mis_api_url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $mis_response = curl_exec($ch);
        $mis_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($mis_http_code === 200) {
            echo json_encode(["success" => "Student added successfully."]);
        } else {
            echo json_encode(["warning" => "Student added but SIS API did not respond correctly.", "mis_response" => $mis_response]);
        }
    } else {
        echo json_encode(["error" => "Failed to add student."]);
    }
}

// Close connection
$conn->close();
?>