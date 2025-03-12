<?php
// get_admission_info.php
require('../database.php');
require_once 'session.php';
checkAccess('Staff'); // Ensure only users with the 'Staff' role can access this page

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT a.*, d.department_name AS department_name FROM sms3_pending_admission a LEFT JOIN sms3_departments d ON a.department_id = d.id WHERE a.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();
    $stmt->close();

    if ($info) {
        // Ensure department_id is present in the response
        $info['department_id'] = $info['department_id'] ?? null;
        echo json_encode(['success' => true, 'info' => $info]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admission record not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
