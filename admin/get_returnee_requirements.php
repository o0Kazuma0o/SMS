<?php
require_once(__DIR__ . '/../database.php');
require_once(__DIR__ . '/session.php');

if (isset($_GET['student_number'])) {
  $studentNumber = $_GET['student_number'];

  $stmt = $conn->prepare("SELECT form138, good_moral, form137, birth_certificate, brgy_clearance, honorable_dismissal, transcript_of_records, certificate_of_grades FROM sms3_students WHERE student_number = ?");
  $stmt->bind_param('s', $studentNumber);
  $stmt->execute();
  $result = $stmt->get_result();
  $requirements = $result->fetch_assoc();
  $stmt->close();

  if ($requirements) {
    echo json_encode(['success' => true, 'requirements' => $requirements]);
  } else {
    echo json_encode(['success' => false, 'message' => 'No requirements found for the returnee.']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}