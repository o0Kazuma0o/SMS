<?php
require_once '../database.php';

if (isset($_GET['student_number'])) {
  $studentNumber = $_GET['student_number'];
  $stmt = $conn->prepare("SELECT * FROM sms3_payments WHERE student_number = ? ORDER BY payment_date DESC");
  $stmt->bind_param("s", $studentNumber);
  $stmt->execute();
  $result = $stmt->get_result();

  $transactions = [];
  while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
  }

  echo json_encode(['success' => true, 'transactions' => $transactions]);
} else {
  echo json_encode(['success' => false, 'message' => 'Student number not provided.']);
}
