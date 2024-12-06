<?php
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Edit section
$edit_section = null;
if (isset($_GET['edit_section_id'])) {
  $edit_section_id = $_GET['edit_section_id'];
  $stmt = $conn->prepare("SELECT * FROM sms3_sections WHERE id = ?");
  $stmt->bind_param("i", $edit_section_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $edit_section = $result->fetch_assoc();
  $stmt->close();
}

// Add section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
  $section_number = $_POST['section_number'];
  $year_level = $_POST['year_level'];
  $capacity = $_POST['capacity'];
  $semester = $_POST['semester_id'];
  $available = $_POST['available'];
  $department_id = $_POST['department_id'];

  try {
    // Check for duplicates
    $stmt = $conn->prepare("
          SELECT COUNT(*) 
          FROM sms3_sections 
          WHERE section_number = ? AND department_id = ? AND semester_id = ?");
    $stmt->bind_param("iii", $section_number, $department_id, $semester);
    $stmt->execute();
    $stmt->bind_result($duplicate_count);
    $stmt->fetch();
    $stmt->close();

    if ($duplicate_count > 0) {
      throw new Exception("Duplicate section: A section with this number, department, and semester already exists.");
    }

    // Insert section
    $stmt = $conn->prepare("
          INSERT INTO sms3_sections (section_number, year_level, capacity, semester_id, available, department_id) 
          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiii", $section_number, $year_level, $capacity, $semester, $available, $department_id);
    $stmt->execute();
    $newSectionId = $stmt->insert_id;
    $stmt->close();

    // Log the addition
    logAudit($conn, $_SESSION['user_id'], 'ADD', 'sms3_sections', $newSectionId, [
      'section_number' => $section_number,
      'year_level' => $year_level,
      'capacity' => $capacity,
      'semester_id' => $semester,
      'available' => $available,
      'department_id' => $department_id
    ]);

    $_SESSION['success_message'] = "Section added successfully!";
    header("Location: manage_sections.php");
    exit;
  } catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: manage_sections.php");
    exit;
  }
}

// Update section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_section'])) {
  $section_id = $_POST['section_id'];
  $section_number = $_POST['section_number'];
  $year_level = $_POST['year_level'];
  $capacity = $_POST['capacity'];
  $semester = $_POST['semester_id'];
  $available = $_POST['available'];
  $department_id = $_POST['department_id'];

  try {
    // Check for duplicates (excluding current section)
    $stmt = $conn->prepare("
          SELECT COUNT(*) 
          FROM sms3_sections 
          WHERE section_number = ? AND department_id = ? AND semester_id = ? AND id != ?");
    $stmt->bind_param("iiii", $section_number, $department_id, $semester, $section_id);
    $stmt->execute();
    $stmt->bind_result($duplicate_count);
    $stmt->fetch();
    $stmt->close();

    if ($duplicate_count > 0) {
      throw new Exception("Duplicate section: A section with this number, department, and semester already exists.");
    }

    // Fetch existing section details for logging
    $stmt = $conn->prepare("SELECT * FROM sms3_sections WHERE id = ?");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $oldSection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Update the section
    $stmt = $conn->prepare("
          UPDATE sms3_sections 
          SET section_number = ?, year_level = ?, capacity = ?, semester_id = ?, available = ?, department_id = ? 
          WHERE id = ?");
    $stmt->bind_param("iiiiiii", $section_number, $year_level, $capacity, $semester, $available, $department_id, $section_id);
    $stmt->execute();
    $stmt->close();

    // Log the update
    logAudit($conn, $_SESSION['user_id'], 'EDIT', 'sms3_sections', $section_id, [
      'id' => $section_id,
      'old' => $oldSection,
      'new' => [
        'section_number' => $section_number,
        'year_level' => $year_level,
        'capacity' => $capacity,
        'semester_id' => $semester,
        'available' => $available,
        'department_id' => $department_id
      ]
    ]);

    $_SESSION['success_message'] = "Section updated successfully!";
    header("Location: manage_sections.php");
    exit;
  } catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: manage_sections.php");
    exit;
  }
}

// Delete section
if (isset($_GET['delete_section_id'])) {
  $delete_id = $_GET['delete_section_id'];
  try {
    // Fetch section details before deletion
    $stmt = $conn->prepare("SELECT * FROM sms3_sections WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $sectionToDelete = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Delete the section
    $stmt = $conn->prepare("DELETE FROM sms3_sections WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Log the deletion
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'sms3_sections', $delete_id, $sectionToDelete);

    $_SESSION['success_message'] = "Section deleted successfully!";
    header("Location: manage_sections.php");
    exit;
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = $e->getCode() == 1451 ? "Error: This section is linked to other data." : "Error: " . $e->getMessage();
    header("Location: manage_sections.php");
    exit;
  }
}

// Fetch sections for display
$sections = $conn->query("
    SELECT s.*, d.department_code, sem.name
    FROM sms3_sections s
    JOIN sms3_departments d ON s.department_id = d.id
    JOIN sms3_semesters sem ON s.semester_id = sem.id
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Sections</title>
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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    .modal {
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

    .modal-content {
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      text-align: center;
      width: 300px;
    }

    .modal-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
    }

    .btn-danger {
      background-color: #dc3545;
      color: white;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }

    .btn:hover {
      opacity: 0.8;
    }

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
      <h1>Section</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Section</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div id="confirmationModal" class="modal">
      <div class="modal-content">
        <p id="confirmationMessage">Are you sure you want to delete this section?</p>
        <div class="modal-buttons">
          <button id="confirmDelete" class="btn btn-danger">Delete</button>
          <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <section class="section dashboard">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <?php if (isset($_GET['edit_section_id'])): ?>
              Edit Section
            <?php else: ?>
              Add Section
            <?php endif; ?>
          </h5>
          <!-- Add Section Form -->
          <form action="manage_sections.php" method="POST" class="mb-4">
            <div class="form-group">
              <label for="section_number">Section Number:</label>
              <input type="number" class="form-control" name="section_number" id="section_number" required
                value="<?= isset($edit_section) ? $edit_section['section_number'] : ''; ?>">
            </div>
            <div class="form-group mt-2">
              <label for="year_level">Year Level:</label>
              <select class="form-control" name="year_level" id="year_level" required>
                <option value="1" <?= isset($edit_section) && $edit_section['year_level'] == '1' ? 'selected' : ''; ?>>1st Year</option>
                <option value="2" <?= isset($edit_section) && $edit_section['year_level'] == '2' ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3" <?= isset($edit_section) && $edit_section['year_level'] == '3' ? 'selected' : ''; ?>>3rd Year</option>
                <option value="4" <?= isset($edit_section) && $edit_section['year_level'] == '4' ? 'selected' : ''; ?>>4th Year</option>
              </select>
            </div>
            <div class="form-group mt-2">
              <label for="capacity">Section Capacity:</label>
              <input type="number" class="form-control" name="capacity" id="capacity" required
                value="<?= isset($edit_section) ? $edit_section['capacity'] : ''; ?>" min="1" placeholder="Enter section capacity">
            </div>
            <div class="form-group mt-2">
              <label for="semester_id">Assign to semester:</label>
              <select class="form-control" name="semester_id" id="semester_id" required>
                <!-- Fetch semesters -->
                <?php
                $semesters = $conn->query("SELECT * FROM sms3_semesters");
                while ($semester = $semesters->fetch_assoc()): ?>
                  <option value="<?= $semester['id']; ?>" <?= isset($edit_section) && $edit_section['semester_id'] == $semester['id'] ? 'selected' : ''; ?>>
                    <?= $semester['name']; ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group mt-2">
              <label for="available">Available Slots:</label>
              <input type="number" class="form-control" name="available" id="available" required
                value="<?= isset($edit_section) ? $edit_section['available'] : ''; ?>" min="1" placeholder="Enter section available slots">
            </div>
            <div class="form-group mt-2">
              <label for="department_id">Assign to Department:</label>
              <select class="form-control" name="department_id" id="department_id" required>
                <!-- Fetch Departments -->
                <?php
                $departments = $conn->query("SELECT * FROM sms3_departments");
                while ($department = $departments->fetch_assoc()): ?>
                  <option value="<?= $department['id']; ?>" <?= isset($edit_section) && $edit_section['department_id'] == $department['id'] ? 'selected' : ''; ?>>
                    <?= $department['department_code']; ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <?php if (isset($edit_section)): ?>
              <input type="hidden" name="section_id" value="<?= $edit_section['id']; ?>">
              <button type="submit" name="update_section" class="btn btn-warning mt-3">Update Section</button>
            <?php else: ?>
              <button type="submit" name="add_section" class="btn btn-primary mt-3">Add Section</button>
            <?php endif; ?>
          </form>

        </div>
      </div>

      <div class="card">
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
          <h5 class="card-title">Section List</h5>
          <table style="width: 100%; min-width: 800px;" class="table table-bordered">
            <thead>
              <tr>
                <th>Section Number</th>
                <th>Year Level</th>
                <th>Capacity</th>
                <th>Semester</th>
                <th>Available Slots</th>
                <th>Department</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($section = $sections->fetch_assoc()): ?>
                <tr>
                  <td><?= $section['section_number']; ?></td>
                  <td><?= $section['year_level']; ?></td>
                  <td><?= $section['capacity']; ?></td>
                  <td><?= $section['name']; ?></td>
                  <td><?= $section['available']; ?></td>
                  <td><?= $section['department_code']; ?></td>
                  <td>
                    <a href="manage_sections.php?edit_section_id=<?= $section['id']; ?>"
                      class="btn btn-info btn-sm">Edit</a>
                    <a href="manage_sections.php?delete_section_id=<?= $section['id']; ?>"
                      class="btn btn-danger btn-sm delete-link"
                      data-section-number="<?= $section['section_number']; ?>">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
          <button id="toggle-semester-btn" class="btn btn-warning mt-3">Toggle All Semesters</button>
        </div>
      </div>

    </section>

  </main><!-- End #main -->

  <script>
    function showConfirmationModal(message, onConfirm) {
      const modal = document.getElementById('confirmationModal');
      const confirmDeleteBtn = document.getElementById('confirmDelete');
      const cancelDeleteBtn = document.getElementById('cancelDelete');
      const confirmationMessage = document.getElementById('confirmationMessage');

      confirmationMessage.innerText = message;
      modal.style.display = 'flex';

      confirmDeleteBtn.onclick = () => {
        onConfirm();
        closeModal();
      };

      cancelDeleteBtn.onclick = closeModal;

      function closeModal() {
        modal.style.display = 'none';
      }
    }

    document.querySelectorAll('.delete-link').forEach(button => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        const deleteUrl = this.href;
        const sectionNumber = this.getAttribute('data-section-number');

        showConfirmationModal(`Are you sure you want to delete the Section: ${sectionNumber}?`, () => {
          window.location.href = deleteUrl;
        });
      });
    });

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