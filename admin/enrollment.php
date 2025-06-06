<?php
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
require '../models/EnrollmentPredictiveModel.php';
checkAccess('Admin');

$currentSemester = getCurrentActiveSemester($conn);

// Fetch timetable details
if (isset($_GET['timetable_details'])) {
  $enrollmentId = intval($_GET['timetable_details']);
  $query = "
      SELECT t.id, sec.section_number, sub.subject_code, 
             CONCAT(t.day_of_week, ' ', TIME_FORMAT(t.start_time, '%H:%i'), '-', TIME_FORMAT(t.end_time, '%H:%i')) AS schedule
      FROM sms3_pending_enrollment pe
      JOIN sms3_timetable t ON t.id IN (pe.timetable_1, pe.timetable_2, pe.timetable_3, pe.timetable_4, pe.timetable_5, pe.timetable_6, pe.timetable_7, pe.timetable_8)
      JOIN sms3_sections sec ON t.section_id = sec.id
      JOIN sms3_subjects sub ON t.subject_id = sub.id
      WHERE pe.id = ?
  ";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $enrollmentId);
  $stmt->execute();
  $result = $stmt->get_result();

  $timetableDetails = [];
  while ($row = $result->fetch_assoc()) {
    $timetableDetails[] = $row;
  }
  $stmt->close();

  header('Content-Type: application/json');
  echo json_encode($timetableDetails);
  exit;
}

// Fetch sections for current semester
if (isset($_GET['fetch_sections'])) {
  $stmt = $conn->prepare("
      SELECT DISTINCT sec.id, sec.section_number
      FROM sms3_sections sec
      JOIN sms3_timetable t ON t.section_id = sec.id
      WHERE sec.semester_id = (SELECT id FROM sms3_semesters WHERE name = ? AND status = 'Active')
  ");
  $stmt->bind_param("s", $currentSemester);
  $stmt->execute();
  $result = $stmt->get_result();

  $sections = [];
  while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
  }
  $stmt->close();

  header('Content-Type: application/json');
  echo json_encode($sections);
  exit;
}

// Fetch subjects based on section
if (isset($_GET['fetch_subjects'])) {
  $sectionId = intval($_GET['section_id']);
  $stmt = $conn->prepare("
      SELECT t.id AS timetable_id, sub.id AS subject_id, sub.subject_code, t.day_of_week, t.start_time, t.end_time
      FROM sms3_timetable t
      JOIN sms3_subjects sub ON t.subject_id = sub.id
      WHERE t.section_id = ?
  ");
  $stmt->bind_param("i", $sectionId);
  $stmt->execute();
  $result = $stmt->get_result();

  $subjects = [];
  while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
  }
  $stmt->close();

  header('Content-Type: application/json');
  echo json_encode($subjects);
  exit;
}

// Fetch pending enrollments
$query = "
    SELECT pe.id AS enrollment_id, s.student_number, s.first_name, s.last_name, s.admission_type, 
           d.department_code, pe.created_at, pe.receipt_status,
           GROUP_CONCAT(DISTINCT sec.section_number ORDER BY sec.id SEPARATOR ', ') AS sections,
           GROUP_CONCAT(DISTINCT sub.subject_code ORDER BY sub.id SEPARATOR ', ') AS subjects,
           GROUP_CONCAT(DISTINCT CONCAT(t.day_of_week, ' ', TIME_FORMAT(t.start_time, '%H:%i'), '-', TIME_FORMAT(t.end_time, '%H:%i')) ORDER BY t.start_time SEPARATOR ', ') AS schedules
    FROM sms3_pending_enrollment pe
    JOIN sms3_students s ON pe.student_id = s.id
    LEFT JOIN sms3_timetable t ON t.id IN (pe.timetable_1, pe.timetable_2, pe.timetable_3, pe.timetable_4, pe.timetable_5, pe.timetable_6, pe.timetable_7, pe.timetable_8)
    LEFT JOIN sms3_sections sec ON t.section_id = sec.id
    LEFT JOIN sms3_subjects sub ON t.subject_id = sub.id
    LEFT JOIN sms3_departments d ON sec.department_id = d.id
    GROUP BY pe.id
    ORDER BY pe.created_at DESC
";

$result = $conn->query($query);

