<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'Registrar' role can access this page

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['branch']) && isset($_GET['department_id']) && !empty($_GET['department_id'])) {
    $branch = $_GET['branch'];
    $department_id = $_GET['department_id'];

    // Fetch rooms that belong to the department and branch
    $rooms = $conn->query("SELECT * FROM sms3_rooms WHERE department_id = $department_id AND branch = '$branch'");

    echo '<option value="">Select Room</option>';
    while ($room = $rooms->fetch_assoc()) {
        echo '<option value="' . $room['id'] . '">' . $room['room_name'] . '</option>';
    }
} else {
    // If no valid department is selected, return default option
    echo '<option value="">Select Room</option>';
}
