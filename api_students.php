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

// Get all students
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT s.student_number, s.first_name, s.middle_name, s.last_name, a.academic_year, s.sex, s.birthday, s.email, s.address, s.religion, s.guardian_name, s.guardian_contact, 
    s.contact_number, s.year_level, s.status, d.department_code
              FROM sms3_students s 
              JOIN sms3_departments d ON s.department_id = d.id 
              JOIN sms3_academic_years a ON s.academic_year = a.id
              ORDER BY s.created_at DESC";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        echo json_encode($students);
    } else {
        echo json_encode(["message" => "No students found."]);
    }
}

// Add a new student and send it to the SIS API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Invalid JSON data."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO sms3_students (student_number, first_name, middle_name, last_name, academic_year, year_level, department_code, sex, 
    birthday, email, contact_number, address, religion, guardian_name, guardian_contact, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssss", $data['student_number'], $data['first_name'], $data['middle_name'], $data['last_name'], $data['academic_year'], $data['year_level'], 
                      $data['department_code'], $data['sex'], $data['birthday'], $data['email'], $data['contact_number'], $data['address'], $data['religion'], $data['guardian_name'], $data['guardian_contact'], $data['status']);
    
    if ($stmt->execute()) {
        $stmt->close();

        // Send student data to SIS API
        $clinic_api_url = "https://sis.bcpsms3.com/api/student"; // Adjust SIS API URL
        $ch = curl_init($clinic_api_url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $clinic_response = curl_exec($ch);
        $clinic_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($clinic_http_code === 200) {
            echo json_encode(["success" => "Student added successfully."]);
        } else {
            echo json_encode(["warning" => "Student added but SIS API did not respond correctly.", "clinic_response" => $clinic_response]);
        }
    } else {
        echo json_encode(["error" => "Failed to add student."]);
    }
}

// Close connection
$conn->close();
?>