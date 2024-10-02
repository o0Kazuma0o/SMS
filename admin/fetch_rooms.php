<?php
$conn = new mysqli('localhost', 'root', '', 'admission_db');
$department_id = $_GET['department_id'];

// Fetch rooms that belong to the department
$rooms = $conn->query("SELECT * FROM rooms WHERE department_id = $department_id");
echo '<option value="">Select Room</option>';
while ($room = $rooms->fetch_assoc()) {
    echo '<option value="' . $room['id'] . '">' . $room['room_name'] . '</option>';
}