// Handle receipt status updates (Paid/Rejected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enrollment_id'], $_POST['receipt_status'])) {
  $enrollmentId = intval($_POST['enrollment_id']);
  $receiptStatus = $_POST['receipt_status'];

  try {
    if ($receiptStatus === 'Rejected') {
      // Fetch pending enrollment details
      $stmt = $conn->prepare("
              SELECT student_id, timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8
              FROM sms3_pending_enrollment WHERE id = ?
          ");
      $stmt->bind_param("i", $enrollmentId);
      $stmt->execute();
      $result = $stmt->get_result();
      $enrollmentData = $result->fetch_assoc();
      $stmt->close();

      if ($enrollmentData) {

        // Fetch section IDs associated with the timetables
        $timetableIds = array_filter([
          $enrollmentData['timetable_1'],
          $enrollmentData['timetable_2'],
          $enrollmentData['timetable_3'],
          $enrollmentData['timetable_4'],
          $enrollmentData['timetable_5'],
          $enrollmentData['timetable_6'],
          $enrollmentData['timetable_7'],
          $enrollmentData['timetable_8']
        ]);

        $sectionIds = [];
        if (!empty($timetableIds)) {
          $placeholders = implode(',', array_fill(0, count($timetableIds), '?'));
          $query = "SELECT DISTINCT section_id FROM sms3_timetable WHERE id IN ($placeholders)";
          $stmt = $conn->prepare($query);
          $stmt->bind_param(str_repeat('i', count($timetableIds)), ...$timetableIds);
          $stmt->execute();
          $result = $stmt->get_result();
          while ($row = $result->fetch_assoc()) {
            $sectionIds[] = $row['section_id'];
          }
          $stmt->close();
        }

        // Increment the available slots for each section
        foreach ($sectionIds as $sectionId) {
          $updateSlotQuery = "UPDATE sms3_sections SET available = available + 1 WHERE id = ?";
          $updateSlotStmt = $conn->prepare($updateSlotQuery);
          $updateSlotStmt->bind_param("i", $sectionId);
          $updateSlotStmt->execute();
          $updateSlotStmt->close();
        }

        // Insert data into sms3_enrollment_data with status Rejected
        $stmt = $conn->prepare("INSERT INTO sms3_enrollment_data (
                student_id, timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8, receipt_status, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $status = 'Rejected';
        $stmt->bind_param(
          "iiiiiiiiiss",
          $enrollmentData['student_id'],
          $enrollmentData['timetable_1'],
          $enrollmentData['timetable_2'],
          $enrollmentData['timetable_3'],
          $enrollmentData['timetable_4'],
          $enrollmentData['timetable_5'],
          $enrollmentData['timetable_6'],
          $enrollmentData['timetable_7'],
          $enrollmentData['timetable_8'],
          $receiptStatus,
          $status
        );
        $stmt->execute();
        $stmt->close();

        // Delete the pending enrollment record
        $stmt = $conn->prepare("DELETE FROM sms3_pending_enrollment WHERE id = ?");
        $stmt->bind_param("i", $enrollmentId);
        $stmt->execute();
        $stmt->close();

        // Log the audit entry
        logAudit($conn, $_SESSION['user_id'], 'DELETE', 'sms3_enrollment_data', $enrollmentId, [
          'id' => $enrollmentId,
          'student_id' => $enrollmentData['student_id']
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Enrollment rejected successfully.']);
        exit; // Stop script execution after sending the JSON response
      } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch enrollment details.']);
        exit; // Stop script execution
      }
    } elseif ($receiptStatus === 'Paid') {
      // Update receipt_status to Paid in sms3_pending_enrollment
      $stmt = $conn->prepare("UPDATE sms3_pending_enrollment SET receipt_status = 'Paid' WHERE id = ?");
      $stmt->bind_param("i", $enrollmentId);
      $stmt->execute();
      $stmt->close();

      // Fetch pending enrollment details
      $stmt = $conn->prepare("
              SELECT student_id, timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8
              FROM sms3_pending_enrollment WHERE id = ?
          ");
      $stmt->bind_param("i", $enrollmentId);
      $stmt->execute();
      $result = $stmt->get_result();
      $enrollmentData = $result->fetch_assoc();
      $stmt->close();

      if ($enrollmentData) {
        // Initialize $timetableDetails to avoid undefined variable errors
        $timetableDetails = [];

        // Fetch timetable details for logging purposes
        $stmt = $conn->prepare("
            SELECT t.id, sec.section_number, sub.subject_code
            FROM sms3_timetable t
            JOIN sms3_sections sec ON t.section_id = sec.id
            JOIN sms3_subjects sub ON t.subject_id = sub.id
            WHERE t.id IN (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
          "iiiiiiii",
          $enrollmentData['timetable_1'],
          $enrollmentData['timetable_2'],
          $enrollmentData['timetable_3'],
          $enrollmentData['timetable_4'],
          $enrollmentData['timetable_5'],
          $enrollmentData['timetable_6'],
          $enrollmentData['timetable_7'],
          $enrollmentData['timetable_8']
        );
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
          $timetableDetails[] = $row;
        }
        $stmt->close();

        // Update student record with timetable and status
        $stmt = $conn->prepare("
            UPDATE sms3_students
            SET timetable_1 = ?, timetable_2 = ?, timetable_3 = ?, timetable_4 = ?, timetable_5 = ?, timetable_6 = ?, timetable_7 = ?, timetable_8 = ?, status = 'Enrolled', admission_type = 'Continuing'
            WHERE id = ?
        ");
        $stmt->bind_param(
          "iiiiiiiii",
          $enrollmentData['timetable_1'],
          $enrollmentData['timetable_2'],
          $enrollmentData['timetable_3'],
          $enrollmentData['timetable_4'],
          $enrollmentData['timetable_5'],
          $enrollmentData['timetable_6'],
          $enrollmentData['timetable_7'],
          $enrollmentData['timetable_8'],
          $enrollmentData['student_id']
        );
        $stmt->execute();
        $stmt->close();

        // Insert data into sms3_enrollment_data with status Approved
        $stmt = $conn->prepare("INSERT INTO sms3_enrollment_data (
            student_id, timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8, receipt_status, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $status = 'Approved';
        $paidStatus = 'Paid';
        $stmt->bind_param(
          "iiiiiiiiiss",
          $enrollmentData['student_id'],
          $enrollmentData['timetable_1'],
          $enrollmentData['timetable_2'],
          $enrollmentData['timetable_3'],
          $enrollmentData['timetable_4'],
          $enrollmentData['timetable_5'],
          $enrollmentData['timetable_6'],
          $enrollmentData['timetable_7'],
          $enrollmentData['timetable_8'],
          $paidStatus,
          $status
        );
        $stmt->execute();
        $stmt->close();

        // Delete the pending enrollment record
        $stmt = $conn->prepare("DELETE FROM sms3_pending_enrollment WHERE id = ?");
        $stmt->bind_param("i", $enrollmentId);
        $stmt->execute();
        $stmt->close();

        // Log the audit entry
        $timetableDetailsStr = implode(', ', array_map(function ($t) {
          return "{$t['subject_code']} ({$t['section_number']})";
        }, $timetableDetails));
        logAudit($conn, $_SESSION['user_id'], 'ADD', 'sms3_enrollment_data', $enrollmentId, [
          'id' => $enrollmentId,
          'student_id' => $enrollmentData['student_id'],
          'timetables' => $timetableDetailsStr
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Enrollment marked as paid and student enrolled successfully.']);
        exit; // Stop script execution
      } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch enrollment details.']);
        exit; // Stop script execution
      }
    }
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
    exit; // Stop script execution
  }
}

// Handle fetching a single timetable for editing
if (isset($_GET['fetch_timetable_id'])) {
  $timetableId = intval($_GET['fetch_timetable_id']);
  $query = "
      SELECT t.id, sec.id AS section_id, sec.section_number, sub.id AS subject_id, sub.subject_code,
             t.day_of_week, r.room_name, t.start_time, t.end_time, d.id AS department_id
      FROM sms3_timetable t
      JOIN sms3_sections sec ON t.section_id = sec.id
      JOIN sms3_subjects sub ON t.subject_id = sub.id
      JOIN sms3_rooms r ON t.room_id = r.id
      JOIN sms3_departments d ON sec.department_id = d.id
      WHERE t.id = ?
  ";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $timetableId);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();
  $stmt->close();

  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

// Update a specific timetable entry in sms3_pending_enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_timetable'])) {
  $timetableId = intval($_POST['timetable_id']); // Current timetable ID
  $newTimetableId = intval($_POST['new_timetable_id']); // New timetable ID to update

  // Identify the column where the current timetable ID exists
  $stmt = $conn->prepare("
      SELECT id,
             IF(timetable_1 = ?, 'timetable_1', 
                IF(timetable_2 = ?, 'timetable_2', 
                   IF(timetable_3 = ?, 'timetable_3', 
                      IF(timetable_4 = ?, 'timetable_4', 
                         IF(timetable_5 = ?, 'timetable_5', 
                            IF(timetable_6 = ?, 'timetable_6', 
                               IF(timetable_7 = ?, 'timetable_7', 
                                  'timetable_8')))))))
             AS timetable_column
      FROM sms3_pending_enrollment
      WHERE ? IN (timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8)
  ");
  $stmt->bind_param("iiiiiiii", $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();
  $stmt->close();

  if (!$data) {
    $_SESSION['error_message'] = "Timetable entry not found in pending enrollment.";
    header("Location: enrollment.php");
    exit;
  }

  $timetableColumn = $data['timetable_column']; // Identified timetable column
  if (!$timetableColumn) {
    $_SESSION['error_message'] = "Unable to identify timetable column.";
    header("Location: enrollment.php");
    exit;
  }

  // Update the identified column with the new timetable ID
  $updateQuery = "UPDATE sms3_pending_enrollment SET $timetableColumn = ? WHERE id = ?";
  $updateStmt = $conn->prepare($updateQuery);
  $updateStmt->bind_param("ii", $newTimetableId, $data['id']);

  if ($updateStmt->execute()) {
    $_SESSION['success_message'] = "Timetable updated successfully.";
  } else {
    $_SESSION['error_message'] = "Failed to update timetable.";
  }
  $updateStmt->close();

  header("Location: enrollment.php");
  exit;
}

// Delete timetable from sms3_pending_enrollment
if (isset($_GET['delete_timetable_from_enrollment'])) {
  $timetableId = intval($_GET['delete_timetable_from_enrollment']);

  // Identify the column containing the timetable ID
  $stmt = $conn->prepare("
        SELECT id,
               IF(timetable_1 = ?, 'timetable_1', 
                  IF(timetable_2 = ?, 'timetable_2', 
                     IF(timetable_3 = ?, 'timetable_3', 
                        IF(timetable_4 = ?, 'timetable_4', 
                           IF(timetable_5 = ?, 'timetable_5', 
                              IF(timetable_6 = ?, 'timetable_6', 
                                 IF(timetable_7 = ?, 'timetable_7', 
                                    'timetable_8')))))))
               AS timetable_column
        FROM sms3_pending_enrollment
        WHERE ? IN (timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8)
    ");
  $stmt->bind_param("iiiiiiii", $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();
  $stmt->close();

  if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Timetable entry not found in enrollment.']);
    exit;
  }

  $timetableColumn = $data['timetable_column']; // Identified timetable column
  if (!$timetableColumn) {
    echo json_encode(['status' => 'error', 'message' => 'Unable to identify timetable column.']);
    exit;
  }

  // Update the column to NULL
  $updateQuery = "UPDATE sms3_pending_enrollment SET $timetableColumn = NULL WHERE id = ?";
  $updateStmt = $conn->prepare($updateQuery);
  $updateStmt->bind_param("i", $data['id']);

  if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Timetable entry deleted successfully.']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete timetable entry.']);
  }
  $updateStmt->close();
  exit;
}

// Fetch historical enrollment data
$sql = "
    SELECT 
        YEAR(created_at) AS year,
        MONTH(created_at) AS month,
        COUNT(*) AS enrollments
    FROM sms3_enrollment_data
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)
";
$result1 = $conn->query($sql);
$historicalData = [];
while ($row = $result1->fetch_assoc()) {
  $historicalData[] = [
    'year' => $row['year'],
    'month' => $row['month'],
    'enrollments' => $row['enrollments']
  ];
}

// Initialize the SARIMA model
$model = new EnrollmentSARIMAModel($historicalData, 12, 2);
$forecast = $model->forecast();

// Get the latest actual value for each department
$latestActuals = [];
$sql_latest = "
    SELECT 
        d.department_name,
        YEAR(ed.created_at) AS year,
        MONTH(ed.created_at) AS month,
        COUNT(DISTINCT ed.student_id) AS total_enrollments
    FROM sms3_enrollment_data ed
    INNER JOIN sms3_timetable t ON 
        ed.timetable_1 = t.id OR
        ed.timetable_2 = t.id OR
        ed.timetable_3 = t.id OR
        ed.timetable_4 = t.id OR
        ed.timetable_5 = t.id OR
        ed.timetable_6 = t.id OR
        ed.timetable_7 = t.id OR
        ed.timetable_8 = t.id
    INNER JOIN sms3_sections s ON t.section_id = s.id
    INNER JOIN sms3_departments d ON s.department_id = d.id
    GROUP BY d.department_name, year, month
    ORDER BY d.department_name, year DESC, month DESC
";
$result_latest = $conn->query($sql_latest);
while ($row = $result_latest->fetch_assoc()) {
  $dept = $row['department_name'];
  // Only keep the latest for each department
  if (
    !isset($latestActuals[$dept]) ||
    ($row['year'] > $latestActuals[$dept]['year']) ||
    ($row['year'] == $latestActuals[$dept]['year'] && $row['month'] > $latestActuals[$dept]['month'])
  ) {
    $latestActuals[$dept] = [
      'year' => $row['year'],
      'month' => $row['month'],
      'enrollments' => $row['total_enrollments']
    ];
  }
}

// Initialize the predictive model
$model = new EnrollmentPredictiveModel($conn, []);
$departmentForecasts = $model->forecastByDepartment(0, 12);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Enrollment</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="https://elc-public-images.s3.ap-southeast-1.amazonaws.com/bcp-olp-logo-mini2.png" rel="icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.0/echarts.min.js"></script>

  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">

  <style>
    .popup-message {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      padding: 15px;
      border-radius: 5px;
      font-size: 16px;
      color: #fff;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
    }

    .popup-message.success {
      background-color: green;
    }

    .popup-message.error {
      background-color: red;
    }
  </style>

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle rounded-circle" style="font-size: 1.5rem;"></i>
            <span class="d-none d-md-block dropdown-toggle ps-2">
              <?= htmlspecialchars($_SESSION['name']); ?>
            </span>
          </a><!-- End Profile Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?= htmlspecialchars($_SESSION['name']); ?></h6>
              <span><?= htmlspecialchars($_SESSION['role']); ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users_profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <hr class="dropdown-divider">
        </li>

        <li>
          <a class="dropdown-item d-flex align-items-center" href="../logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sign Out</span>
          </a>
        </li>

      </ul><!-- End Profile Dropdown Items -->
      </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <div style="display: flex; flex-direction: column; align-items: center; padding: 16px;">
        <div style="display: flex; align-items: center; justify-content: center; width: 7rem; height: 8rem; overflow: hidden;">
          <img src="/assets/img/bcp.png" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
      </div>

      <hr class="sidebar-divider">

      <li class="nav-item">
        <a class="nav-link " href="Dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <hr class="sidebar-divider">

      <li class="nav-heading">Admission & Enrollment</li>

      <li class="nav-item">
        <a class="nav-link " href="admission.php">
          <i class="bi bi-grid"></i>
          <span>Admission</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="admission_temp.php">
          <i class="bi bi-grid"></i>
          <span>Temporary Admission</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="enrollment.php">
          <i class="bi bi-grid"></i>
          <span>Enrollment</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="students.php">
          <i class="bi bi-grid"></i>
          <span>Students</span>
        </a>
      </li>

      <!-- End System Nav -->

      <hr class="sidebar-divider">

      <li class="nav-heading">TEST CASHIER</li>

      <li class="nav-item">
        <a class="nav-link " href="manage_payment.php">
          <i class="bi bi-grid"></i>
          <span>Payment</span>
        </a>
      </li>

      <hr class="sidebar-divider">

      <li class="nav-heading">TEST REGISTRAR</li>

      <li class="nav-item">
        <a class="nav-link " href="manage_academic_semester.php">
          <i class="bi bi-grid"></i>
          <span>Academic Structure</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="manage_departments.php">
          <i class="bi bi-grid"></i>
          <span>Department</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="manage_rooms.php">
          <i class="bi bi-grid"></i>
          <span>Rooms</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="manage_sections.php">
          <i class="bi bi-grid"></i>
          <span>Sections</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="manage_subjects.php">
          <i class="bi bi-grid"></i>
          <span>Subjects</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="manage_timetable.php">
          <i class="bi bi-grid"></i>
          <span>Timetable</span>
        </a>
      </li>
      <!-- End System Nav -->

      <hr class="sidebar-divider">

      <li class="nav-heading">MANAGE USER & DATA</li>
      <li class="nav-item">
        <a class="nav-link " href="audit_logs.php">
          <i class="bi bi-grid"></i>
          <span>Audit Logs</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="manage_user.php">
          <i class="bi bi-grid"></i>
          <span>Users</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="admissions_data.php">
          <i class="bi bi-grid"></i>
          <span>Admission Data</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="enrollment_data.php">
          <i class="bi bi-grid"></i>
          <span>Enrollment Data</span>
        </a>
      </li>

      <hr class="sidebar-divider">

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Enrollment</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Enrollment</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Forecasting -->
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Enrollment Forecast</h5>

              <div id="enrollmentForecastChart" style="min-height: 400px;" class="echart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  const historicalData = <?php echo json_encode($historicalData); ?>;
                  const forecast = <?php echo json_encode($forecast); ?>;

                  if (historicalData.length === 0 || forecast.length === 0) {
                    document.getElementById('enrollmentForecastChart').innerHTML = '<div style="text-align: center; padding: 20px;">No data available for forecasting.</div>';
                    return;
                  }

                  // Prepare data for chart
                  const months = historicalData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);
                  const historicalValues = historicalData.map(item => item.enrollments);
                  const forecastValues = forecast.map(item => item.predicted_enrollments);
                  const forecastMonths = forecast.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);

                  // Initialize echarts instance
                  const chart = echarts.init(document.querySelector("#enrollmentForecastChart"));

                  // Set chart options
                  chart.setOption({
                    tooltip: {
                      trigger: 'item'
                    },
                    legend: {
                      data: ['Historical', 'Forecast']
                    },
                    xAxis: {
                      type: 'category',
                      data: [...months, ...forecastMonths]
                    },
                    yAxis: {
                      type: 'value'
                    },
                    series: [{
                        name: 'Historical',
                        type: 'line',
                        smooth: true,
                        data: historicalValues
                      },
                      {
                        name: 'Forecast',
                        type: 'line',
                        smooth: true,
                        data: [...Array(months.length).fill(null), ...forecastValues]
                      }
                    ]
                  });

                  // Handle window resize to maintain chart responsiveness
                  window.addEventListener('resize', () => {
                    chart.resize();
                  });
                });
              </script>
            </div>
          </div>
        </div>

        <!-- Department Forecasting -->
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Forecasting by Department</h5>
              <div id="departmentColumnChart" style="min-height: 400px;"></div>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  const departmentForecasts = <?php echo json_encode($departmentForecasts); ?>;
                  const latestActuals = <?php echo json_encode($latestActuals); ?>;

                  if (Object.keys(departmentForecasts).length === 0) {
                    document.getElementById('departmentColumnChart').innerHTML = '<div style="text-align: center; padding: 20px;">No forecast data available.</div>';
                    return;
                  }

                  // Prepare data for the chart
                  const departments = Object.keys(departmentForecasts);
                  // Get months from forecast (assume all departments have same forecast months)
                  const forecastMonths = departmentForecasts[departments[0]].map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);

                  // Add latest actual month (if exists) as the first category
                  let months = [];
                  let hasActual = false;
                  if (latestActuals[departments[0]]) {
                    months.push(`${latestActuals[departments[0]].year}-${String(latestActuals[departments[0]].month).padStart(2, '0')} (Actual)`);
                    hasActual = true;
                  }
                  forecastMonths.forEach(m => months.push(m + " (Forecast)"));

                  const seriesData = departments.map(department => {
                    let data = [];
                    // Add latest actual value if exists
                    if (hasActual && latestActuals[department]) {
                      data.push(latestActuals[department].enrollments);
                    } else if (hasActual) {
                      data.push(null);
                    }
                    // Add forecasted values
                    data = data.concat(departmentForecasts[department].map(item => item.predicted_enrollments));
                    return {
                      name: department,
                      data: data
                    };
                  });

                  // Initialize ApexCharts instance
                  new ApexCharts(document.querySelector("#departmentColumnChart"), {
                    series: seriesData,
                    chart: {
                      type: 'bar',
                      height: 400
                    },
                    plotOptions: {
                      bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                      }
                    },
                    dataLabels: {
                      enabled: false
                    },
                    stroke: {
                      show: true,
                      width: 2,
                      colors: ['transparent']
                    },
                    xaxis: {
                      categories: months
                    },
                    yaxis: {
                      title: {
                        text: 'Enrollments'
                      }
                    },
                    fill: {
                      opacity: 1
                    },
                    tooltip: {
                      y: {
                        formatter: function(val) {
                          return val + " enrollments";
                        }
                      }
                    }
                  }).render();
                });
              </script>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Pending Enrollment</h5>

            <!-- Filter Inputs -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="filterDepartment" class="form-label">Department</label>
                <select id="filterDepartment" class="form-select">
                  <option value="">All Departments</option>
                  <?php
                  $departments = $conn->query("SELECT * FROM sms3_departments");
                  while ($department = $departments->fetch_assoc()): ?>
                    <option value="<?= $department['department_code']; ?>"><?= $department['department_code']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label for="filterStartDate" class="form-label">Start Date</label>
                <input type="date" id="filterStartDate" class="form-control">
              </div>
              <div class="col-md-4">
                <label for="filterEndDate" class="form-label">End Date</label>
                <input type="date" id="filterEndDate" class="form-control">
              </div>
            </div>

            <table class="table">
              <thead>
                <tr>
                  <th>Student Number</th>
                  <th>Student</th>
                  <th>Admission Type</th>
                  <th>Department</th>
                  <th>Subjects</th>
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-enrollment-id="<?= $row['enrollment_id']; ?>">
                      <td><?= htmlspecialchars($row['student_number']); ?></td>
                      <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                      <td><?= htmlspecialchars($row['admission_type']); ?></td>
                      <td><?= htmlspecialchars($row['department_code']); ?></td>
                      <td>
                        <button class="btn btn-info btn-sm" onclick="viewTimetableDetails(<?= $row['enrollment_id']; ?>)">View Timetable</button>
                      </td>
                      <td><?= htmlspecialchars($row['created_at']); ?></td>
                      <td>
                        <span class="badge bg-<?= $row['receipt_status'] == 'Not Enrolled' ? 'warning' : ($row['receipt_status'] == 'Approved' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['receipt_status']); ?>
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-info btn-sm" onclick="showTransactionModal(<?= $row['enrollment_id']; ?>, '<?= $row['student_number']; ?>', '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>')">View Transactions</button>
                        <button class="btn btn-danger btn-sm" onclick="updateEnrollmentStatus(<?= $row['enrollment_id']; ?>, 'Rejected')">Reject</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center">No pending enrollments found</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Timetable Modal -->
        <div class="modal fade" id="timetableModal" tabindex="-1" aria-labelledby="timetableModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="timetableModalLabel">Timetable Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Section</th>
                      <th>Subject</th>
                      <th>Schedule</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="timetableDetails">
                    <!-- Content populated dynamically -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="editTimetableModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <form method="POST" action="enrollment.php">
                  <input type="hidden" id="editTimetableId" name="timetable_id">

                  <div class="mb-3">
                    <label for="editSection" class="form-label">Section</label>
                    <select id="editSection" class="form-control" required>
                      <option value="">Select Section</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="newTimetableId" class="form-label">Timetable</label>
                    <select id="newTimetableId" name="new_timetable_id" class="form-control" required>
                      <option value="">Select Timetable</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="editDay" class="form-label">Day</label>
                    <input type="text" id="editDay" class="form-control" disabled>
                  </div>

                  <div class="mb-3">
                    <label for="editStartTime" class="form-label">Start Time</label>
                    <input type="time" id="editStartTime" class="form-control" disabled>
                  </div>

                  <div class="mb-3">
                    <label for="editEndTime" class="form-label">End Time</label>
                    <input type="time" id="editEndTime" class="form-control" disabled>
                  </div>

                  <button type="submit" name="update_timetable" class="btn btn-primary">Save Changes</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main><!-- End #main -->

  <!-- Transaction Modal -->
  <div class="modal fade transaction-modal" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="transactionModalLabel">Transaction Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="modalEnrollmentId">
          <h6>Student Information</h6>
          <p><strong>Student Number:</strong> <span id="modalStudentNumber"></span></p>
          <p><strong>Student Name:</strong> <span id="modalStudentName"></span></p>
          <hr>
          <h6>Transaction History</h6>
          <table class="table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Type</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="transactionTableBody">
              <!-- Transactions will be dynamically loaded here -->
            </tbody>
          </table>
          <hr>
          <button type="button" class="btn btn-success" id="confirmEnrollmentButton" onclick="showConfirmationModal()">Confirm Enrollment</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div class="modal fade confirmation-modal" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmationModalLabel">Confirm Enrollment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to confirm the enrollment for <strong><span id="confirmStudentName"></span></strong> (Student Number: <strong><span id="confirmStudentNumber"></span></strong>)?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="finalConfirmEnrollmentButton">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showTransactionModal(enrollmentId, studentNumber, studentName) {
      // Set student information in the modal
      document.getElementById('modalStudentNumber').innerText = studentNumber;
      document.getElementById('modalStudentName').innerText = studentName;
      document.getElementById('modalEnrollmentId').value = enrollmentId; // Set the enrollmentId in the hidden input field

      // Fetch transaction details via AJAX
      fetch(`get_transactions.php?student_number=${studentNumber}`)
        .then(response => response.json())
        .then(data => {
          const transactionTableBody = document.getElementById('transactionTableBody');
          const confirmButton = document.getElementById('confirmEnrollmentButton');
          transactionTableBody.innerHTML = ''; // Clear previous data

          if (data.success && data.transactions.length > 0) {
            let hasEnrollmentTransaction = false;

            data.transactions.forEach(transaction => {
              if (transaction.payment_type === 'Enrollment') {
                hasEnrollmentTransaction = true;
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${transaction.id}</td>
                <td>${transaction.amount}</td>
                <td>${transaction.payment_method}</td>
                <td>${transaction.payment_type}</td>
                <td>${transaction.payment_date}</td>
              `;
                transactionTableBody.appendChild(row);
              }
            });

            // Enable or disable the confirm button based on transactions
            confirmButton.disabled = !hasEnrollmentTransaction;

            if (!hasEnrollmentTransaction) {
              transactionTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No enrollment transactions found</td></tr>';
            }
          } else {
            transactionTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No transactions found</td></tr>';
            confirmButton.disabled = true; // Disable the confirm button
          }

          // Show the transaction modal
          new bootstrap.Modal(document.getElementById('transactionModal')).show();
        })
        .catch(error => {
          console.error('Error fetching transactions:', error);
          alert('An error occurred while fetching transaction details.');
        });
    }

    function showConfirmationModal() {
      const studentName = document.getElementById('modalStudentName').innerText;
      const studentNumber = document.getElementById('modalStudentNumber').innerText;
      const enrollmentId = document.getElementById('modalEnrollmentId').value;

      document.getElementById('confirmStudentName').innerText = studentName;
      document.getElementById('confirmStudentNumber').innerText = studentNumber;

      const confirmButton = document.getElementById('finalConfirmEnrollmentButton');
      confirmButton.onclick = function() {
        updateEnrollmentStatus(enrollmentId, 'Paid');
        const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
        confirmationModal.hide();
      };

      new bootstrap.Modal(document.getElementById('confirmationModal')).show();
    }

    function updateEnrollmentStatus(enrollmentId, receiptStatus) {
      const form = new FormData();
      form.append('enrollment_id', enrollmentId);
      form.append('receipt_status', receiptStatus);

      fetch('enrollment.php', {
          method: 'POST',
          body: form,
        })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Reload the page to reflect changes
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Error handling enrollment status:', error);
          alert('An unexpected error occurred.');
        });
    }

    // Added event listeners for the filter inputs
    document.getElementById('filterDepartment').addEventListener('change', filterEnrollments);
    document.getElementById('filterStartDate').addEventListener('change', filterEnrollments);
    document.getElementById('filterEndDate').addEventListener('change', filterEnrollments);

    function filterEnrollments() {
      const department = document.getElementById('filterDepartment').value;
      const startDate = document.getElementById('filterStartDate').value;
      const endDate = document.getElementById('filterEndDate').value;
      const rows = document.querySelectorAll('.datatable tbody tr');

      rows.forEach(row => {
        const rowDepartment = row.cells[3].textContent.trim();
        const rowDate = row.cells[5].textContent.trim();

        const departmentMatch = department === '' || rowDepartment === department;
        const dateMatch = (!startDate || new Date(rowDate) >= new Date(startDate)) &&
          (!endDate || new Date(rowDate) <= new Date(endDate));

        if (departmentMatch && dateMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    function viewTimetableDetails(enrollmentId) {
      fetch(`enrollment.php?timetable_details=${enrollmentId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.json();
        })
        .then(data => {
          if (!data || data.length === 0) {
            alert("No timetable details found for this enrollment.");
            return;
          }

          const timetableDetails = document.getElementById('timetableDetails');
          timetableDetails.innerHTML = '';
          data.forEach(item => {
            timetableDetails.innerHTML += `
              <tr>
                  <td>${item.section_number}</td>
                  <td>${item.subject_code}</td>
                  <td>${item.schedule}</td>
                  <td>
                    <button class="btn btn-warning btn-sm" onclick="editTimetable(${item.id})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteTimetableFromEnrollment(${item.id})">Delete</button>
                  </td>
              </tr>
            `;
          });

          const modal = new bootstrap.Modal(document.getElementById('timetableModal'));
          modal.show();
        })
        .catch(error => console.error("Error fetching timetable details:", error));
    }

    function deleteTimetableRow(timetableId) {
      if (!confirm("Are you sure you want to delete this timetable entry?")) return;

      fetch(`enrollment.php?delete_timetable_id=${timetableId}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            alert(data.message);
            // Refresh the timetable details
            const modal = bootstrap.Modal.getInstance(document.getElementById('timetableModal'));
            modal.hide();
            setTimeout(() => location.reload(), 500); // Reload the page to reflect changes
          } else {
            alert(data.message);
          }
        })
        .catch(error => console.error("Error deleting timetable entry:", error));
    }

    function editTimetable(timetableId) {
      fetch(`enrollment.php?fetch_timetable_id=${timetableId}`)
        .then(response => response.json())
        .then(data => {
          // Populate current timetable details
          document.getElementById('editTimetableId').value = data.id;

          // Populate section dropdown
          fetch('enrollment.php?fetch_sections')
            .then(response => response.json())
            .then(sections => {
              const sectionDropdown = document.getElementById('editSection');
              sectionDropdown.innerHTML = `<option value="">Select Section</option>`;
              sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.text = section.section_number;
                sectionDropdown.appendChild(option);
              });
              sectionDropdown.value = data.section_id; // Pre-select current section
            });

          // Update timetable dropdown based on section
          document.getElementById('editSection').addEventListener('change', function() {
            const sectionId = this.value;
            fetch(`enrollment.php?fetch_subjects&section_id=${sectionId}`)
              .then(response => response.json())
              .then(timetables => {
                const timetableDropdown = document.getElementById('newTimetableId');
                timetableDropdown.innerHTML = `<option value="">Select Timetable</option>`;
                timetables.forEach(timetable => {
                  const option = document.createElement('option');
                  option.value = timetable.timetable_id;
                  option.text = `${timetable.subject_code} (${timetable.day_of_week} ${timetable.start_time}-${timetable.end_time})`;
                  option.dataset.day = timetable.day_of_week;
                  option.dataset.startTime = timetable.start_time;
                  option.dataset.endTime = timetable.end_time;
                  timetableDropdown.appendChild(option);
                });

                // Update day, start time, and end time on timetable selection
                timetableDropdown.addEventListener('change', function() {
                  const selectedOption = this.options[this.selectedIndex];
                  document.getElementById('editDay').value = selectedOption.dataset.day || '';
                  document.getElementById('editStartTime').value = selectedOption.dataset.startTime || '';
                  document.getElementById('editEndTime').value = selectedOption.dataset.endTime || '';
                });
              });
          });
          const modal = new bootstrap.Modal(document.getElementById('editTimetableModal'));
          modal.show();
        })
        .catch(error => console.error("Error fetching timetable details:", error));
    }

    function deleteTimetableFromEnrollment(timetableId) {
      if (!confirm("Are you sure you want to delete this timetable entry?")) return;

      fetch(`enrollment.php?delete_timetable_from_enrollment=${timetableId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json(); // Parse JSON response
        })
        .then(data => {
          if (data.status === 'success') {
            showPopupMessage(data.message, 'success');
            // Refresh the timetable modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('timetableModal'));
            if (modal) modal.hide();
            setTimeout(() => location.reload(), 500); // Reload to update changes
          } else {
            showPopupMessage(data.message, 'error');
          }
        })
        .catch(error => {
          console.error("Error deleting timetable entry:", error);
          showPopupMessage("An unexpected error occurred while deleting the timetable.", 'error');
        });
    }

    function showPopupMessage(message, type = 'success') {
      const popup = document.createElement('div');
      popup.className = `popup-message ${type}`;
      popup.innerText = message;

      popup.style.position = 'fixed';
      popup.style.top = '20px';
      popup.style.right = '20px';
      popup.style.padding = '15px';
      popup.style.zIndex = '1000';
      popup.style.borderRadius = '5px';
      popup.style.color = '#fff';
      popup.style.fontSize = '16px';
      popup.style.backgroundColor = type === 'success' ? 'green' : 'red';
      popup.style.opacity = '1';
      popup.style.transition = 'opacity 0.5s ease';

      document.body.appendChild(popup);

      setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => {
          popup.remove();
        }, 500);
      }, 3000);
    }

    window.onload = function() {
      <?php if (isset($_SESSION['error_message'])): ?>
        showPopupMessage('<?= $_SESSION['error_message']; ?>', 'error');
        <?php unset($_SESSION['error_message']); ?>
      <?php elseif (isset($_SESSION['success_message'])): ?>
        showPopupMessage('<?= $_SESSION['success_message']; ?>', 'success');
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
    };
  </script>



  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../assets/vendor/quill/quill.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>

</body>

</html>