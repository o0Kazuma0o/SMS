<?php
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

$departmentsResult = $conn->query("SELECT id, department_name FROM sms3_departments");
if (!$departmentsResult) {
  die("Department query failed: " . $conn->error);
}
$departments = [];
while ($row = $departmentsResult->fetch_assoc()) {
  $departments[] = $row;
}

// For JSON encoding
$jsonDepartments = json_encode($departments, JSON_THROW_ON_ERROR);

// Function to generate student number
function generateStudentNumber($conn)
{
  $yearPrefix = date('y'); // e.g., '24' for 2024

  $result = $conn->query("SELECT student_number FROM sms3_temp_enroll ORDER BY id DESC LIMIT 1");
  $lastStudentNumber = $result->fetch_assoc();

  if ($lastStudentNumber) {
    $lastNumber = intval(substr($lastStudentNumber['student_number'], 2)); // Extract last 6 digits
    $newNumber = $lastNumber + 1;
  } else {
    $newNumber = 100001; // Starting number for first student
  }

  return $yearPrefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT); // e.g., "24100001"
}

// Handle status update requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admission_id'], $_POST['status'])) {
  $admissionId = intval($_POST['admission_id']);
  $status = $_POST['status'];
  $userId = $_SESSION['user_id'];

  if ($status === 'Temporarily Enrolled') {
    // Move record to sms3_temp_enroll on temporary enrollment
    $stmt = $conn->prepare("SELECT * FROM sms3_pending_admission WHERE id = ?");
    $stmt->bind_param('i', $admissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $admissionData = $result->fetch_assoc();
    $stmt->close();

    if ($admissionData) {
      // Use old student number if admission type is Returnee
      $studentNumber = $admissionData['admission_type'] === 'Returnee' ? $admissionData['old_student_number'] : generateStudentNumber($conn);

      // Insert data into sms3_temp_enroll
      $stmt = $conn->prepare("INSERT INTO sms3_temp_enroll (
        student_number, first_name, middle_name, last_name, department_id, branch, admission_type, 
        year_level, sex, civil_status, religion, birthday, email, contact_number,
        address, guardian_name, guardian_contact, primary_school, primary_year, 
        secondary_school, secondary_year, last_school, last_school_year, referral_source, working_student, member4ps,
        form138, good_moral, form137, birth_certificate, brgy_clearance,
        honorable_dismissal, transcript_of_records, certificate_of_grades, status, receipt_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      $form138 = isset($_POST['form138']) ? ($_POST['form138'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['form138_shown']) ? 'To Be Followed' : NULL);
      $form137 = isset($_POST['form137']) ? ($_POST['form137'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['form137_shown']) ? 'To Be Followed' : NULL);
      $goodMoral = isset($_POST['good_moral']) ? ($_POST['good_moral'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['good_moral_shown']) ? 'To Be Followed' : NULL);
      $birthCertificate = isset($_POST['birth_certificate']) ? ($_POST['birth_certificate'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['birth_certificate_shown']) ? 'To Be Followed' : NULL);
      $brgyClearance = isset($_POST['brgy_clearance']) ? ($_POST['brgy_clearance'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['brgy_clearance_shown']) ? 'To Be Followed' : NULL);
      $honorableDismissal = isset($_POST['honorable_dismissal']) ? ($_POST['honorable_dismissal'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['honorable_dismissal_shown']) ? 'To Be Followed' : NULL);
      $transcriptOfRecords = isset($_POST['transcript_of_records']) ? ($_POST['transcript_of_records'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['transcript_of_records_shown']) ? 'To Be Followed' : NULL);
      $certificateOfGrades = isset($_POST['certificate_of_grades']) ? ($_POST['certificate_of_grades'] === 'on' ? 'Submitted' : 'To Be Followed') : (isset($_POST['certificate_of_grades_shown']) ? 'To Be Followed' : NULL);
      $status = 'Temporarily Enrolled';
      $receiptStatus = 'Not Paid';

      $stmt->bind_param(
        "ssssssssssssssssssssssssssssssssssss",
        $studentNumber,
        $admissionData['first_name'],
        $admissionData['middle_name'],
        $admissionData['last_name'],
        $admissionData['department_id'],
        $admissionData['branch'],
        $admissionData['admission_type'],
        $admissionData['year_level'],
        $admissionData['sex'],
        $admissionData['civil_status'],
        $admissionData['religion'],
        $admissionData['birthday'],
        $admissionData['email'],
        $admissionData['contact_number'],
        $admissionData['address'],
        $admissionData['guardian_name'],
        $admissionData['guardian_contact'],
        $admissionData['primary_school'],
        $admissionData['primary_year'],
        $admissionData['secondary_school'],
        $admissionData['secondary_year'],
        $admissionData['last_school'],
        $admissionData['last_school_year'],
        $admissionData['referral_source'],
        $admissionData['working_student'],
        $admissionData['member4ps'],
        $form138,
        $goodMoral,
        $form137,
        $birthCertificate,
        $brgyClearance,
        $honorableDismissal,
        $transcriptOfRecords,
        $certificateOfGrades,
        $status,
        $receiptStatus
      );

      if ($stmt->execute()) {
        $updateStmt = $conn->prepare("UPDATE sms3_pending_admission SET status = 'Temporarily Enrolled' WHERE id = ?");
        $updateStmt->bind_param('i', $admissionId);
        $updateStmt->execute();
        $updateStmt->close();

        // Log the audit entry
        logAudit($conn, $userId, 'ADD', 'sms3_temp_enroll', $admissionId, [
          'student_number' => $studentNumber
        ]);

        echo json_encode(['success' => true, 'message' => 'Student moved to temporary enrollment successfully!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert student record.']);
      }
      $stmt->close();
    } else {
      echo json_encode(['success' => false, 'message' => 'Admission record not found.']);
    }
  }
  exit;
}

// Fetch all pending admissions
$query = "SELECT a.*, d.department_code AS department 
          FROM sms3_pending_admission a
          LEFT JOIN sms3_departments d ON a.department_id = d.id
          ORDER BY a.created_at DESC";

$result = $conn->query($query);

if (!$result) {
  $queryError = "Failed to execute query: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admission</title>
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
    .modal .confirmationModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content .confirmationModal {
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      text-align: center;
      width: 300px;
    }

    .popup-message {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      padding: 15px;
      border-radius: 5px;
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
      </li><!-- End System Nav -->

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
      <h1>Admission</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Admission</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Basic Information</h5>
            <!-- Added filter dropdowns -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="filterDepartment" class="form-label">Department</label>
                <select class="form-select" id="filterDepartment">
                  <option value="">All Departments</option>
                  <?php
                  $departments = $conn->query("SELECT * FROM sms3_departments");
                  while ($department = $departments->fetch_assoc()): ?>
                    <option value="<?= $department['department_code']; ?>"><?= $department['department_code']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label for="filterAdmissionType" class="form-label">Admission Type</label>
                <select class="form-select" id="filterAdmissionType">
                  <option value="">All Admission Types</option>
                  <option value="Freshmen">Freshmen</option>
                  <option value="Transferee">Transferee</option>
                  <option value="Returnee">Returnee</option>
                </select>
              </div>
              <div class="col-md-4">
                <label for="filterStatus" class="form-label">Status</label>
                <select class="form-select" id="filterStatus">
                  <option value="">All Status</option>
                  <option value="Pending">Pending</option>
                  <option value="Temporarily Enrolled">Temporarily Enrolled</option>
                </select>
              </div>
            </div>
            <table class="table datatable">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Admission Type</th>
                  <th>Department</th>
                  <th>Year Level</th>
                  <th>Information</th>
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <?= htmlspecialchars($row['first_name']) . ' ' .
                          (!empty($row['middle_name']) ? htmlspecialchars($row['middle_name']) . ' ' : '') .
                          htmlspecialchars($row['last_name']); ?>
                      </td>
                      <td><?= htmlspecialchars($row['admission_type']) ?></td>
                      <td><?= htmlspecialchars($row['department']) ?></td>
                      <td><?= htmlspecialchars($row['year_level']) ?></td>
                      <td>
                        <button class="btn btn-info btn-sm" onclick="viewInformation(<?= $row['id'] ?>)">View Information</button>
                      </td>
                      <td><?= htmlspecialchars($row['created_at']) ?></td>
                      <td>
                        <span class="badge bg-<?= $row['status'] == 'Pending' ? 'warning' : ($row['status'] == 'Temporarily Enrolled' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['status']) ?>
                        </span>
                      </td>
                      <td>
                        <!-- Approve and Reject buttons -->
                        <?php if ($row['status'] !== 'Temporarily Enrolled' && $row['status'] !== 'Rejected'): ?>
                          <button class="btn btn-primary btn-sm" onclick="processAdmission(<?= $row['id'] ?>, '<?= $row['admission_type'] ?>', '<?= $row['old_student_number'] ?>')">Process Admission</button>
                        <?php else: ?>
                          <!-- No action if already approved or rejected -->
                          <span>No Action Available</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="9" class="text-center">No admissions found</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <!-- End Table with stripped rows -->
        </div>
      </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- Modal for processing admission -->
  <div class="modal fade" id="processAdmissionModal" tabindex="-1" aria-labelledby="processAdmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="processAdmissionModalLabel">Process Admission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="admissionForm">
            <input type="hidden" id="admissionId" name="admission_id">
            <div id="requirements"></div>
            <div class="mb-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Temporarily Enroll</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

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

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="modal fade" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmationModalLabel">Confirm Enrollment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to temporarily enroll this student?</p>
        </div>
        <div class="modal-footer">
          <button id="confirmEnroll" class="btn btn-primary">Yes</button>
          <button id="cancelEnroll" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Added event listeners for the filter dropdowns
    document.getElementById('filterDepartment').addEventListener('change', filterAdmissions);
    document.getElementById('filterAdmissionType').addEventListener('change', filterAdmissions);
    document.getElementById('filterStatus').addEventListener('change', filterAdmissions);

    function filterAdmissions() {
      const department = document.getElementById('filterDepartment').value;
      const admissionType = document.getElementById('filterAdmissionType').value;
      const status = document.getElementById('filterStatus').value;
      const rows = document.querySelectorAll('.datatable tbody tr');

      rows.forEach(row => {
        const rowStatus = row.cells[6].textContent.trim();
        const rowDepartment = row.cells[2].textContent.trim();
        const rowAdmissionType = row.cells[1].textContent.trim();

        const statusMatch = status === '' || rowStatus === status;
        const departmentMatch = department === '' || rowDepartment === department;
        const admissionTypeMatch = admissionType === '' || rowAdmissionType === admissionType;

        if (statusMatch && departmentMatch && admissionTypeMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    let departments = <?= $jsonDepartments ?: '[]' ?>; // This will ensure departments is always an array

    function viewInformation(id) {
      // Fetch additional information using AJAX
      fetch('get_admission_info.php?id=' + id)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Populate the modal content
            const info = data.info;
            let departmentOptions = '';

            if (Array.isArray(departments)) {
              departments.forEach(department => {
                // Convert both IDs to integers for accurate comparison
                const selected = parseInt(department.id, 10) === parseInt(info.department_id, 10) ? 'selected' : '';
                departmentOptions += `<option value="${department.id}" ${selected}>${department.department_name}</option>`;
              });
            } else {
              console.error('Departments data is not an array:', departments);
              // If departments is not an array, add a default option
              departmentOptions = '<option value="">No departments available</option>';
            }

            const isTemporarilyEnrolled = info.status === 'Temporarily Enrolled';
            const programContent = isTemporarilyEnrolled ?
              `<p><strong>Program:</strong> ${info.department_name}</p>` :
              `<p><strong>Program:</strong></p>
             <select class="form-select" id="editDepartment">
               ${departmentOptions}
             </select>`;

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
            </div>
            <div class="col-md-6">
              <p><strong>Admission Type:</strong> ${info.admission_type}</p>
              ${info.admission_type === 'Returnee' ? `<p><strong>Old Student Number:</strong> ${info.old_student_number}</p>` : ''}
              ${programContent}
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
              <p><strong>Guardian's Full Name:</strong> ${info.guardian_name}</p>
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
          <h4>Referral</h4>
          <p><strong>How did you hear about our school?</strong> ${info.referral_source}</p>
          <hr>
          ${isTemporarilyEnrolled ? '' : '<div class="d-flex justify-content-end"><button type="button" class="btn btn-primary" onclick="saveChanges(' + info.id + ')">Save Changes</button></div>'}
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

    function saveChanges(id) {
      const departmentId = document.getElementById('editDepartment').value;

      fetch('update_admission_info.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id: id,
            department_id: departmentId
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Program updated successfully.');
            location.reload();
          } else {
            alert('Failed to update program: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while updating the program.');
        });
    }

    function processAdmission(admissionId, admissionType, oldStudentNumber = null) {
      document.getElementById('admissionId').value = admissionId;

      let requirementsHtml = '';
      if (admissionType === 'Freshmen') {
        requirementsHtml = `
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="form138" name="form138">
          <label class="form-check-label" for="form138">Form 138</label>
          <input type="hidden" name="form138_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="good_moral" name="good_moral">
          <label class="form-check-label" for="good_moral">Good Moral Certificate</label>
          <input type="hidden" name="good_moral_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="form137" name="form137">
          <label class="form-check-label" for="form137">Form 137</label>
          <input type="hidden" name="form137_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="birth_certificate" name="birth_certificate">
          <label class="form-check-label" for="birth_certificate">Birth Certificate</label>
          <input type="hidden" name="birth_certificate_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="brgy_clearance" name="brgy_clearance">
          <label class="form-check-label" for="brgy_clearance">Barangay Clearance</label>
          <input type="hidden" name="brgy_clearance_shown" value="1">
        </div>
      </div>
    `;
      } else if (admissionType === 'Transferee') {
        requirementsHtml = `
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="honorable_dismissal" name="honorable_dismissal">
          <label class="form-check-label" for="honorable_dismissal">Honorable Dismissal</label>
          <input type="hidden" name="honorable_dismissal_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="transcript_of_records" name="transcript_of_records">
          <label class="form-check-label" for="transcript_of_records">Transcript of Records</label>
          <input type="hidden" name="transcript_of_records_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="certificate_of_grades" name="certificate_of_grades">
          <label class="form-check-label" for="certificate_of_grades">Certificate of Grades</label>
          <input type="hidden" name="certificate_of_grades_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="good_moral" name="good_moral">
          <label class="form-check-label" for="good_moral">Good Moral Certificate</label>
          <input type="hidden" name="good_moral_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="brgy_clearance" name="brgy_clearance">
          <label class="form-check-label" for="brgy_clearance">Barangay Clearance</label>
          <input type="hidden" name="brgy_clearance_shown" value="1">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="birth_certificate" name="birth_certificate">
          <label class="form-check-label" for="birth_certificate">Birth Certificate</label>
          <input type="hidden" name="birth_certificate_shown" value="1">
        </div>
      </div>
    `;
      } else if (admissionType === 'Returnee') {
        // Fetch requirements for the returnee
        fetch('get_returnee_requirements.php?student_number=' + oldStudentNumber)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const requirements = data.requirements;
              requirementsHtml = '<div class="mb-3">';
              if (requirements.form138) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="form138" name="form138" ${requirements.form138 === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="form138">Form 138</label>
                <input type="hidden" name="form138_shown" value="1">
              </div>
            `;
              }
              if (requirements.good_moral) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="good_moral" name="good_moral" ${requirements.good_moral === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="good_moral">Good Moral Certificate</label>
                <input type="hidden" name="good_moral_shown" value="1">
              </div>
            `;
              }
              if (requirements.form137) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="form137" name="form137" ${requirements.form137 === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="form137">Form 137</label>
                <input type="hidden" name="form137_shown" value="1">
              </div>
            `;
              }
              if (requirements.birth_certificate) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="birth_certificate" name="birth_certificate" ${requirements.birth_certificate === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="birth_certificate">Birth Certificate</label>
                <input type="hidden" name="birth_certificate_shown" value="1">
              </div>
            `;
              }
              if (requirements.brgy_clearance) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="brgy_clearance" name="brgy_clearance" ${requirements.brgy_clearance === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="brgy_clearance">Barangay Clearance</label>
                <input type="hidden" name="brgy_clearance_shown" value="1">
              </div>
            `;
              }
              if (requirements.honorable_dismissal) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="honorable_dismissal" name="honorable_dismissal" ${requirements.honorable_dismissal === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="honorable_dismissal">Honorable Dismissal</label>
                <input type="hidden" name="honorable_dismissal_shown" value="1">
              </div>
            `;
              }
              if (requirements.transcript_of_records) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="transcript_of_records" name="transcript_of_records" ${requirements.transcript_of_records === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="transcript_of_records">Transcript of Records</label>
                <input type="hidden" name="transcript_of_records_shown" value="1">
              </div>
            `;
              }
              if (requirements.certificate_of_grades) {
                requirementsHtml += `
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="certificate_of_grades" name="certificate_of_grades" ${requirements.certificate_of_grades === 'Submitted' ? 'checked' : ''}>
                <label class="form-check-label" for="certificate_of_grades">Certificate of Grades</label>
                <input type="hidden" name="certificate_of_grades_shown" value="1">
              </div>
            `;
              }
              requirementsHtml += '</div>';
              document.getElementById('requirements').innerHTML = requirementsHtml;
            } else {
              alert('Failed to fetch requirements for the returnee.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching the requirements.');
          });
      }

      document.getElementById('requirements').innerHTML = requirementsHtml;
      new bootstrap.Modal(document.getElementById('processAdmissionModal')).show();
    }

    document.getElementById('admissionForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
      confirmationModal.show();

      document.getElementById('confirmEnroll').onclick = () => {
        const processAdmissionModal = bootstrap.Modal.getInstance(document.getElementById('processAdmissionModal'));
        processAdmissionModal.hide(); // Hide the "Process Admission" modal

        const admissionId = document.getElementById('admissionId').value;
        const formData = new FormData(document.getElementById('admissionForm'));
        formData.append('status', 'Temporarily Enrolled');

        fetch('admission.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
          })
          .then(response => response.json())
          .then(data => {
            confirmationModal.hide();
            if (data.success) {
              showPopupMessage('Student temporarily enrolled successfully.', 'success');
              setTimeout(() => location.reload(), 3000);
            } else {
              showPopupMessage('Failed to enroll student: ' + data.message, 'error');
            }
          })
          .catch(error => {
            confirmationModal.hide();
            console.error('Error:', error);
            showPopupMessage('An error occurred while processing the admission.', 'error');
          });
      };

      document.getElementById('cancelEnroll').onclick = () => {
        confirmationModal.hide();
      };
    });

    // Function to show popup messages
    function showPopupMessage(message, type = 'success') {
      const popup = document.createElement('div');
      popup.className = `popup-message ${type}`;
      popup.innerText = message;
      document.body.appendChild(popup);

      popup.style.opacity = '1';
      setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => popup.remove(), 500);
      }, 3000);
    }

    // Function to show popup messages
    function showPopupMessage(message, type = 'success') {
      const popup = document.createElement('div');
      popup.className = `popup-message ${type}`;
      popup.innerText = message;
      document.body.appendChild(popup);

      popup.style.opacity = '1';
      setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => popup.remove(), 500);
      }, 3000);
    }

    // Show popup on load if a session message exists
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