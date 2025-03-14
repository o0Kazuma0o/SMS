<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $fullName = $_POST['fullName'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];

  try {
    $stmt = $conn->prepare("UPDATE sms3_user SET name = ?, phone = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fullName, $phone, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
  } catch (mysqli_sql_exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
  }
}
?>