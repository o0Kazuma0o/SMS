<?php
session_start();
require 'database.php';

// Retrieve the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

$selectedBranch = $_SESSION['selected_branch'] ?? $data['selectedBranch'] ?? null;

// Add validation for branch
if (!$selectedBranch) {
    echo json_encode(['success' => false, 'message' => 'Branch selection is required']);
    exit;
}

// Prepare the SQL statement to insert the data
$sql = "INSERT INTO sms3_pending_admission (
    first_name, middle_name, last_name, department_id, branch, admission_type, year_level, sex, civil_status, religion, 
    birthday, email, contact_number, working_student, address, 
    guardian_name, guardian_contact, primary_school, primary_year, secondary_school, 
    secondary_year, last_school, last_school_year, referral_source, old_student_number, status
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending'
)";

try {
    $stmt = $conn->prepare($sql);
    $address = $data['addressInfo']['address'] . ', ' . $data['addressInfo']['barangay'] . ', ' . $data['addressInfo']['municipality'] . ' - ' . $data['addressInfo']['region'];
    $guardianName = $data['guardianInfo']['guardian_firstname'] . ' ' . $data['guardianInfo']['guardian_middlename'] . ' ' . $data['guardianInfo']['guardian_lastname'];
    $oldStudentNumber = $data['basicInfo']['admissiontype'] === 'Returnee' ? $data['basicInfo']['oldStudentNumber'] : null;

    // Bind parameters to the statement
    $stmt->bind_param(
        'sssssssssssssssssssssssss',
        $data['basicInfo']['firstname'],
        $data['basicInfo']['middlename'],
        $data['basicInfo']['lastname'],
        $data['basicInfo']['program'],
        $selectedBranch,
        $data['basicInfo']['admissiontype'],
        $data['basicInfo']['yrlvl'],
        $data['basicInfo']['sex'],
        $data['basicInfo']['civilstatus'],
        $data['basicInfo']['religion'],
        $data['basicInfo']['birthday'],
        $data['basicInfo']['email'],
        $data['basicInfo']['contactnumber'],
        $data['basicInfo']['workingstudent'],
        $address,
        $guardianName,
        $data['guardianInfo']['gcontactnumber'],
        $data['educationInfo']['primary'],
        $data['educationInfo']['pyear'],
        $data['educationInfo']['secondary'],
        $data['educationInfo']['syear'],
        $data['educationInfo']['lschool'],
        $data['educationInfo']['lyear'],
        $data['referralInfo']['how'],
        $oldStudentNumber
    );

    // Execute the prepared statement
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to insert data: ' . $e->getMessage()]);
}

// Close the statement and connection
$stmt->close();
