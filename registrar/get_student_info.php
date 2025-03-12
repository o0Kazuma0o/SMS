<?php
// get_admission_info.php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT a.*, d.department_name AS department_name FROM sms3_students a LEFT JOIN sms3_departments d ON a.department_id = d.id WHERE a.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();
    $stmt->close();

    if ($info) {
        echo json_encode(['success' => true, 'info' => $info]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admission record not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>