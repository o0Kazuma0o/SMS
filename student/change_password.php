<?php
require('../database.php');
require_once 'session.php';
checkAccess('Student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $currentPassword = $_POST['currentPassword'];
  $newPassword = $_POST['newPassword'];
  $renewPassword = $_POST['renewPassword'];

  if ($newPassword !== $renewPassword) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
    exit;
  }

  // Fetch current password from database
  $stmt = $conn->prepare("SELECT password FROM sms3_students WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
  }

  // Update password
  $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
  $stmt = $conn->prepare("UPDATE sms3_user SET password = ? WHERE id = ?");
  $stmt->bind_param("si", $hashedPassword, $user_id);
  $stmt->execute();
  $stmt->close();

  echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
}
?>