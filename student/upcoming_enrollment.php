<?php
require('../database.php');
require_once 'session.php';
checkAccess('Student'); // Ensure only users with the 'student' role can access this page

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch sections based on student's department, year level, and semester
$studentId = $_SESSION['user_id'];
$studentDepartment = $_SESSION['department'];
$studentYearLevel = $_SESSION['year_level'];
$currentSemester = $_SESSION['semester'];
$departmentCodePrefix = substr($studentYearLevel, 0, 1) . ($currentSemester === '1st Semester' ? '1' : '2');

// Check if the student is already enrolled or has a pending enrollment
$enrollmentCheckQuery = "
    SELECT 
        (SELECT COUNT(*) FROM sms3_pending_enrollment WHERE student_id = ?) AS pending_count,
        (SELECT COUNT(*) FROM sms3_students WHERE id = ? AND status = 'Enrolled') AS enrolled_count
";
$enrollmentCheckStmt = $conn->prepare($enrollmentCheckQuery);
$enrollmentCheckStmt->bind_param("ii", $studentId, $studentId);
$enrollmentCheckStmt->execute();
$enrollmentCheckResult = $enrollmentCheckStmt->get_result();
$enrollmentCheck = $enrollmentCheckResult->fetch_assoc();
$enrollmentCheckStmt->close();

$isEnrolled = $enrollmentCheck['pending_count'] > 0 || $enrollmentCheck['enrolled_count'] > 0;

// Redirect to the dashboard if the student is already enrolled or has a pending enrollment
if ($isEnrolled) {
  header("Location: Dashboard.php");
  exit;
}

$query = "SELECT s.id, s.section_number, s.available, 
                 tt.id AS timetable_id, tt.day_of_week, tt.start_time, tt.end_time, 
                 subj.subject_name
          FROM sms3_sections s
          JOIN sms3_timetable tt ON s.id = tt.section_id
          JOIN sms3_subjects subj ON tt.subject_id = subj.id
          WHERE s.department_id = (SELECT id FROM sms3_departments WHERE department_name = ?) 
          AND s.year_level = ? 
          AND s.section_number LIKE ?
          ORDER BY s.section_number";

$stmt = $conn->prepare($query);
$sectionPattern = $departmentCodePrefix . '%';
$stmt->bind_param("sis", $studentDepartment, $studentYearLevel, $sectionPattern);
$stmt->execute();
$sectionsResult = $stmt->get_result();

$sections = [];
while ($row = $sectionsResult->fetch_assoc()) {
  if ($row['available'] > 0) {
    $sections[$row['id']]['section_number'] = $row['section_number'];
    $sections[$row['id']]['available'] = $row['available'];
    $sections[$row['id']]['subjects'][] = $row;
  }
}
$stmt->close();

