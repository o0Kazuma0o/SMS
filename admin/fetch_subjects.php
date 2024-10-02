<?php
$conn = new mysqli('localhost', 'root', '', 'admission_db');
$department_id = $_GET['department_id'];

// Fetch subjects that belong to the department
$subjects = $conn->query("SELECT * FROM subjects WHERE department_id = $department_id");
echo '<option value="">Select Section</option>';
while ($subject = $subjects->fetch_assoc()) {
    echo '<option value="' . $subject['id'] . '">' . $subject['subject_code'] . '</option>';
}