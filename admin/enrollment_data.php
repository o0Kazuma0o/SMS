<?php
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
checkAccess('Admin');

// Fetch enrollment data
$query = "
    SELECT ed.id AS enrollment_id, s.student_number, s.first_name, s.last_name, s.admission_type, 
           d.department_code, ed.created_at, ed.receipt_status, ed.status,
           GROUP_CONCAT(DISTINCT sec.section_number ORDER BY sec.id SEPARATOR ', ') AS sections,
           GROUP_CONCAT(DISTINCT sub.subject_code ORDER BY sub.id SEPARATOR ', ') AS subjects,
           GROUP_CONCAT(DISTINCT CONCAT(t.day_of_week, ' ', TIME_FORMAT(t.start_time, '%H:%i'), '-', TIME_FORMAT(t.end_time, '%H:%i')) ORDER BY t.start_time SEPARATOR ', ') AS schedules
    FROM sms3_enrollment_data ed
    JOIN sms3_students s ON ed.student_id = s.id
    LEFT JOIN sms3_timetable t ON t.id IN (ed.timetable_1, ed.timetable_2, ed.timetable_3, ed.timetable_4, ed.timetable_5, ed.timetable_6, ed.timetable_7, ed.timetable_8)
    LEFT JOIN sms3_sections sec ON t.section_id = sec.id
    LEFT JOIN sms3_subjects sub ON t.subject_id = sub.id
    LEFT JOIN sms3_departments d ON sec.department_id = d.id
    GROUP BY ed.id
    ORDER BY ed.created_at DESC
";

$result = $conn->query($query);

if (isset($_GET['timetable_details'])) {
  $enrollmentId = intval($_GET['timetable_details']);
  $query = "
      SELECT sec.section_number, sub.subject_code, 
             CONCAT(t.day_of_week, ' ', TIME_FORMAT(t.start_time, '%H:%i'), '-', TIME_FORMAT(t.end_time, '%H:%i')) AS schedule
      FROM sms3_enrollment_data ed
      JOIN sms3_timetable t ON t.id IN (ed.timetable_1, ed.timetable_2, ed.timetable_3, ed.timetable_4, ed.timetable_5, ed.timetable_6, ed.timetable_7, ed.timetable_8)
      JOIN sms3_sections sec ON t.section_id = sec.id
      JOIN sms3_subjects sub ON t.subject_id = sub.id
      WHERE ed.id = ?
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Enrollment Data</title>
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
      <h1>Enrollment Data</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Enrollment Data</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Enrollment Data</h5>

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
            <!-- Add a search bar and pagination controls above the table -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="searchBar" class="form-label">Search</label>
                <input type="text" id="searchBar" class="form-control" placeholder="Search by Room Name, Location, or Department">
              </div>
              <div class="col-md-6">
                <label for="itemsPerPage" class="form-label">Items per Page</label>
                <select id="itemsPerPage" class="form-select">
                  <option value="10" selected>10</option>
                  <option value="20">20</option>
                </select>
              </div>
            </div>

            <table id="enrollmentTable" class="table">
              <thead>
                <tr>
                  <th>Student Number</th>
                  <th>Student</th>
                  <th>Admission Type</th>
                  <th>Department</th>
                  <th>Subjects</th>
                  <th>Date Submitted</th>
                  <th>Receipt Status</th>
                  <th>Status</th>
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
                        <span class="badge bg-<?= $row['receipt_status'] == 'Not Paid' ? 'warning' : ($row['receipt_status'] == 'Paid' ? 'success' : 'danger') ?>">
                          <?= htmlspecialchars($row['receipt_status']); ?>
                        </span>
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
                    <td colspan="8" class="text-center">No enrollment data found</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>

  </main><!-- End #main -->

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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const table = document.getElementById('enrollmentTable');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const searchBar = document.getElementById('searchBar');
      const itemsPerPageSelect = document.getElementById('itemsPerPage');
      const paginationControls = document.getElementById('paginationControls');

      let currentPage = 1;
      let itemsPerPage = parseInt(itemsPerPageSelect.value);

      function renderTable() {
        const searchQuery = searchBar.value.toLowerCase();
        const filteredRows = rows.filter(row => {
          const cells = Array.from(row.cells);
          return cells.some(cell => cell.textContent.toLowerCase().includes(searchQuery));
        });

        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        currentPage = Math.min(currentPage, totalPages);

        tbody.innerHTML = '';
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        filteredRows.slice(start, end).forEach(row => tbody.appendChild(row));

        renderPaginationControls(totalPages);
      }

      function renderPaginationControls(totalPages) {
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return; // No need for pagination if there's only one page

        // Create "First" and "Previous" buttons
        const firstButton = document.createElement('button');
        firstButton.textContent = '<<';
        firstButton.className = 'btn btn-sm btn-secondary mx-1';
        firstButton.disabled = currentPage === 1;
        firstButton.addEventListener('click', () => {
          currentPage = 1;
          renderTable();
        });
        paginationControls.appendChild(firstButton);

        const prevButton = document.createElement('button');
        prevButton.textContent = '<';
        prevButton.className = 'btn btn-sm btn-secondary mx-1';
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => {
          currentPage = Math.max(1, currentPage - 1);
          renderTable();
        });
        paginationControls.appendChild(prevButton);

        // Show up to 5 page buttons around the current page
        const maxVisiblePages = 5;
        const startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        for (let i = startPage; i <= endPage; i++) {
          const button = document.createElement('button');
          button.textContent = i;
          button.className = 'btn btn-sm btn-primary mx-1';
          if (i === currentPage) {
            button.classList.add('active');
          }
          button.addEventListener('click', () => {
            currentPage = i;
            renderTable();
          });
          paginationControls.appendChild(button);
        }

        // Create "Next" and "Last" buttons
        const nextButton = document.createElement('button');
        nextButton.textContent = '>';
        nextButton.className = 'btn btn-sm btn-secondary mx-1';
        nextButton.disabled = currentPage === totalPages;
        nextButton.addEventListener('click', () => {
          currentPage = Math.min(totalPages, currentPage + 1);
          renderTable();
        });
        paginationControls.appendChild(nextButton);

        const lastButton = document.createElement('button');
        lastButton.textContent = '>>';
        lastButton.className = 'btn btn-sm btn-secondary mx-1';
        lastButton.disabled = currentPage === totalPages;
        lastButton.addEventListener('click', () => {
          currentPage = totalPages;
          renderTable();
        });
        paginationControls.appendChild(lastButton);
      }

      searchBar.addEventListener('input', renderTable);
      itemsPerPageSelect.addEventListener('change', () => {
        itemsPerPage = parseInt(itemsPerPageSelect.value);
        currentPage = 1;
        renderTable();
      });

      renderTable();
    });

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
      // Fetch timetable details via AJAX
      fetch(`enrollment_data.php?timetable_details=${enrollmentId}`)
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
          timetableDetails.innerHTML = ''; // Clear previous data
          data.forEach(item => {
            timetableDetails.innerHTML += `
            <tr>
              <td>${item.section_number}</td>
              <td>${item.subject_code}</td>
              <td>${item.schedule}</td>
            </tr>
          `;
          });

          // Show the modal
          const modal = new bootstrap.Modal(document.getElementById('timetableModal'));
          modal.show();
        })
        .catch(error => console.error("Error fetching timetable details:", error));
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