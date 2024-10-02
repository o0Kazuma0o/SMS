<?php
$conn = new mysqli('localhost', 'root', '', 'admission_db');
$department_id = $_GET['department_id'];

// Fetch sections that belong to the department
$sections = $conn->query("SELECT * FROM sections WHERE department_id = $department_id");
echo '<option value="">Select Section</option>';
while ($section = $sections->fetch_assoc()) {
    echo '<option value="' . $section['id'] . '">' . $section['section_number'] . '</option>';
}

