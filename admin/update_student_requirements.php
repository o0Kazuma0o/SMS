<?php
require_once(__DIR__ . '/../database.php');
require_once(__DIR__ . '/session.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
  $studentId = intval($_POST['student_id']);

  $fieldsToUpdate = [];
  $params = [];

  if (isset($_POST['form138'])) {
    $fieldsToUpdate[] = "form138 = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['good_moral'])) {
    $fieldsToUpdate[] = "good_moral = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['form137'])) {
    $fieldsToUpdate[] = "form137 = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['birth_certificate'])) {
    $fieldsToUpdate[] = "birth_certificate = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['brgy_clearance'])) {
    $fieldsToUpdate[] = "brgy_clearance = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['honorable_dismissal'])) {
    $fieldsToUpdate[] = "honorable_dismissal = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['transcript_of_records'])) {
    $fieldsToUpdate[] = "transcript_of_records = ?";
    $params[] = 'Submitted';
  }
  if (isset($_POST['certificate_of_grades'])) {
    $fieldsToUpdate[] = "certificate_of_grades = ?";
    $params[] = 'Submitted';
  }

  if (!empty($fieldsToUpdate)) {
    $params[] = $studentId;
    $query = "UPDATE sms3_students SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($fieldsToUpdate)) . 'i', ...$params);

    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Requirements updated successfully.']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to update requirements.']);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'message' => 'No requirements to update.']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}