<?php 
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

// Edit subject
$edit_subject = null;
if (isset($_GET['edit_subject_id'])) {
    $edit_subject_id = $_GET['edit_subject_id'];

    // Fetch the subject details to pre-fill the form for editing
    $stmt = $conn->prepare("SELECT * FROM sms3_subjects WHERE id = ?");
    $stmt->bind_param("i", $edit_subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_subject = $result->fetch_assoc();
    $stmt->close();
}

// Add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $department_id = $_POST['department_id'];

  try{
    // Insert subject
    $stmt = $conn->prepare("INSERT INTO sms3_subjects (subject_code, subject_name, department_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $subject_code, $subject_name, $department_id);
    $stmt->execute();
    $newSubjectId = $stmt->insert_id; // Get the ID of the new subject
    $stmt->close();

    // Log the addition
    logAudit($conn, $_SESSION['user_id'], 'ADD', 'sms3_subjects', $newSubjectId, ['subject_name' => $subject_name]);

    $_SESSION['success_message'] = "Subject added successfully!";

    // Redirect to manage_subjects.php
    header('Location: manage_subjects.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for subject code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_subjects.php'); // Redirect to show error
        exit;
    }
}

// Update subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subject'])) {
  $subject_id = $_POST['subject_id'];  // The ID of the subject being updated
  $subject_code = $_POST['subject_code'];
  $subject_name = $_POST['subject_name'];
  $department_id = $_POST['department_id'];

  try{
    // Fetch existing room details for logging
    $stmt = $conn->prepare("SELECT * FROM sms3_subjects WHERE id = ?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $oldSubject = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Update the subject in the database
    $stmt = $conn->prepare("UPDATE sms3_subjects SET subject_code = ?, subject_name = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $subject_code, $subject_name, $department_id, $subject_id);
    $stmt->execute();
    $stmt->close();

    // Log the update
    logAudit($conn, $_SESSION['user_id'], 'EDIT', 'sms3_rooms', $subject_id, [
      'id' => $subject_id,
      'old' => $oldSubject,
      'new' => ['subject_code' => $subject_code, 'subject_name' => $subject_name, 'department_id' => $department_id]
    ]);

    $_SESSION['success_message'] = "Subject updated successfully!";

    // Redirect to manage_subjects.php after updating
    header('Location: manage_subjects.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for subject code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_subjects.php'); // Redirect to show error
        exit;
    }
}

// Delete subject
if (isset($_GET['delete_subject_id'])) {
  $delete_id = $_GET['delete_subject_id'];
  try{
    // Fetch existing room details for logging
    $stmt = $conn->prepare("SELECT * FROM sms3_subjects WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $subjectToDelete = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM sms3_subjects WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Log deletion
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'sms3_subjects', $delete_id, $subjectToDelete);

    $_SESSION['success_message'] = "Subject deleted successfully!";

    // Redirect to manage_subjects.php
    header('Location: manage_subjects.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint error code
            $_SESSION['error_message'] = "Error: This room is still connected to other data.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_subjects.php'); // Redirect to show error
        exit;
    }
}

// Fetch all subjects
$subjects = $conn->query("SELECT s.*, d.department_code FROM sms3_subjects s JOIN sms3_departments d ON s.department_id = d.id");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Subjects</title>
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
      <h1>Subjects</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Subjects</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div id="confirmationModal" class="modal">
      <div class="modal-content">
          <p id="confirmationMessage">Are you sure you want to delete this department?</p>
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
        <?php if (isset($_GET['edit_subject_id'])): ?>
          Edit Subject
        <?php else: ?>
          Add Subject
        <?php endif; ?></h5>

          <!-- Add Subject Form -->
          <form action="manage_subjects.php" method="POST" class="mb-4">
            <div class="form-group">
              <label for="subject_code">Subject Code:</label>
              <input type="text" class="form-control" name="subject_code" id="subject_code" required
              value="<?= isset($edit_subject) ? $edit_subject['subject_code'] : ''; ?>">
            </div>
            <div class="form-group mt-2">
              <label for="subject_name">Subject Name:</label>
              <input type="text" class="form-control" name="subject_name" id="subject_name" required
              value="<?= isset($edit_subject) ? $edit_subject['subject_name'] : ''; ?>">
            </div>
            <div class="form-group mt-2">
              <label for="department_id">Assign to Department:</label>
              <select class="form-control" name="department_id" id="department_id" required>
                <!-- Fetch Departments -->
                <?php
                $departments = $conn->query("SELECT * FROM sms3_departments");
                while ($department = $departments->fetch_assoc()): ?>
                    <option value="<?= $department['id']; ?>" <?= isset($edit_subject) && $edit_subject['department_id'] == $department['id'] ? 'selected' : ''; ?>>
                      <?= $department['department_code']; ?>
                    </option>
                <?php endwhile; ?>
              </select>
            </div>
            <?php if (isset($edit_subject)): ?>
              <input type="hidden" name="subject_id" value="<?= $edit_subject['id']; ?>">
              <button type="submit" name="update_subject" class="btn btn-warning mt-3">Update Subject</button>
              <?php else: ?>
              <button type="submit" name="add_subject" class="btn btn-primary mt-3">Add Subject</button>
              <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="card">
      <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
      <h5 class="card-title">List of Subject</h5>
          <!-- List of Subjects -->
          <table style="width: 100%; min-width: 800px;" class="table table-bordered">
            <thead>
              <tr>
                  <th>Subject Code</th>
                  <th>Subject Name</th>
                  <th>Department</th>
                  <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($subject = $subjects->fetch_assoc()): ?>
              <tr>
                <td><?= $subject['subject_code']; ?></td>
                <td><?= $subject['subject_name']; ?></td>
                <td><?= $subject['department_code']; ?></td>
                <td>
                <a href="manage_subjects.php?edit_subject_id=<?= $subject['id']; ?>" 
                    class="btn btn-info btn-sm">Edit</a>
                <a href="manage_subjects.php?delete_subject_id=<?= $subject['id']; ?>" 
                    class="btn btn-danger btn-sm delete-link"
                    data-subject-code="<?= $subject['subject_code']; ?>">Delete</a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

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
          const subjectCode = this.getAttribute('data-subject-code');

          showConfirmationModal(`Are you sure you want to delete the Room: ${subjectCode}?`, () => {
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