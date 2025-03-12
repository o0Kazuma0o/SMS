<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

$query = "
    SELECT 
        s.*, 
        s.id AS student_id, 
        d.department_code AS department, 
        ay.academic_year AS academic_year_label, 
        GROUP_CONCAT(t.id) AS timetable_ids 
    FROM sms3_students s
    LEFT JOIN sms3_departments d ON s.department_id = d.id
    LEFT JOIN sms3_academic_years ay ON s.academic_year = ay.id
    LEFT JOIN sms3_timetable t ON t.id IN (
        s.timetable_1, 
        s.timetable_2, 
        s.timetable_3, 
        s.timetable_4, 
        s.timetable_5, 
        s.timetable_6, 
        s.timetable_7, 
        s.timetable_8
    )
    GROUP BY s.id
    ORDER BY s.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$currentSemester = getCurrentActiveSemester($conn);

// Fetch timetable details
if (isset($_GET['timetable_details'])) {
  $studentId = intval($_GET['timetable_details']);
  $query = "
      SELECT t.id, sec.section_number, sub.subject_code, 
             CONCAT(t.day_of_week, ' ', TIME_FORMAT(t.start_time, '%H:%i'), '-', TIME_FORMAT(t.end_time, '%H:%i')) AS schedule
      FROM sms3_students s
      JOIN sms3_timetable t ON t.id IN (s.timetable_1, s.timetable_2, s.timetable_3, s.timetable_4, s.timetable_5, s.timetable_6, s.timetable_7, s.timetable_8)
      JOIN sms3_sections sec ON t.section_id = sec.id
      JOIN sms3_subjects sub ON t.subject_id = sub.id
      WHERE s.id = ?
  ";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $studentId);
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

// Update a specific timetable entry in sms3_students
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
      FROM sms3_students
      WHERE ? IN (timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8)
  ");
  $stmt->bind_param("iiiiiiii", $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();
  $stmt->close();

  if (!$data) {
    $_SESSION['error_message'] = "Timetable entry not found in student.";
    header("Location: students.php");
    exit;
  }

  $timetableColumn = $data['timetable_column']; // Identified timetable column
  if (!$timetableColumn) {
    $_SESSION['error_message'] = "Unable to identify timetable column.";
    header("Location: students.php");
    exit;
  }

  // Update the identified column with the new timetable ID
  $updateQuery = "UPDATE sms3_students SET $timetableColumn = ? WHERE id = ?";
  $updateStmt = $conn->prepare($updateQuery);
  $updateStmt->bind_param("ii", $newTimetableId, $data['id']);

  if ($updateStmt->execute()) {
    $_SESSION['success_message'] = "Timetable updated successfully.";
  } else {
    $_SESSION['error_message'] = "Failed to update timetable.";
  }
  $updateStmt->close();

  header("Location: students.php");
  exit;
}

// Delete timetable from students
if (isset($_GET['delete_timetable_from_student'])) {
  $timetableId = intval($_GET['delete_timetable_from_student']);

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
                                  'timetable_8'))))))) AS timetable_column
      FROM sms3_students
      WHERE ? IN (timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8)
  ");
  $stmt->bind_param("iiiiiiii", $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId, $timetableId);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();
  $stmt->close();

  if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Timetable entry not found in student.']);
    exit;
  }

  $timetableColumn = $data['timetable_column']; // Identified timetable column
  if (!$timetableColumn) {
    echo json_encode(['status' => 'error', 'message' => 'Unable to identify timetable column.']);
    exit;
  }

  // Update the column to NULL
  $updateQuery = "UPDATE sms3_students SET $timetableColumn = NULL WHERE id = ?";
  $updateStmt = $conn->prepare($updateQuery);
  $updateStmt->bind_param("i", $data['id']);

  if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Timetable entry deleted successfully.']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete timetable entry.']);
  }
  $updateStmt->close();
  exit; // Ensure no further output is sent
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Students</title>
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
              <a class="dropdown-item d-flex align-items-center" href="#">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="#">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
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
      </li><!-- End System Nav -->

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

      <hr class="sidebar-divider">

      <li class="nav-heading">MANAGE USER</li>
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

      <hr class="sidebar-divider">
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Students</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Students</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="col-lg-12">
        <div class="card">
          <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
            <h5 class="card-title">List of Student</h5>

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
                <label for="filterYearLevel" class="form-label">Year Level</label>
                <select id="filterYearLevel" class="form-select">
                  <option value="">All Year Levels</option>
                  <option value="1">1st Year</option>
                  <option value="2">2nd Year</option>
                  <option value="3">3rd Year</option>
                  <option value="4">4th Year</option>
                </select>
              </div>
            </div>

            <!-- Table with stripped rows -->
            <table style="width: 100%; min-width: 800px;" class="table datatable">
              <thead>
                <tr>
                  <th>Student Number</th>
                  <th>Name</th>
                  <th>Academic Year</th>
                  <th>Program</th>
                  <th>Year Level</th>
                  <th>Information</th>
                  <th>Requirements</th>
                  <th>Schedule</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['student_number']); ?></td>
                      <td>
                        <?= htmlspecialchars($row['first_name']) . ' ' .
                          (!empty($row['middle_name']) ? htmlspecialchars($row['middle_name']) . ' ' : '') .
                          htmlspecialchars($row['last_name']); ?>
                      </td>
                      <td><?= htmlspecialchars($row['academic_year_label']); ?></td>
                      <td><?= htmlspecialchars($row['department']); ?></td>
                      <td><?= htmlspecialchars($row['year_level']); ?></td>
                      <td>
                        <button class="btn btn-info btn-sm" onclick="viewInformation(<?= $row['id'] ?>)">View Information</button>
                      </td>
                      <td>
                        <button class="btn btn-info btn-sm" onclick="viewRequirements(<?= $row['id'] ?>)">Requirements</button>
                      </td>
                      <td>
                        <button class="btn btn-info btn-sm" onclick="viewTimetableDetails(<?= $row['id']; ?>)">View Timetable</button>
                      </td>
                      <td>
                        <span class="badge bg-<?= $row['status'] == 'Not Enrolled' ? 'warning' : ($row['status'] == 'Approved' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="9" class="text-center">No students found</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
            <!-- End Table with stripped rows -->
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
                <form method="POST" action="students.php">
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

  <div class="modal fade" id="informationModal" tabindex="-1" aria-labelledby="informationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="informationModalLabel">Admission Information</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="informationContent">
          <!-- Information will be loaded here dynamically -->
        </div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('filterDepartment').addEventListener('change', filterStudents);
    document.getElementById('filterYearLevel').addEventListener('change', filterStudents);

    function filterStudents() {
      const department = document.getElementById('filterDepartment').value;
      const yearLevel = document.getElementById('filterYearLevel').value;
      const rows = document.querySelectorAll('.datatable tbody tr');

      rows.forEach(row => {
        const rowDepartment = row.cells[3].textContent.trim();
        const rowYearLevel = row.cells[4].textContent.trim();

        const departmentMatch = department === '' || rowDepartment === department;
        const yearLevelMatch = yearLevel === '' || rowYearLevel === yearLevel;

        if (departmentMatch && yearLevelMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    function viewInformation(id) {
      // Fetch additional information using AJAX
      fetch('get_student_info.php?id=' + id)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Populate the modal content
            const info = data.info;
            const content = `
          <h4>Campus Branch</h4>
          <p><strong>Selected Branch:</strong> ${info.branch}</p>
          <hr>
          <h4>Basic Information</h4>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Full Name:</strong> ${info.first_name} ${info.middle_name} ${info.last_name}</p>
              <p><strong>Sex:</strong> ${info.sex}</p>
              <p><strong>Birthday:</strong> ${info.birthday}</p>
              <p><strong>Contact Number:</strong> ${info.contact_number}</p>
              <p><strong>Email:</strong> ${info.email}</p>
              <p><strong>Address:</strong> ${info.address}</p>
              <p><strong>Facebook Name:</strong> ${info.facebook_name}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Admission Type:</strong> ${info.admission_type}</p>
              ${info.admission_type === 'Returnee' ? `<p><strong>Old Student Number:</strong> ${info.old_student_number}</p>` : ''}
              <p><strong>Program:</strong> ${info.department_name}</p>
              <p><strong>Year Level:</strong> ${info.year_level}</p>
              <p><strong>Working Student:</strong> ${info.working_student === 'Yes' ? 'Yes' : 'No'}</p>
              <p><strong>Civil Status:</strong> ${info.civil_status}</p>
              <p><strong>Religion:</strong> ${info.religion}</p>
            </div>
          </div>
          <hr>
          <h4>Parent/Guardian Information</h4>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Father's Full Name:</strong> ${info.father_name}</p>
              <p><strong>Mother's Full Name:</strong> ${info.mother_name}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Guardian's Full Name:</strong> ${info.guardian_name}</p>
              <p><strong>Guardian's Occupation:</strong> ${info.occupation}</p>
              <p><strong>Guardian's Contact Number:</strong> ${info.guardian_contact}</p>
              <p><strong>Guardian's member of 4ps:</strong> ${info.member4ps === 'Yes' ? 'Yes' : 'No'}</p>
            </div>
          </div>
          <hr>
          <h4>Educational Background</h4>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Last School Attended:</strong> ${info.last_school}</p>
              <p><strong>Last School Year Attended:</strong> ${info.last_school_year}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Primary School Attended:</strong> ${info.primary_school}</p>
              <p><strong>Year Graduated:</strong> ${info.primary_year}</p>
              <p><strong>Secondary School Attended:</strong> ${info.secondary_school}</p>
              <p><strong>Year Graduated:</strong> ${info.secondary_year}</p>
            </div>
          </div>
          <hr>
          <h4>Requirements</h4>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Form 138:</strong> ${info.form138 || 'Not Applicable'}</p>
              <p><strong>Good Moral Certificate:</strong> ${info.good_moral || 'Not Applicable'}</p>
              <p><strong>Form 137:</strong> ${info.form137 || 'Not Applicable'}</p>
              <p><strong>Birth Certificate:</strong> ${info.birth_certificate || 'Not Applicable'}</p>
              <p><strong>Barangay Clearance:</strong> ${info.brgy_clearance || 'Not Applicable'}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Honorable Dismissal:</strong> ${info.honorable_dismissal || 'Not Applicable'}</p>
              <p><strong>Transcript of Records:</strong> ${info.transcript_of_records || 'Not Applicable'}</p>
              <p><strong>Certificate of Grades:</strong> ${info.certificate_of_grades || 'Not Applicable'}</p>
            </div>
          </div>
          <hr>
          <h4>Referral</h4>
          <p><strong>How did you hear about our school?</strong> ${info.referral_source}</p>
          <hr>
        `;

            document.getElementById('informationContent').innerHTML = content;
            // Show the modal
            new bootstrap.Modal(document.getElementById('informationModal')).show();
          } else {
            alert('Failed to fetch admission information.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while fetching the information.');
        });
    }

    function viewTimetableDetails(studentId) {
      fetch(`students.php?timetable_details=${studentId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.json();
        })
        .then(data => {
          if (!data || data.length === 0) {
            alert("No timetable details found for this student.");
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
                    <button class="btn btn-danger btn-sm" onclick="deleteTimetableFromStudents(${item.id})">Delete</button>
                  </td>
              </tr>
            `;
          });

          const modal = new bootstrap.Modal(document.getElementById('timetableModal'));
          modal.show();
        })
        .catch(error => console.error("Error fetching timetable details:", error));
    }

    function editTimetable(timetableId) {
      fetch(`students.php?fetch_timetable_id=${timetableId}`)
        .then(response => response.json())
        .then(data => {
          // Populate current timetable details
          document.getElementById('editTimetableId').value = data.id;

          // Populate section dropdown
          fetch('students.php?fetch_sections')
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
            fetch(`students.php?fetch_subjects&section_id=${sectionId}`)
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

    function deleteTimetableFromStudents(timetableId) {
      if (!confirm("Are you sure you want to delete this timetable entry?")) return;

      fetch(`students.php?delete_timetable_from_student=${timetableId}`)
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