<?php
require('../database.php');
require('../access_control.php'); // Include the file with the checkAccess function
checkAccess('superadmin'); // Ensure only users with the 'admin' role can access this page

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['department_id']) && !empty($_GET['department_id'])) {
    $department_id = $_GET['department_id'];

    // Fetch sections that belong to the department
    $sections = $conn->query("SELECT * FROM sms3_sections WHERE department_id = $department_id");

    echo '<option value="">Select Section</option>';
    while ($section = $sections->fetch_assoc()) {
        echo '<option value="' . $section['id'] . '">' . $section['section_number'] . '</option>';
    }
} else {
    // If no valid department is selected, return default option
    echo '<option value="">Select Section</option>';
}