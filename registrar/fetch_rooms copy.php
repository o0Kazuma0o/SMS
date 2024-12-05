<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['department_code']) && !empty($_GET['department_code'])) {
    $department_code = $_GET['department_code'];

    // Fetch the department_id using the department_code
    $stmt = $conn->prepare("SELECT id FROM sms3_departments WHERE department_code = ?");
    $stmt->bind_param("s", $department_code);
    $stmt->execute();
    $stmt->bind_result($department_id);
    $stmt->fetch();
    $stmt->close();

    if ($department_id) {
        // Fetch rooms that belong to the department
        $rooms = $conn->query("SELECT * FROM sms3_rooms WHERE department_id = $department_id");

        echo '<option value="">Select Room</option>';
        while ($room = $rooms->fetch_assoc()) {
            echo '<option value="' . $room['id'] . '">' . $room['room_name'] . '</option>';
        }
    } else {
        echo '<option value="">No rooms found</option>';
    }
} else {
    echo '<option value="">Select Room</option>';
}