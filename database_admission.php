<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exception for errors
$conn = new mysqli('localhost', 'root', '', 'bcp-sms_admission');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Prepare the SQL statement to insert the data
$sql = "INSERT INTO sms3_pending_admission (
    full_name, program, admission_type, year_level, sex, civil_status, religion, 
    birthday, email, contact_number, facebook_name, address, father_name, mother_name, 
    guardian_name, guardian_contact, primary_school, primary_year, secondary_school, 
    secondary_year, last_school, last_school_year, referral_source, status
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending'
)";

try {
    $stmt = $conn->prepare($sql);
    $fullName = $data['basicInfo']['firstname'] . ' ' . $data['basicInfo']['middlename'] . ' ' . $data['basicInfo']['lastname'];
    $address = $data['addressInfo']['address'] . ', ' . $data['addressInfo']['barangay'] . ', ' . $data['addressInfo']['municipality'] . ' - ' . $data['addressInfo']['region'];
    $fatherName = $data['guardianInfo']['father_firstname'] . ' ' . $data['guardianInfo']['father_middlename'] . ' ' . $data['guardianInfo']['father_lastname'];
    $motherName = $data['guardianInfo']['mother_firstname'] . ' ' . $data['guardianInfo']['mother_middlename'] . ' ' . $data['guardianInfo']['mother_lastname'];
    $guardianName = $data['guardianInfo']['guardian_firstname'] . ' ' . $data['guardianInfo']['guardian_middlename'] . ' ' . $data['guardianInfo']['guardian_lastname'];

    // Bind parameters to the statement
    $stmt->bind_param(
        'sssssssssssssssssssssss', // 23 's' characters
        $fullName,
        $data['basicInfo']['program'],
        $data['basicInfo']['admissiontype'],
        $data['basicInfo']['yrlvl'],
        $data['basicInfo']['sex'],
        $data['basicInfo']['civilstatus'],
        $data['basicInfo']['religion'],
        $data['basicInfo']['birthday'],
        $data['basicInfo']['email'],
        $data['basicInfo']['contactnumber'],
        $data['basicInfo']['facebookname'],
        $address,
        $fatherName,
        $motherName,
        $guardianName,
        $data['guardianInfo']['gcontactnumber'],
        $data['educationInfo']['primary'],
        $data['educationInfo']['pyear'],
        $data['educationInfo']['secondary'],
        $data['educationInfo']['syear'],
        $data['educationInfo']['lschool'],
        $data['educationInfo']['lyear'],
        $data['referralInfo']['how']
    );

    // Execute the prepared statement
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to insert data: ' . $e->getMessage()]);
}

// Close the statement and connection
$stmt->close();
    