<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Add academic year
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_academic_year'])) {
  $academic_year = $_POST['academic_year'];

  try {
    // Insert academic year
    $stmt = $conn->prepare("INSERT INTO sms3_academic_years (academic_year, is_current) VALUES (?, 0)");
    $stmt->bind_param("s", $academic_year);
    $stmt->execute();
    $_SESSION['success_message'] = "Academic year added successfully!";
    header('Location: manage_academic_year.php');
    exit;
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = $e->getCode() == 1062
      ? "Error: Duplicate academic year."
      : "Error: " . $e->getMessage();
    header('Location: manage_academic_year.php');
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

  header('Location: manage_academic_year.php');
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

  header('Location: manage_academic_year.php');
  exit;
}

// Fetch all academic years
$academic_years = $conn->query("SELECT * FROM sms3_academic_years ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Academic</title>
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
        <a class="nav-link " href="manage_academic_year.php">
          <i class="bi bi-grid"></i>
          <span>Academic Year</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="manage_semester.php">
          <i class="bi bi-grid"></i>
          <span>Semester</span>
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

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Add New Academic Year</h5>

              <form action="manage_academic_year.php" method="POST">
                <div class="mb-3">
                  <label for="academicYear" class="form-label">Academic Year</label>
                  <input type="text" class="form-control" id="academicYear" name="academic_year" placeholder="2023-2024" required>
                </div>
                <button type="submit" name="add_academic_year" class="btn btn-primary">Add Academic Year</button>
              </form>

            </div>
          </div>
        </div>

        <div class="col-lg-12">
          <div class="card">
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
              <h5 class="card-title">Academic Year List</h5>

              <table style="width: 100%; min-width: 800px;" class="table">
                <thead>
                  <tr>
                    <th>Academic Year</th>
                    <th>Set as Current</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($academic_years->num_rows > 0): ?>
                    <?php while ($row = $academic_years->fetch_assoc()): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['academic_year']); ?></td>
                        <td>
                          <?= $row['is_current']
                            ? '<span class="badge bg-success">Current</span>'
                            : '<a href="manage_academic_year.php?set_current_academic_year_id=' . $row['id'] . '" class="btn btn-sm btn-primary">Set as Current</a>' ?>
                        </td>
                        <td>
                          <a href="manage_academic_year.php?delete_academic_year_id=<?= $row['id']; ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure you want to delete this academic year?');">Delete</a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-center">No academic years found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <script>
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