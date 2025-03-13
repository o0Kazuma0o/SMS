<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Function to generate and bcrypt hash password
function generatePassword($lastName)
{
  $passwordPlain = '#' . substr($lastName, 0, 2) . '8080';
  return password_hash($passwordPlain, PASSWORD_BCRYPT);
}

// Fetch current academic year
function getCurrentAcademicYear($conn)
{
  $result = $conn->query("SELECT * FROM sms3_academic_years WHERE is_current = 1 LIMIT 1");
  return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['id'] : null;
}


// Handle status update requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admission_id'], $_POST['status'], $_POST['receipt_status'])) {
  $admissionId = intval($_POST['admission_id']);
  $status = $_POST['status'];
  $receiptStatus = $_POST['receipt_status'];

  if ($status === 'Enrolled' && $receiptStatus === 'Paid') {
    // Move record to sms3_student
    $stmt = $conn->prepare("SELECT * FROM sms3_temp_enroll WHERE id = ?");
    $stmt->bind_param('i', $admissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $admissionData = $result->fetch_assoc();
    $stmt->close();

    if ($admissionData) {
      $academicYearId = getCurrentAcademicYear($conn);
      if (!$academicYearId) {
        echo json_encode(['success' => false, 'message' => 'Error: No current academic year set.']);
        exit;
      }
      // Generate hashed password
      $password = generatePassword($admissionData['last_name']);

      // Insert data into sms3_students
      $stmt = $conn->prepare("INSERT INTO sms3_students (
              student_number, first_name, middle_name, last_name, academic_year, username, password, role, department_id, branch, admission_type, 
              year_level, sex, civil_status, religion, birthday, email, contact_number, facebook_name, 
              address, father_name, mother_name, guardian_name, guardian_contact, primary_school, primary_year, 
              secondary_school, secondary_year, last_school, last_school_year, referral_source, working_student, member4ps,
              form138, good_moral, form137, birth_certificate, brgy_clearance,
              honorable_dismissal, transcript_of_records, certificate_of_grades, status
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      $username = 's' . $admissionData['student_number'];
      $role = 'Student';
      $status = 'Enrolled';

      $stmt->bind_param(
        "ssssssssssssssssssssssssssssssssssssssssss",
        $admissionData['student_number'],
        $admissionData['first_name'],
        $admissionData['middle_name'],
        $admissionData['last_name'],
        $academicYearId,
        $username,
        $password,
        $role,
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
        $admissionData['facebook_name'],
        $admissionData['address'],
        $admissionData['father_name'],
        $admissionData['mother_name'],
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
        $admissionData['form138'],
        $admissionData['good_moral'],
        $admissionData['form137'],
        $admissionData['birth_certificate'],
        $admissionData['brgy_clearance'],
        $admissionData['honorable_dismissal'],
        $admissionData['transcript_of_records'],
        $admissionData['certificate_of_grades'],
        $status
      );

      if ($stmt->execute()) {
        $updateStmt = $conn->prepare("UPDATE sms3_temp_enroll SET status = 'Enrolled', receipt_status = 'Paid' WHERE id = ?");
        $updateStmt->bind_param('i', $admissionId);
        $updateStmt->execute();
        $updateStmt->close();
        echo json_encode(['success' => true, 'message' => 'Student moved to temporary enrollment successfully!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert student record.']);
      }
      $stmt->close();
    } else {
      echo json_encode(['success' => false, 'message' => 'Admission record not found.']);
    }
  } else {
    $stmt = $conn->prepare("UPDATE sms3_temp_enroll SET status = ?, receipt_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $receiptStatus, $admissionId);
    echo json_encode($stmt->execute() ? ['success' => true, 'message' => 'Admission status updated successfully.'] : ['success' => false, 'message' => 'Failed to update admission status.']);
  }
  exit;
}

// Fetch all temporarily enrolled
$query = "SELECT a.*, d.department_code AS department 
          FROM sms3_temp_enroll a
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
                  <option value="New Regular">New Regular</option>
                  <option value="Transferee">Transferee</option>
                  <option value="Returnee">Returnee</option>
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
                  <th>Receipt</th>
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
                        <span class="badge bg-<?= $row['status'] == 'Temporarily Enrolled' ? 'warning' : ($row['status'] == 'Enrolled' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['status']) ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-<?= $row['receipt_status'] == 'Not Paid' ? 'warning' : ($row['receipt_status'] == 'Paid' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['receipt_status']) ?>
                        </span>
                      </td>
                      <td>
                        <!-- Approve and Reject buttons -->
                        <?php if ($row['status'] == 'Temporarily Enrolled' && $row['receipt_status'] == 'Not Paid'): ?>
                          <button class="btn btn-success btn-sm" onclick="updateAdmissionStatus(<?= $row['id'] ?>, 'Enrolled')">Enroll</button>
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

  <script>
    // Added event listeners for the filter dropdowns
    document.getElementById('filterDepartment').addEventListener('change', filterAdmissions);
    document.getElementById('filterAdmissionType').addEventListener('change', filterAdmissions);

    function filterAdmissions() {
      const department = document.getElementById('filterDepartment').value;
      const admissionType = document.getElementById('filterAdmissionType').value;
      const rows = document.querySelectorAll('.datatable tbody tr');

      rows.forEach(row => {
        const rowDepartment = row.cells[2].textContent.trim();
        const rowAdmissionType = row.cells[1].textContent.trim();

        const departmentMatch = department === '' || rowDepartment === department;
        const admissionTypeMatch = admissionType === '' || rowAdmissionType === admissionType;

        if (departmentMatch && admissionTypeMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    function viewInformation(id) {
      // Fetch additional information using AJAX
      fetch('get_temp_admission_info.php?id=' + id)
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

    document.getElementById('admissionForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const admissionId = document.getElementById('admissionId').value;
      const formData = new FormData(this);
      formData.append('status', 'Temporarily Enrolled');

      fetch('admission.php', {
          method: 'POST',
          body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Student temporarily enrolled successfully.');
            location.reload();
          } else {
            alert('Failed to enroll student: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while processing the admission.');
        });
    });

    // JavaScript function to update admission status
    function updateAdmissionStatus(admissionId, status) {
      if (!confirm('Are you sure you want to update the status to ' + status + '?')) {
        return;
      }

      // Send an AJAX request to update the status and receipt_status
      fetch('admission_temp.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'admission_id': admissionId,
            'status': status,
            'receipt_status': 'Paid'
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Status updated successfully.');
            location.reload();
          } else {
            alert('Failed to update status: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while updating the status.');
        });
    }
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