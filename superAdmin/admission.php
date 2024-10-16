<?php require('../database.php');
require('../access_control.php'); // Include the file with the checkAccess function
checkAccess('superadmin'); // Ensure only users with the 'admin' role can access this page

// Handle status update requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admission_id'], $_POST['status'])) {
  $admissionId = intval($_POST['admission_id']);
  $status = $_POST['status'];

// If the status is 'Rejected', delete the record
if ($status === 'Rejected') {
  $stmt = $conn->prepare("DELETE FROM sms3_pending_admission WHERE id = ?");
  $stmt->bind_param("i", $admissionId);

  if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Admission record deleted successfully.']);
  } else {
      echo json_encode(['success' => false, 'message' => 'Failed to delete admission record.']);
  }
  } else {
    // Update the status in the database if it's not 'Rejected'
    $stmt = $conn->prepare("UPDATE sms3_pending_admission SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $admissionId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Admission status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update admission status.']);
    }
  }
exit; // Terminate the script after handling the AJAX request
}

// Fetch all pending admissions
$query = "SELECT * FROM sms3_pending_admission WHERE status = 'Pending' ORDER BY created_at DESC";
$result = $conn->query($query);

// Check if the query was successful
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
              <a class="dropdown-item d-flex align-items-center" href="#">
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
        <a class="nav-link " href="Adashboard.php">
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
      </li><!-- End System Nav -->

      <li class="nav-item">
        <a class="nav-link " href="enrolled.php">
          <i class="bi bi-grid"></i>
          <span>Enrolled Students</span>
        </a>
      </li><!-- End System Nav -->

      <hr class="sidebar-divider">

      <li class="nav-heading">TEST REGISTRAR</li>
      
      <li class="nav-item">
        <a class="nav-link " href="manage_academic_year.php">
          <i class="bi bi-grid"></i>
          <span>Academic Year</span>
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

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Admission</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Adashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Admission</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Basic Information</h5>
              <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <!-- Table with stripped rows -->
                <table style="width: 100%; min-width: 800px;" class="table datatable">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th data-type="date" data-format="YYYY/DD/MM">Birthdate</th>
                      <th>Sex</th>
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
                          <td><?= htmlspecialchars($row['full_name']) ?></td>
                          <td><?= htmlspecialchars($row['birthday']) ?></td>
                          <td><?= htmlspecialchars($row['sex']) ?></td>
                          <td><?= htmlspecialchars($row['program']) ?></td>
                          <td><?= htmlspecialchars($row['year_level']) ?></td>
                          <td>
                              <button class="btn btn-info btn-sm" onclick="viewInformation(<?= $row['id'] ?>)">View Information</button>
                          </td>
                          <td><?= htmlspecialchars($row['created_at']) ?></td>
                          <td>
                              <span class="badge bg-<?= $row['status'] == 'Pending' ? 'warning' : ($row['status'] == 'Approved' ? 'success' : 'danger') ?>">
                                  <?= htmlspecialchars($row['status']) ?>
                              </span>
                          </td>
                          <td>
                              <button class="btn btn-sm btn-success" onclick="updateAdmissionStatus(<?= $row['id'] ?>, 'Approved')">Approve</button>
                              <button class="btn btn-sm btn-danger" onclick="updateAdmissionStatus(<?= $row['id'] ?>, 'Rejected')">Reject</button>
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

  // Initialize the datatable after checking if the table exists
  document.addEventListener("DOMContentLoaded", function() {
    var table = document.querySelector("#admissionTable");
    if (table) {
      new simpleDatatables.DataTable(table);
    }
  });

  function viewInformation(id) {
    // Fetch additional information using AJAX
    fetch('get_admission_info.php?id=' + id)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Populate the modal content
        const info = data.info;
        const content = `
          <p><strong>Email:</strong> ${info.email || 'N/A'}</p>
          <p><strong>Contact Number:</strong> ${info.contact_number || 'N/A'}</p>
          <p><strong>Facebook Name:</strong> ${info.facebook_name || 'N/A'}</p>
          <p><strong>Working Student:</strong> ${info.working_student === 'Yes' ? 'Yes' : 'No'}</p>
          <p><strong>Address:</strong> ${info.address || 'N/A'}</p>
          <p><strong>Civil Status:</strong> ${info.civil_status || 'N/A'}</p>
          <p><strong>Religion:</strong> ${info.religion || 'N/A'}</p>
          <p><strong>Father's Name:</strong> ${info.father_name || 'N/A'}</p>
          <p><strong>Mother's Name:</strong> ${info.mother_name || 'N/A'}</p>
          <p><strong>Guardian's Name:</strong> ${info.guardian_name || 'N/A'}</p>
          <p><strong>Guardian's Contact:</strong> ${info.guardian_contact || 'N/A'}</p>
          <p><strong>Member 4Ps:</strong> ${info.member4ps === 'Yes' ? 'Yes' : 'No'}</p>
          <p><strong>Primary School:</strong> ${info.primary_school || 'N/A'} (${info.primary_year || 'N/A'})</p>
          <p><strong>Secondary School:</strong> ${info.secondary_school || 'N/A'} (${info.secondary_year || 'N/A'})</p>
          <p><strong>Last School Attended:</strong> ${info.last_school || 'N/A'} (${info.last_school_year || 'N/A'})</p>
          <p><strong>Referral Source:</strong> ${info.referral_source || 'N/A'}</p>
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

  // JavaScript function to update admission status
  function updateAdmissionStatus(admissionId, status) {
    if (!confirm('Are you sure you want to update the status to ' + status + '?')) {
        return;
    }

    // Send an AJAX request to update the status
    fetch('admission.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'admission_id': admissionId,
            'status': status
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