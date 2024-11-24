<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

$id = $_GET['id'];
$response = ['success' => false];

if (!empty($id)) {
    $stmt = $conn->prepare("SELECT * FROM sms3_students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['info'] = $result->fetch_assoc();
    }

    $stmt->close();
}

echo json_encode($response);