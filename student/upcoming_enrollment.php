<?php
require('../database.php');
require_once 'session.php';
checkAccess('Student'); // Ensure only users with the 'student' role can access this page

// Fetch sections based on student's department, year level, and semester
$studentId = $_SESSION['user_id'];
$studentDepartment = $_SESSION['department'];
$studentYearLevel = $_SESSION['year_level'];
$currentSemester = $_SESSION['semester'];
$departmentCodePrefix = substr($studentYearLevel, 0, 1) . ($currentSemester === '1st Semester' ? '1' : '2');

$query = "SELECT s.id, s.section_number, s.available, tt.id AS timetable_id, tt.day_of_week, tt.start_time, tt.end_time, subj.subject_name
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
  <link href="../assets/img/favicon.png" rel="icon">
  <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

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
    .day-button.active, .section-button.active {
        background-color: #007bff;
        color: #fff;
    }
    .day-button:hover, .section-button:hover {
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
            <img src="../assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2">K. Anderson</span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>Kevin Anderson</h6>
              <span>Web Designer</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
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

      <div class="flex items-center w-full p-1 pl-6" style="display: flex; align-items: center; padding: 3px; width: 40px; background-color: transparent; height: 4rem;">
        <div class="flex items-center justify-center" style="display: flex; align-items: center; justify-content: center;">
            <img src="https://elc-public-images.s3.ap-southeast-1.amazonaws.com/bcp-olp-logo-mini2.png" alt="Logo" style="width: 30px; height: auto;">
        </div>
      </div>

      <div style="display: flex; flex-direction: column; align-items: center; padding: 16px;">
        <div style="display: flex; align-items: center; justify-content: center; width: 96px; height: 96px; border-radius: 50%; background-color: #334155; color: #e2e8f0; font-size: 48px; font-weight: bold; text-transform: uppercase; line-height: 1;">
            LC
        </div>
        <div style="display: flex; flex-direction: column; align-items: center; margin-top: 24px; text-align: center;">
            <div style="font-weight: 500; color: #fff;">
                Name
            </div>
            <div style="margin-top: 4px; font-size: 14px; color: #fff;">
                ID
            </div>
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
        <a class="nav-link collapsed" data-bs-target="#system-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Enrollment</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="system-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="current_enrollment.php">
              <i class="bi bi-circle"></i><span>Current Enrollemnt</span>
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
            <div id="section-buttons" class="d-flex flex-wrap">
                <!-- Section buttons populated here -->
            </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
            <h5 class="card-title">Section Schedule</h5>
            <table id="schedule-table" class="table table-striped" style="display: none;">
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
            <button id="enroll-button" class="btn btn-success" style="display: none;" onclick="enrollInSection()">Enroll</button>
        </div>
      </div>

    </div>
    </section>

  </main><!-- End #main -->

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
      
      // Clear sections if no days are selected
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

          if (containsAllSelectedDays) {
              const button = document.createElement('button');
              button.classList.add('btn', 'btn-outline-primary', 'section-button');
              button.textContent = `Section ${sectionData.section_number} (Available slots: ${sectionData.available})`;
              button.setAttribute('data-section-id', sectionId);
              button.onclick = () => selectSection(button);
              sectionButtons.appendChild(button);
          }
      }
    }

    function selectSection(button) {
        document.querySelectorAll('.section-button').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        selectedSection = button.getAttribute('data-section-id');
        displaySectionDetails(selectedSection);
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

    function enrollInSection() {
        if (!selectedSection) return;

        fetch('enroll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: <?= $studentId ?>, section_id: selectedSection })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => console.error('Enrollment error:', error));
    }
  </script>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>XXXXXX</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      BCP
    </div>
  </footer><!-- End Footer -->

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