<?php
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Edit room
$edit_room = null;
if (isset($_GET['edit_room_id'])) {
    $edit_room_id = $_GET['edit_room_id'];

    // Fetch the room details to pre-fill the form for editing
    $stmt = $conn->prepare("SELECT * FROM sms3_rooms WHERE id = ?");
    $stmt->bind_param("i", $edit_room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_room = $result->fetch_assoc();
    $stmt->close();
}

// Add room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
  $room_name = $_POST['room_name'];
  $location = $_POST['location'];
  $department_id = $_POST['department_id'];

  try{
    // Insert room
    $stmt = $conn->prepare("INSERT INTO sms3_rooms (room_name, location, department_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $room_name, $location, $department_id);
    $stmt->execute();
    $newRoomId = $stmt->insert_id; // Get the ID of the new room
    $stmt->close();

    // Log the addition
    logAudit($conn, $_SESSION['user_id'], 'ADD', 'sms3_rooms', $newRoomId, ['room_name' => $room_name]);

    $_SESSION['success_message'] = "Room added successfully!";
    header('Location: manage_rooms.php');
    exit;
  } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for department code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_rooms.php'); // Redirect to show error
        exit;
    }
}

// Update room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])) {
  $room_id = $_POST['room_id'];
  $room_name = $_POST['room_name'];
  $location = $_POST['location'];
  $department_id = $_POST['department_id'];

  try {
    // Fetch existing room details for logging
    $stmt = $conn->prepare("SELECT * FROM sms3_rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $oldRoom = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Update the room
    $stmt = $conn->prepare("UPDATE sms3_rooms SET room_name = ?, location = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $room_name, $location, $department_id, $room_id);
    $stmt->execute();
    $stmt->close();

    // Log the update
    logAudit($conn, $_SESSION['user_id'], 'EDIT', 'sms3_rooms', $room_id, [
      'id' => $room_id,
      'old' => $oldRoom,
      'new' => ['room_name' => $room_name, 'location' => $location, 'department_id' => $department_id]
    ]);

    $_SESSION['success_message'] = "Room updated successfully!";
    header('Location: manage_rooms.php');
    exit;
  } catch (mysqli_sql_exception $e) {
      if ($e->getCode() == 1062) { // Duplicate entry error code
          $_SESSION['error_message'] = "Error: Duplicate entry for department code or name.";
      } else {
          $_SESSION['error_message'] = "Error: " . $e->getMessage();
      }
      header('Location: manage_rooms.php'); // Redirect to show error
      exit;
  }
}

// Delete room
if (isset($_GET['delete_room_id'])) {
  $delete_id = $_GET['delete_room_id'];
  try {
    // Fetch room details for logging
    $stmt = $conn->prepare("SELECT * FROM sms3_rooms WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $roomToDelete = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Delete the room
    $stmt = $conn->prepare("DELETE FROM sms3_rooms WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Log deletion
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'sms3_rooms', $delete_id, $roomToDelete);

    $_SESSION['success_message'] = "Room deleted successfully!";
    header('Location: manage_rooms.php');
    exit;
  } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint error code
            $_SESSION['error_message'] = "Error: This room is still connected to other data.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_rooms.php'); // Redirect to show error
        exit;
    }
}

// Fetch all rooms
$rooms = $conn->query("SELECT r.*, d.department_code FROM sms3_rooms r JOIN sms3_departments d ON r.department_id = d.id");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Rooms</title>
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
      <h1>Rooms</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Rooms</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div id="confirmationModal" class="modal">
      <div class="modal-content">
          <p id="confirmationMessage">Are you sure you want to delete this room?</p>
          <div class="modal-buttons">
              <button id="confirmDelete" class="btn btn-danger">Delete</button>
              <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
          </div>
      </div>
    </div>


    <section class="section dashboard">
    <div class="row">

      <div class="card">
        <div class="card-body">
        <h5 class="card-title"><?php if (isset($_GET['edit_room_id'])): ?>
          Edit Room
        <?php else: ?>
          Add Room
        <?php endif; ?>
        </h5>
          <form action="manage_rooms.php" method="POST" class="mb-4">
            <div class="form-group">
              <label for="room_name">Room Name:</label>
              <input type="text" class="form-control" name="room_name" id="room_name" required
                    value="<?= isset($edit_room) ? $edit_room['room_name'] : ''; ?>">
            </div>

            <div class="form-group mt-2">
              <label for="location">Location:</label>
              <select class="form-control" name="location" id="location" required>
                <option value="2nd Floor" <?= isset($edit_room) && $edit_room['location'] == '2nd Floor' ? 'selected' : ''; ?>>2nd Floor</option>
                <option value="3rd Floor" <?= isset($edit_room) && $edit_room['location'] == '3rd Floor' ? 'selected' : ''; ?>>3rd Floor</option>
                <option value="4th Floor" <?= isset($edit_room) && $edit_room['location'] == '4th Floor' ? 'selected' : ''; ?>>4th Floor</option>
                <option value="5th Floor" <?= isset($edit_room) && $edit_room['location'] == '5th Floor' ? 'selected' : ''; ?>>5th Floor</option>
              </select>
            </div>

            <div class="form-group mt-2">
            <label for="department_id">Assign to Department:</label>
            <select class="form-control" name="department_id" id="department_id" required>
              <!-- Fetch Departments -->
              <?php
              $departments = $conn->query("SELECT * FROM sms3_departments");
              while ($department = $departments->fetch_assoc()): ?>
                <option value="<?= $department['id']; ?>" <?= isset($edit_room) && $edit_room['department_id'] == $department['id'] ? 'selected' : ''; ?>>
                  <?= $department['department_code']; ?>
                </option>
              <?php endwhile; ?>
            </select>
            </div>

            <?php if (isset($edit_room)): ?>
              <input type="hidden" name="room_id" value="<?= $edit_room['id']; ?>">
              <button type="submit" name="update_room" class="btn btn-warning mt-3">Update Room</button>
            <?php else: ?>
              <button type="submit" name="add_room" class="btn btn-primary mt-3">Add Room</button>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <div class="card">
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
        <h5 class="card-title">Room List</h5>
          <table style="width: 100%; min-width: 800px;" class="table table-bordered">
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Location</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
              <?php while ($room = $rooms->fetch_assoc()): ?>
                <tr>
                    <td><?= $room['room_name']; ?></td>
                    <td><?= $room['location']; ?></td>
                    <td><?= $room['department_code']; ?></td>
                    <td>
                    <a href="manage_rooms.php?edit_room_id=<?= $room['id']; ?>" 
                      class="btn btn-info btn-sm">Edit</a>
                    <a href="manage_rooms.php?delete_room_id=<?= $room['id']; ?>" 
                      class="btn btn-danger btn-sm delete-link" 
                      data-room-name="<?= $room['room_name']; ?>">Delete</a>
                    </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
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
          const roomName = this.getAttribute('data-room-name');

          showConfirmationModal(`Are you sure you want to delete the Room: ${roomName}?`, () => {
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