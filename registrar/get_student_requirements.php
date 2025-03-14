<?php
require_once(__DIR__ . '/../database.php');
require_once(__DIR__ . '/session.php');

if (isset($_GET['student_id'])) {
  $studentId = intval($_GET['student_id']);

  $stmt = $conn->prepare("SELECT form138, good_moral, form137, birth_certificate, brgy_clearance, honorable_dismissal, transcript_of_records, certificate_of_grades FROM sms3_students WHERE id = ?");
  $stmt->bind_param('i', $studentId);
  $stmt->execute();
  $result = $stmt->get_result();
  $requirements = $result->fetch_assoc();
  $stmt->close();

  if ($requirements) {
    echo json_encode(['success' => true, 'requirements' => $requirements]);
  } else {
    echo json_encode(['success' => false, 'message' => 'No requirements found for the student.']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}