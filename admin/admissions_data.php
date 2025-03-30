<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Fetch all admissions data
$query = "SELECT a.*, d.department_code AS department 
          FROM sms3_admissions_data a
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

  <title>Admission Data</title>
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
                  <th>Receipt Status</th>
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
                        <span class="badge bg-<?= $row['status'] == 'Accepted' ? 'success' : ($row['status'] == 'Processing' ? 'primary' : 'danger') ?>">
                          <?= htmlspecialchars($row['status']) ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-<?= $row['receipt_status'] == 'Not Paid' ? 'warning' : ($row['receipt_status'] == 'Paid' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['receipt_status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="9" class="text-center">No admissions data found</td>
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
    document.getElementById('filterStatus').addEventListener('change', filterAdmissions);

    function filterAdmissions() {
      const department = document.getElementById('filterDepartment').value;
      const admissionType = document.getElementById('filterAdmissionType').value;
      const status = document.getElementById('filterStatus').value;
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

    let departments = <?= $jsonDepartments ?: '[]' ?>; // This will ensure departments is always an array

    function viewInformation(id) {
      // Fetch additional information using AJAX
      fetch('get_admission_data_info.php?id=' + id)
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
                  <p><strong>Guardian's Full Name:</strong> ${info.guardian_name}</p>
                  <p><strong>Guardian's Contact Number:</strong> ${info.guardian_contact}</p>
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