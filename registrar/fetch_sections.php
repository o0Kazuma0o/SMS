<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['branch']) && isset($_GET['department_id']) && !empty($_GET['department_id'])) {
    $branch = $_GET['branch'];
    $department_id = $_GET['department_id'];

    // Fetch sections that belong to the department and branch
    $sections = $conn->query("SELECT * FROM sms3_sections WHERE department_id = $department_id AND branch = '$branch'");

    echo '<option value="">Select Section</option>';
    while ($section = $sections->fetch_assoc()) {
        echo '<option value="' . $section['id'] . '">' . $section['section_number'] . '</option>';
    }
} else {
    // If no valid department is selected, return default option
    echo '<option value="">Select Section</option>';
}
