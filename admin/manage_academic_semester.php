<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

if (!isset($_SESSION['active_tab'])) {
  $_SESSION['active_tab'] = 'academicYear';
}

if (isset($_GET['tab'])) {
  $_SESSION['active_tab'] = $_GET['tab'];
}

// Add academic year
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_academic_year'])) {
  $academic_year = $_POST['academic_year'];

  try {
    // Insert academic year
    $stmt = $conn->prepare("INSERT INTO sms3_academic_years (academic_year, is_current) VALUES (?, 0)");
    $stmt->bind_param("s", $academic_year);
    $stmt->execute();
    $_SESSION['success_message'] = "Academic year added successfully!";
    header("Location: manage_academic_semester.php?tab=academicYear");
    exit;
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = $e->getCode() == 1062
      ? "Error: Duplicate academic year."
      : "Error: " . $e->getMessage();
    header("Location: manage_academic_semester.php?tab=academicYear");
    exit;
  }
}

// Set academic year as current
if (isset($_GET['set_current_academic_year_id'])) {
  $set_current_id = intval($_GET['set_current_academic_year_id']);

  try {
    // Reset all academic years
    $conn->query("UPDATE sms3_academic_years SET is_current = 0");

    // Set the selected academic year as current
    $stmt = $conn->prepare("UPDATE sms3_academic_years SET is_current = 1 WHERE id = ?");
    $stmt->bind_param("i", $set_current_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Academic year set as current!";
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
  }

  header("Location: manage_academic_semester.php?tab=academicYear");
  exit;
}

// Delete academic year
if (isset($_GET['delete_academic_year_id'])) {
  $delete_id = intval($_GET['delete_academic_year_id']);

  try {
    $stmt = $conn->prepare("DELETE FROM sms3_academic_years WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Academic year deleted successfully!";
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = $e->getCode() == 1451
      ? "Error: This academic year is still linked to other data."
      : "Error: " . $e->getMessage();
  }

  header("Location: manage_academic_semester.php?tab=academicYear");
  exit;
}

// Add a new semester
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_semester'])) {
  $semester_name = $_POST['semester_name'];

  // Insert the new semester with 'Inactive' status
  $stmt = $conn->prepare("INSERT INTO sms3_semesters (name, status) VALUES (?, 'Inactive')");
  $stmt->bind_param("s", $semester_name);

  if ($stmt->execute()) {
    $_SESSION['success_message'] = "New semester added successfully!";
  } else {
    $_SESSION['error_message'] = "Failed to add semester.";
  }
  $stmt->close();
  header("Location: manage_academic_semester.php?tab=semester");
  exit;
}

// Set a semester as active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_active'])) {
  $semester_id = $_POST['semester_id'];

  // Set all semesters to 'Inactive' before setting the selected one to 'Active'
  $conn->query("UPDATE sms3_semesters SET status = 'Inactive'");
  $stmt = $conn->prepare("UPDATE sms3_semesters SET status = 'Active' WHERE id = ?");
  $stmt->bind_param("i", $semester_id);

  if ($stmt->execute()) {
    $_SESSION['success_message'] = "Semester set to Active!";
  } else {
    $_SESSION['error_message'] = "Failed to activate semester.";
  }
  $stmt->close();
  header("Location: manage_academic_semester.php?tab=semester");
  exit;
}

