<?php
// Data to be sent
$data = array(
    "student_number" => "2025001",
    "first_name" => "John",
    "middle_name" => "A.",
    "last_name" => "Doe",
    "contact_number" => "1234567890",
    "year_level" => "1",
    "sex" => "Male",
    "department_id" => "1"
);

// Convert data to JSON format
$data_json = json_encode($data);

// Initialize cURL session
$ch = curl_init('https://admission.bcpsms3.com/api_students.php');

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);

// Execute cURL request and get the response
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    die('Curl error: ' . $error);
}

// Close cURL session
curl_close($ch);

// Decode the response
$response_data = json_decode($response, true);

// Print the response
echo '<pre>';
print_r($response_data);
echo '</pre>';
?>