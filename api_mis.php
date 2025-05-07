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
    $query = "SELECT * FROM sms3_payments";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $payment = [];
        while ($row = $result->fetch_assoc()) {
            $payment[] = $row;
        }
        echo json_encode($payment);
    } else {
        echo json_encode(["message" => "No payment found."]);
    }
}

// Add a new student and send it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Invalid JSON data."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO sms3_payments (student_number, amount, payment_method, payment_type, payment_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $data['student_number'], $data['amount'], $data['payment_method'], $data['payment_type'], $data['payment_date']);
    
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
            echo json_encode(["success" => "Payment added successfully."]);
        } else {
            echo json_encode(["warning" => "Payment added but SIS API did not respond correctly.", "mis_response" => $mis_response]);
        }
    } else {
        echo json_encode(["error" => "Failed to add Payment."]);
    }
}

// Close connection
$conn->close();
?>