// Delete semester
if (isset($_GET['delete_semester_id'])) {
  $delete_id = intval($_GET['delete_semester_id']);

  try {
    $stmt = $conn->prepare("DELETE FROM sms3_semesters WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Semester deleted successfully!";
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = $e->getCode() == 1451
      ? "Error: This semester is still linked to other data."
      : "Error: " . $e->getMessage();
  }

  header("Location: manage_academic_semester.php?tab=semester");
  exit;
}
// Fetch all academic years and semesters
$academic_years = $conn->query("SELECT * FROM sms3_academic_years ORDER BY id DESC");
$semesters = $conn->query("SELECT * FROM sms3_semesters ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Academic Year</title>
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
    .alert {
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
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
      <h1>Academic Structure</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Academic Structure</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="card">
        <div class="card-header">
          <ul class="nav nav-tabs nav-tabs-bordered">
            <li class="nav-item">
              <a class="nav-link <?= $_SESSION['active_tab'] == 'academicYear' ? 'active' : '' ?>"
                href="#academicYear"
                data-bs-toggle="tab">Academic Year</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $_SESSION['active_tab'] == 'semester' ? 'active' : '' ?>"
                href="#semester"
                data-bs-toggle="tab">Semester</a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <!-- Academic Year Tab -->
            <div class="tab-pane fade <?= $_SESSION['active_tab'] == 'academicYear' ? 'active' : '' ?>" id="academicYear">
              <div class="row">
                <div class="col-md-4">
                  <h5 class="card-title">Add New Academic Year</h5>
                  <form action="manage_academic_semester.php" method="POST">
                    <div class="mb-3">
                      <label for="academicYear" class="form-label">Academic Year</label>
                      <input type="text" class="form-control" id="academicYear" name="academic_year" placeholder="e.g., 2023-2024" required>
                    </div>
                    <button type="submit" name="add_academic_year" class="btn btn-primary">Add Academic Year</button>
                  </form>
                </div>
                <div class="col-md-8">
                  <h5 class="card-title">Academic Year List</h5>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($academic_year = $academic_years->fetch_assoc()): ?>
                        <tr>
                          <td><?= htmlspecialchars($academic_year['academic_year']); ?></td>
                          <td>
                            <?= $academic_year['is_current']
                              ? '<span class="badge bg-success">Active</span>'
                              : '<a href="manage_academic_semester.php?set_current_academic_year_id=' . $academic_year['id'] . '" class="btn btn-sm btn-primary">Set as Active</a>' ?>
                          </td>
                          <td>
                            <a href="manage_academic_semester.php?delete_academic_year_id=<?= $academic_year['id']; ?>"
                              class="btn btn-sm btn-danger"
                              onclick="return confirm('Are you sure you want to delete this academic year?');">Delete</a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Semester Tab -->
            <div class="tab-pane fade <?= $_SESSION['active_tab'] == 'semester' ? 'active' : '' ?>" id="semester">
              <div class="row">
                <div class="col-md-4">
                  <h5 class="card-title">Add New Semester</h5>
                  <form action="manage_academic_semester.php" method="POST">
                    <div class="mb-3">
                      <label for="semesterName" class="form-label">Semester Name</label>
                      <input type="text" class="form-control" id="semesterName" name="semester_name" required placeholder="e.g., 1st Semester">
                    </div>
                    <button type="submit" name="add_semester" class="btn btn-primary">Add Semester</button>
                  </form>
                </div>
                <div class="col-md-8">
                  <h5 class="card-title">Semester List</h5>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Semester Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($semester = $semesters->fetch_assoc()): ?>
                        <tr>
                          <td><?= htmlspecialchars($semester['name']); ?></td>
                          <td>
                            <?php if ($semester['status'] !== 'Active'): ?>
                              <form action="manage_academic_semester.php" method="POST" style="display:inline;">
                                <input type="hidden" name="semester_id" value="<?= $semester['id']; ?>">
                                <button type="submit" name="set_active" class="btn btn-sm btn-primary">Set as Active</button>
                              </form>
                            <?php else: ?>
                              <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <a href="manage_academic_semester.php?delete_semester_id=<?= $semester['id']; ?>"
                              class="btn btn-sm btn-danger"
                              onclick="return confirm('Are you sure you want to delete this semester?');">
                              Delete
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


  </main><!-- End #main -->

  <script>
    // Initialize Bootstrap Tabs with session tracking
    document.addEventListener('DOMContentLoaded', function() {
      // Ensure the active tab is set from session
      const activeTab = `#${$_SESSION['active_tab']}`;
      const tabEl = document.querySelector(`[href="${activeTab}"]`);
      if (tabEl) {
        const tab = new bootstrap.Tab(tabEl);
        tab.show();
      }

      // Update the active tab when navigating
      document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const tabId = this.getAttribute('href').substring(1);
          window.location.href = `?tab=${tabId}`;
        });
      });
    });

    function setCurrentAcademicYear(id) {
      if (confirm('Are you sure you want to set this academic year as current?')) {
        fetch('set_academic_year.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Academic year set as current successfully.');
              location.reload();
            } else {
              alert('Failed to set the academic year.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while setting the academic year.');
          });
      }
    }

    function deleteAcademicYear(id) {
      if (confirm('Are you sure you want to delete this academic year?')) {
        fetch('manage_academic_year.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              delete_id: id
            })
          })
          .then(response => {
            if (!response.ok) {
              // Handle non-200 responses
              return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
              });
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              alert(data.message);
              location.reload(); // Reload the page to update the list
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the academic year.');
          });
      }
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