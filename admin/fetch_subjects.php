<?php
$conn = new mysqli('localhost', 'admi_caps', 're^AKBzarIgoqxka', 'admi_bcp_sms3_admission');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['department_id']) && !empty($_GET['department_id'])) {
    $department_id = $_GET['department_id'];

    // Fetch subjects that belong to the department
    $subjects = $conn->query("SELECT * FROM sms3_subjects WHERE department_id = $department_id");

    echo '<option value="">Select Subject</option>';
    while ($subject = $subjects->fetch_assoc()) {
        echo '<option value="' . $subject['id'] . '">' . $subject['subject_code'] . '</option>';
    }
} else {
    // If no valid department is selected, return default option
    echo '<option value="">Select Subject</option>';
}