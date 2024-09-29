<?php
$conn = new mysqli('localhost', 'root', '', 'admission_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    // Get the department of the selected subject
    $stmt = $conn->prepare("SELECT department_id FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $stmt->bind_result($department_id);
    $stmt->fetch();
    $stmt->close();

    // Fetch sections that belong to the same department
    $sections = $conn->query("SELECT * FROM sections WHERE department_id = $department_id");

    // Generate options for the sections dropdown
    echo '<option value="">Select a section</option>';
    while ($section = $sections->fetch_assoc()) {
        echo '<option value="' . $section['id'] . '">' . $section['section_number'] . '</option>';
    }
}
