<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_payment') {
  $studentNumber = $_POST['student_number'];
  $amount = $_POST['amount'];
  $paymentMethod = $_POST['payment_method'];
  $paymentType = $_POST['payment_type']; // Admission or Enrollment
  $paymentDate = date('Y-m-d H:i:s');

  if (empty($studentNumber) || empty($amount) || empty($paymentMethod) || empty($paymentType)) {
    $_SESSION['error_message'] = 'All fields are required.';
  } else {
    // Check if student exists in either sms3_students or sms3_temp_enroll
    $exists = false;
    $stmt = $conn->prepare("SELECT 1 FROM sms3_students WHERE student_number = ?");
    $stmt->bind_param("s", $studentNumber);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
      $exists = true;
    } else {
      // Check in sms3_temp_enroll for Admission payments
      if ($paymentType === 'Admission') {
        $stmt = $conn->prepare("SELECT 1 FROM sms3_temp_enroll WHERE student_number = ?");
        $stmt->bind_param("s", $studentNumber);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
      }
    }
    $stmt->close();

    if ($exists) {
      $stmt = $conn->prepare("INSERT INTO sms3_payments (student_number, amount, payment_method, payment_type, payment_date) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sdsss", $studentNumber, $amount, $paymentMethod, $paymentType, $paymentDate);

      if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Payment processed successfully.';
      } else {
        $_SESSION['error_message'] = 'Failed to process payment: ' . $conn->error;
      }
      $stmt->close();
    } else {
      $_SESSION['error_message'] = 'Student not found in either enrolled or temporary admission list.';
    }
  }

  header('Location: manage_payment.php');
  exit;
}


// Handle delete payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_payment') {
  $paymentId = $_POST['payment_id'];

  $stmt = $conn->prepare("DELETE FROM sms3_payments WHERE id = ?");
  $stmt->bind_param("i", $paymentId);

  if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Payment deleted successfully.';
  } else {
    $_SESSION['error_message'] = 'Failed to delete payment.';
  }
  $stmt->close();

  header('Location: manage_payment.php');
  exit;
}

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
      <h1>Payment</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Payment</li>
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
        <div class="card">

          <!-- Tabs -->
          <ul class="nav nav-tabs mt-3" id="paymentTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" id="admission-tab" data-bs-toggle="tab" data-bs-target="#admission" type="button" role="tab" aria-controls="admission" aria-selected="true">Admission</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" id="enrollment-tab" data-bs-toggle="tab" data-bs-target="#enrollment" type="button" role="tab" aria-controls="enrollment" aria-selected="false">Enrollment</button>
            </li>
          </ul>
          <div class="tab-content mb-3" id="paymentTabsContent">
            <!-- Admission Tab -->
            <div class="tab-pane fade show active" id="admission" role="tabpanel" aria-labelledby="admission-tab">
              <form method="POST" action="">
                <input type="hidden" name="action" value="add_payment">
                <input type="hidden" name="payment_type" value="Admission">
                <div class="mb-3">
                  <label for="student_number_admission" class="form-label">Student Number</label>
                  <select class="form-select" id="student_number_admission" name="student_number" required>
                    <option value="">Select Student</option>
                    <?php
                    $result = $conn->query("SELECT student_number, first_name, last_name FROM sms3_temp_enroll");
                    while ($row = $result->fetch_assoc()) {
                      echo "<option value='{$row['student_number']}'>{$row['student_number']} - {$row['first_name']} {$row['last_name']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="amount_admission" class="form-label">Amount</label>
                  <input type="number" class="form-control" id="amount_admission" name="amount" step="0.01" required>
                </div>
                <div class="mb-3">
                  <label for="payment_method_admission" class="form-label">Payment Method</label>
                  <select class="form-select" id="payment_method_admission" name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit Payment</button>
              </form>
            </div>

            <!-- Enrollment Tab -->
            <div class="tab-pane fade" id="enrollment" role="tabpanel" aria-labelledby="enrollment-tab">
              <form method="POST" action="">
                <input type="hidden" name="action" value="add_payment">
                <input type="hidden" name="payment_type" value="Enrollment">
                <div class="mb-3">
                  <label for="student_number_enrollment" class="form-label">Student Number</label>
                  <select class="form-select" id="student_number_enrollment" name="student_number" required>
                    <option value="">Select Student</option>
                    <?php
                    $result = $conn->query("SELECT student_number, first_name, last_name FROM sms3_students");
                    while ($row = $result->fetch_assoc()) {
                      echo "<option value='{$row['student_number']}'>{$row['student_number']} - {$row['first_name']} {$row['last_name']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="amount_enrollment" class="form-label">Amount</label>
                  <input type="number" class="form-control" id="amount_enrollment" name="amount" step="0.01" required>
                </div>
                <div class="mb-3">
                  <label for="payment_method_enrollment" class="form-label">Payment Method</label>
                  <select class="form-select" id="payment_method_enrollment" name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit Payment</button>
              </form>
            </div>
          </div><!-- End Tabs -->

        </div>
        <div class="card">
          <!-- Payment Records -->
          <h5 class="card-title mt-5">Payment Records</h5>
          <table class="table datatable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Student Number</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Type</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $result = $conn->query("SELECT * FROM sms3_payments");
              while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['student_number']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['payment_method']}</td>
                    <td>{$row['payment_type']}</td>
                    <td>{$row['payment_date']}</td>
                    <td>
                      <form method='POST' action='' style='display:inline;'>
                        <input type='hidden' name='action' value='delete_payment'>
                        <input type='hidden' name='payment_id' value='{$row['id']}'>
                        <button type='button' class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>Delete</button>
                      </form>
                    </td>
                  </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>

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