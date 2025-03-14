<?php
require('../database.php');
require_once 'session.php';
checkAccess('Student'); // Ensure only users with the 'Student' role can access this page

// Fetch the student number
$student_number = null;
if (isset($_SESSION['user_id'])) {
  $stmt = $conn->prepare("SELECT student_number FROM sms3_students WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $studentData = $result->fetch_assoc();
  $student_number = $studentData['student_number'] ?? null;
  $stmt->close();
}

if (!$student_number) {
  die("Student number not found.");
}

// Query to fetch student details and related data
$query = "
    SELECT 
        s.student_number, s.first_name, s.middle_name, s.last_name, s.year_level, 
        s.admission_type, d.department_name, d.department_code, 
        ay.academic_year, sem.name AS semester,
        s.timetable_1, s.timetable_2, s.timetable_3, s.timetable_4, 
        s.timetable_5, s.timetable_6, s.timetable_7, s.timetable_8
    FROM sms3_students s
    LEFT JOIN sms3_departments d ON s.department_id = d.id
    LEFT JOIN sms3_academic_years ay ON s.academic_year = ay.id
    LEFT JOIN sms3_semesters sem ON sem.status = 'Active'
    WHERE s.student_number = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
  die("Student details not found.");
}

// Build timetable IDs
$timetable_ids = array_filter([
  $student['timetable_1'],
  $student['timetable_2'],
  $student['timetable_3'],
  $student['timetable_4'],
  $student['timetable_5'],
  $student['timetable_6'],
  $student['timetable_7'],
  $student['timetable_8']
]);

$timetables = [];
if (!empty($timetable_ids)) {
  // Query to fetch timetable data
  $timetable_placeholders = implode(',', array_fill(0, count($timetable_ids), '?'));
  $timetable_query = "
      SELECT 
          tt.day_of_week, 
          TIME_FORMAT(tt.start_time, '%H:%i') AS start_time, 
          TIME_FORMAT(tt.end_time, '%H:%i') AS end_time, 
          subj.subject_code, 
          subj.subject_name, 
          CONCAT(d.department_code, '-', sec.section_number) AS section_with_prefix, 
          room.room_name
      FROM sms3_timetable tt
      LEFT JOIN sms3_subjects subj ON tt.subject_id = subj.id
      LEFT JOIN sms3_sections sec ON tt.section_id = sec.id
      LEFT JOIN sms3_departments d ON sec.department_id = d.id
      LEFT JOIN sms3_rooms room ON tt.room_id = room.id
      WHERE tt.id IN ($timetable_placeholders)
      ORDER BY tt.day_of_week, tt.start_time
  ";
  $stmt = $conn->prepare($timetable_query);
  $stmt->bind_param(str_repeat('i', count($timetable_ids)), ...$timetable_ids);
  $stmt->execute();
  $timetable_result = $stmt->get_result();
  $timetables = $timetable_result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Current Enrollment</title>
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

      <li class="nav-heading">Enrollment</li>

      <li class="nav-item">
        <a class="nav-link " data-bs-target="#system-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Enrollment</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="system-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          <li>
            <a href="current_enrollment.php" class="active">
              <i class="bi bi-circle"></i><span>Current Enrollment</span>
            </a>
          </li>
          <li>
            <a href="upcoming_enrollment.php">
              <i class="bi bi-circle"></i><span>Upcoming Enrollment</span>
            </a>
          </li>
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
          <li class="breadcrumb-item active">Current Enrollment</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Student Information</h5>
            <div class="row">
              <div class="col-md-4"><strong>Student Number:</strong> <?= htmlspecialchars($student['student_number']) ?></div>
              <div class="col-md-4"><strong>Full Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?></div>
              <div class="col-md-4"><strong>Admission Type:</strong> <?= htmlspecialchars($student['admission_type']) ?></div>
            </div>
            <div class="row mt-3">
              <div class="col-md-4"><strong>Department:</strong> <?= htmlspecialchars($student['department_name']) ?></div>
              <div class="col-md-4"><strong>Year Level:</strong> <?= htmlspecialchars($student['year_level']) ?></div>
              <div class="col-md-4"><strong>Academic Year:</strong> <?= htmlspecialchars($student['academic_year']) ?> - <?= htmlspecialchars($student['semester']) ?></div>
            </div>
          </div>

        </div>
      </div>

      <!-- Timetable -->
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Timetable</h5>
          <?php if (empty($timetables)): ?>
            <p>No timetable data available.</p>
          <?php else: ?>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Section</th>
                  <th>Subject Code</th>
                  <th>Subject Name</th>
                  <th>Day</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Room</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($timetables as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['section_with_prefix']) ?></td>
                    <td><?= htmlspecialchars($row['subject_code']) ?></td>
                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                    <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                    <td><?= htmlspecialchars($row['start_time']) ?></td>
                    <td><?= htmlspecialchars($row['end_time']) ?></td>
                    <td><?= htmlspecialchars($row['room_name']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      </div>
    </section>

  </main><!-- End #main -->



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