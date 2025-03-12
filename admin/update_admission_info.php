<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['department_id'])) {
    $id = intval($data['id']);
    $department_id = intval($data['department_id']);

    $stmt = $conn->prepare("UPDATE sms3_pending_admission SET department_id = ? WHERE id = ?");
    $stmt->bind_param('ii', $department_id, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Program updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update program.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>