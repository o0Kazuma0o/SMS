<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>User</title>
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
      <h1>User</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">User</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div id="confirmationModal" class="modal">
      <div class="modal-content">
        <p id="confirmationMessage"></p>
        <div class="modal-buttons">
          <button id="confirmDelete" class="btn btn-danger">Delete</button>
          <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <section class="section dashboard">
      <div class="row">

      </div>
    </section>

  </main><!-- End #main -->

  <script>
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

    // Confirmation modal for deletion
    document.querySelectorAll('.delete-link').forEach(button => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        const deleteUrl = this.href;
        const username = this.getAttribute('data-username');

        const modal = document.getElementById('confirmationModal');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const confirmationMessage = document.getElementById('confirmationMessage');

        confirmationMessage.innerText = `Are you sure you want to delete the user: ${name}?`;
        modal.style.display = 'flex';

        confirmDeleteBtn.onclick = () => {
          window.location.href = deleteUrl;
          modal.style.display = 'none';
        };

        cancelDeleteBtn.onclick = () => modal.style.display = 'none';
      });
    });


    // Popup message function
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