// Function to handle enrollment
function enrollInTimetables($studentId, $selectedTimetables)
{
  global $conn;

  // Check if the student already has a pending enrollment
  $checkQuery = "SELECT COUNT(*) AS count FROM sms3_pending_enrollment WHERE student_id = ?";
  $checkStmt = $conn->prepare($checkQuery);
  $checkStmt->bind_param("i", $studentId);
  $checkStmt->execute();
  $checkResult = $checkStmt->get_result();
  $checkData = $checkResult->fetch_assoc();
  $checkStmt->close();

  if ($checkData['count'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You already have a pending enrollment.']);
    exit;
  }

  // Prepare the INSERT statement with dynamic columns for timetables
  $timetablePlaceholders = implode(', ', array_map(fn($n) => "timetable_$n", range(1, count($selectedTimetables))));

  $query = "
        INSERT INTO sms3_pending_enrollment (student_id, $timetablePlaceholders)
        VALUES (?, " . str_repeat('?, ', count($selectedTimetables) - 1) . "?)
    ";

  $stmt = $conn->prepare($query);

  // Merge the student ID and timetable IDs for parameter binding
  $params = array_merge([$studentId], $selectedTimetables);
  $stmt->bind_param(str_repeat('i', count($params)), ...$params);

  if ($stmt->execute()) {
    // Reduce the available slots for the selected section
    $updateQuery = "UPDATE sms3_sections SET available = available - 1 WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $selectedTimetables[0]); // Assuming the first timetable ID corresponds to the section
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Enrollment successful!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
  }

  $stmt->close();
}

// Handle AJAX request for enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json');

  $inputData = json_decode(file_get_contents('php://input'), true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received.']);
    exit;
  }

  if (!isset($inputData['enroll'], $inputData['timetables'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing enrollment data.']);
    exit;
  }

  $selectedTimetables = array_slice($inputData['timetables'], 0, 8); // Limit to 8 timetables

  enrollInTimetables($studentId, $selectedTimetables);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Upcoming Enrollment</title>
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

  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">

  <style>
    .day-button {
      margin-right: 10px;
      padding: 5px 10px;
    }

    .day-button.active,
    .section-button.active {
      background-color: #007bff;
      color: #fff;
    }

    .day-button:hover,
    .section-button:hover {
      background-color: gray;
      color: #fff;
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

      <li class="nav-heading">Enrollment</li>

      <li class="nav-item">
        <a class="nav-link " data-bs-target="#system-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Enrollment</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="system-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          <li>
            <a href="current_enrollment.php">
              <i class="bi bi-circle"></i><span>Current Enrollment</span>
            </a>
          </li>
          <?php if (!$isEnrolled): ?>
            <li>
              <a href="upcoming_enrollment.php" class="active">
                <i class="bi bi-circle"></i><span>Upcoming Enrollment</span>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </li><!-- End System Nav -->

      <hr class="sidebar-divider">
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Current Enrollment</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Enrollment</li>
          <li class="breadcrumb-item active">Upcoming Enrollment</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Filter Sections by Days</h5>
            <div class="mb-3">
              <label class="form-label">Select Weekdays:</label>
              <div id="weekday-buttons" class="d-flex flex-wrap">
                <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day): ?>
                  <button type="button" class="btn btn-outline-primary day-button" data-day="<?= $day ?>" onclick="toggleDay(this)">
                    <?= $day ?>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Available Sections:</label>
              <div id="section-buttons" class="d-flex flex-wrap">
                <!-- Section buttons populated here -->
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
            <h5 class="card-title">Section Schedule</h5>
            <table style="width: 100%; min-width: 800px;" id="schedule-table" class="table table-striped" style="display: none;">
              <thead>
                <tr>
                  <th>Section</th>
                  <th>Subject</th>
                  <th>Day</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                </tr>
              </thead>
              <tbody id="schedule-body"></tbody>
            </table>
            <button id="enroll-button" class="btn btn-success" style="display: none;">Enroll</button>
          </div>
        </div>

      </div>
    </section>

  </main><!-- End #main -->

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="modal fade" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmationModalLabel">Confirm Enrollment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to enroll in this section?</p>
        </div>
        <div class="modal-footer">
          <button id="confirmEnroll" class="btn btn-primary">Yes</button>
          <button id="cancelEnroll" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const sectionsData = <?= json_encode($sections) ?>;
    const scheduleTable = document.getElementById('schedule-table');
    const scheduleBody = document.getElementById('schedule-body');
    let selectedDays = [];
    let selectedSection = null;

    function toggleDay(button) {
      const day = button.getAttribute('data-day');
      if (button.classList.contains('active')) {
        button.classList.remove('active');
        selectedDays = selectedDays.filter(d => d !== day);
      } else {
        if (selectedDays.length < 3) {
          button.classList.add('active');
          selectedDays.push(day);
        } else {
          alert("You can select up to 3 days.");
        }
      }

      clearScheduleTable();
      if (selectedDays.length === 0) {
        document.getElementById('section-buttons').innerHTML = '';
      } else {
        filterSectionsByDays();
      }
    }

    function filterSectionsByDays() {
      const sectionButtons = document.getElementById('section-buttons');
      sectionButtons.innerHTML = '';

      for (const [sectionId, sectionData] of Object.entries(sectionsData)) {
        const sectionDays = sectionData.subjects.map(subject => subject.day_of_week);
        const containsAllSelectedDays = selectedDays.every(day => sectionDays.includes(day));

        if (containsAllSelectedDays && sectionData.available > 0) {
          const button = document.createElement('button');
          button.classList.add('btn', 'btn-outline-primary', 'section-button', 'me-2', 'mb-2');
          button.textContent = `Section ${sectionData.section_number} (Slots: ${sectionData.available})`;
          button.setAttribute('data-section-id', sectionId);
          button.onclick = () => toggleSectionSelection(button);
          sectionButtons.appendChild(button);
        }
      }
    }

    function toggleSectionSelection(button) {
      if (button.classList.contains('active')) {
        button.classList.remove('active');
        selectedSection = null;
        clearScheduleTable();
      } else {
        document.querySelectorAll('.section-button').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        selectedSection = button.getAttribute('data-section-id');
        displaySectionDetails(selectedSection);
      }
    }

    function displaySectionDetails(sectionId) {
      scheduleBody.innerHTML = '';
      const section = sectionsData[sectionId];
      section.subjects.forEach(subject => {
        const row = document.createElement('tr');
        row.innerHTML = `
                <td>${section.section_number}</td>
                <td>${subject.subject_name}</td>
                <td>${subject.day_of_week}</td>
                <td>${subject.start_time}</td>
                <td>${subject.end_time}</td>
            `;
        scheduleBody.appendChild(row);
      });
      scheduleTable.style.display = section.subjects.length ? 'table' : 'none';
      document.getElementById('enroll-button').style.display = section.subjects.length ? 'block' : 'none';
    }

    function clearScheduleTable() {
      scheduleBody.innerHTML = '';
      scheduleTable.style.display = 'none';
      document.getElementById('enroll-button').style.display = 'none';
    }

    document.getElementById('enroll-button').onclick = function() {
      if (!selectedSection) {
        alert("Please select a section before enrolling.");
        return;
      }

      const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
      confirmationModal.show();

      document.getElementById('confirmEnroll').onclick = () => {
        confirmationModal.hide();

        const selectedSections = [selectedSection];
        const selectedTimetables = sectionsData[selectedSection].subjects.map(sub => sub.timetable_id);

        fetch('upcoming_enrollment.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              enroll: true,
              sections: selectedSections,
              timetables: selectedTimetables
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              alert(data.message);
              window.location.href = 'Dashboard.php'; // Redirect to Dashboard
            } else {
              console.error("Server error:", data.message);
              alert(`Enrollment failed: ${data.message}`);
            }
          })
          .catch(error => {
            console.error("Error during enrollment:", error);
            alert("An error occurred while enrolling. Please try again later.");
          });
      };

      document.getElementById('cancelEnroll').onclick = () => {
        confirmationModal.hide();
      };
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