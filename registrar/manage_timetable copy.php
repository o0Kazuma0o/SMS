<?php
require('../database.php');
require_once 'session.php';
require_once 'audit_log_function.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

// Edit timetable
$edit_timetable = null;
if (isset($_GET['edit_timetable_id'])) {
  $edit_timetable_id = $_GET['edit_timetable_id'];

  // Fetch the timetable details for editing (fetch multiple entries based on timetable id)
  $stmt = $conn->prepare("
        SELECT t.id, t.subject_id, s.subject_code, t.day_of_week, t.start_time, t.end_time 
        FROM sms3_timetable t 
        JOIN sms3_subjects s ON t.subject_id = s.id 
        WHERE t.id = ?");
  $stmt->bind_param("i", $edit_timetable_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $edit_timetable = $result->fetch_all(MYSQLI_ASSOC); // Fetch multiple rows for the timetable
  $stmt->close();
}

// Insert new section entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_section_entry'])) {
  $section_id = $_POST['section_id'];

  try {
    $conn->begin_transaction();

    // Insert a new timetable entry with null values for additional fields
    $stmt = $conn->prepare("INSERT INTO sms3_timetable (section_id, subject_id, room_id, day_of_week, start_time, end_time)
                              VALUES (?, NULL, NULL, NULL, NULL, NULL)");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();

    $timetableId = $stmt->insert_id;

    // Log the action
    logAudit($conn, $_SESSION['user_id'], 'ADD', 'sms3_timetable', $timetableId, [
      'section_id' => $section_id,
      'subject_id' => null,
      'room_id' => null,
      'day_of_week' => null,
      'start_time' => null,
      'end_time' => null,
    ]);

    $conn->commit();
    $_SESSION['success_message'] = "Section entry added successfully!";
    header('Location: manage_timetable.php');
    exit;
  } catch (mysqli_sql_exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: manage_timetable.php');
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_timetable_changes'])) {
  $section_id = $_POST['section_id'];
  $subjects = $_POST['subjects'];
  $rooms = $_POST['rooms'];
  $days = $_POST['days'];
  $start_times = $_POST['start_times'];
  $end_times = $_POST['end_times'];

  if (
    count($subjects) === count($rooms) && count($rooms) === count($days) &&
    count($days) === count($start_times) && count($start_times) === count($end_times)
  ) {
    try {
      $conn->begin_transaction();

      for ($i = 0; $i < count($subjects); $i++) {
        $subject_id = $subjects[$i];
        $room_id = $rooms[$i];
        $day_of_week = $days[$i];
        $start_time = $start_times[$i];
        $end_time = $end_times[$i];

        // Validate duplicate subject in the same section
        $stmt = $conn->prepare("
                  SELECT COUNT(*) 
                  FROM sms3_timetable 
                  WHERE section_id = ? AND subject_id = ?");
        $stmt->bind_param("ii", $section_id, $subject_id);
        $stmt->execute();
        $stmt->bind_result($duplicate_count);
        $stmt->fetch();
        $stmt->close();

        if ($duplicate_count > 0) {
          $_SESSION['error_message'] = "Error: Duplicate subject in section.";
          $conn->rollback();
          header('Location: manage_timetable.php');
          exit;
        }

        // Validate time conflicts
        $stmt = $conn->prepare("
                  SELECT COUNT(*) 
                  FROM sms3_timetable 
                  WHERE section_id = ? AND day_of_week = ? 
                  AND ((start_time < ? AND end_time > ?) 
                  OR (start_time < ? AND end_time > ?))");
        $stmt->bind_param("isssss", $section_id, $day_of_week, $end_time, $start_time, $end_time, $start_time);
        $stmt->execute();
        $stmt->bind_result($conflict_count);
        $stmt->fetch();
        $stmt->close();

        if ($conflict_count > 0) {
          $_SESSION['error_message'] = "Error: Time conflict detected.";
          $conn->rollback();
          header('Location: manage_timetable.php');
          exit;
        }

        // Check for existing row with null values
        $stmt = $conn->prepare("
                  SELECT id 
                  FROM sms3_timetable 
                  WHERE section_id = ? 
                  AND (subject_id IS NULL OR room_id IS NULL OR day_of_week IS NULL OR start_time IS NULL OR end_time IS NULL) 
                  LIMIT 1");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $stmt->bind_result($existing_row_id);
        $stmt->fetch();
        $stmt->close();

        if ($existing_row_id) {
          // Update the existing row
          $stmt = $conn->prepare("
                      UPDATE sms3_timetable 
                      SET subject_id = ?, room_id = ?, day_of_week = ?, start_time = ?, end_time = ? 
                      WHERE id = ?");
          $stmt->bind_param("iisssi", $subject_id, $room_id, $day_of_week, $start_time, $end_time, $existing_row_id);
        } else {
          // Insert a new row
          $stmt = $conn->prepare("
                      INSERT INTO sms3_timetable (subject_id, section_id, room_id, day_of_week, start_time, end_time) 
                      VALUES (?, ?, ?, ?, ?, ?)");
          $stmt->bind_param("iiisss", $subject_id, $section_id, $room_id, $day_of_week, $start_time, $end_time);
        }

        if (!$stmt->execute()) {
          error_log("SQL Error: " . $stmt->error); // Log SQL errors
          $_SESSION['error_message'] = "Error processing timetable.";
          $conn->rollback();
          header('Location: manage_timetable.php');
          exit;
        }
        $stmt->close();

        // Log the action
        logAudit($conn, $_SESSION['user_id'], 'ADD', 'sms3_timetable', $section_id, [
          'subject_id' => $subject_id,
          'section_id' => $section_id,
          'room_id' => $room_id,
          'day_of_week' => $day_of_week,
          'start_time' => $start_time,
          'end_time' => $end_time
        ]);
      }

      $conn->commit();
      $_SESSION['success_message'] = "Timetable added successfully!";
      header('Location: manage_timetable.php');
      exit;
    } catch (mysqli_sql_exception $e) {
      $conn->rollback();
      error_log("Transaction failed: " . $e->getMessage()); // Log error details
      $_SESSION['error_message'] = "Error: " . $e->getMessage();
      header('Location: manage_timetable.php');
      exit;
    }
  } else {
    $_SESSION['error_message'] = "Mismatch in input fields.";
    header('Location: manage_timetable.php');
    exit;
  }
}

// Update timetable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_single_timetable'])) {
  $timetable_id = intval($_POST['timetable_id']);
  $section_id = intval($_POST['section_id']);
  $subject_id = intval($_POST['subject_id']);
  $room_id = intval($_POST['room_id']);
  $day_of_week = $_POST['day'];
  $start_time = $_POST['start_time'];
  $end_time = $_POST['end_time'];

  try {
    if ($subject_id && $room_id) {
      // Fetch existing data for logging
      $stmt = $conn->prepare("SELECT * FROM sms3_timetable WHERE id = ?");
      $stmt->bind_param("i", $timetable_id);
      $stmt->execute();
      $oldData = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      // Validate for duplicate subjects in the same section
      $stmt = $conn->prepare("
              SELECT COUNT(*) 
              FROM sms3_timetable 
              WHERE section_id = ? AND subject_id = ? AND id != ?");
      $stmt->bind_param("iii", $section_id, $subject_id, $timetable_id);
      $stmt->execute();
      $stmt->bind_result($duplicate_count);
      $stmt->fetch();
      $stmt->close();

      if ($duplicate_count > 0) {
        $_SESSION['error_message'] = "Error: Duplicate subject in section.";
        header('Location: manage_timetable.php');
        exit;
      }

      // Validate for time conflicts
      $stmt = $conn->prepare("
              SELECT COUNT(*) 
              FROM sms3_timetable 
              WHERE section_id = ? AND day_of_week = ? 
              AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?)) AND id != ?");
      $stmt->bind_param("isssssi", $section_id, $day_of_week, $end_time, $start_time, $end_time, $start_time, $timetable_id);
      $stmt->execute();
      $stmt->bind_result($conflict_count);
      $stmt->fetch();
      $stmt->close();

      if ($conflict_count > 0) {
        $_SESSION['error_message'] = "Error: Time conflict detected on {$day_of_week} between {$start_time} and {$end_time}.";
        header('Location: manage_timetable.php');
        exit;
      }

      // Update timetable
      $stmt = $conn->prepare("
              UPDATE sms3_timetable 
              SET subject_id = ?, room_id = ?, day_of_week = ?, start_time = ?, end_time = ? 
              WHERE id = ?");
      $stmt->bind_param("iisssi", $subject_id, $room_id, $day_of_week, $start_time, $end_time, $timetable_id);
      $stmt->execute();

      // Log the action
      logAudit($conn, $_SESSION['user_id'], 'EDIT', 'sms3_timetable', $timetable_id, [
        'id' => $timetable_id,
        'old' => $oldData,
        'new' => [
          'subject_id' => $subject_id,
          'room_id' => $room_id,
          'day_of_week' => $day_of_week,
          'start_time' => $start_time,
          'end_time' => $end_time
        ]
      ]);

      $_SESSION['success_message'] = "Timetable updated successfully!";
      header('Location: manage_timetable.php');
      exit;
    } else {
      $_SESSION['error_message'] = "Invalid subject or room.";
      header('Location: manage_timetable.php');
      exit;
    }
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: manage_timetable.php');
    exit;
  }
}

// Delete entire timetable for a section (or group of timetables)
if (isset($_GET['delete_timetable_id'])) {
  $sectionId = intval($_GET['delete_timetable_id']);
  try {
    // Check if timetable exists for the given section ID
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sms3_timetable WHERE section_id = ?");
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
      // Delete all timetables for the section
      $stmt = $conn->prepare("DELETE FROM sms3_timetable WHERE section_id = ?");
      $stmt->bind_param("i", $sectionId);
      $stmt->execute();
      $stmt->close();

      $_SESSION['success_message'] = "Entire timetable deleted successfully!";
    } else {
      $_SESSION['error_message'] = "No timetable entries found for the section.";
    }
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
  }
  header('Location: manage_timetable.php');
  exit;
}

// Delete a single row from the timetable
if (isset($_GET['delete_row_id'])) {
  $delete_row_id = $_GET['delete_row_id'];

  try {
    // Fetch details for audit logging
    $stmt = $conn->prepare("
          SELECT t.id, s.subject_code, sec.section_number, r.room_name, t.day_of_week, t.start_time, t.end_time
          FROM sms3_timetable t
          JOIN sms3_subjects s ON t.subject_id = s.id
          JOIN sms3_sections sec ON t.section_id = sec.id
          JOIN sms3_rooms r ON t.room_id = r.id
          WHERE t.id = ?
      ");
    $stmt->bind_param("i", $delete_row_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $deletedTimetable = $result->fetch_assoc();
    $stmt->close();

    // Delete the timetable entry
    $stmt = $conn->prepare("DELETE FROM sms3_timetable WHERE id = ?");
    $stmt->bind_param("i", $delete_row_id);
    $stmt->execute();
    $stmt->close();

    // Log the deletion
    if ($deletedTimetable) {
      logAudit($conn, $_SESSION['user_id'], 'DELETE', 'sms3_timetable', $deletedTimetable['id'], [
        'id' => $deletedTimetable['id'], // Ensure ID is included
        'subject_code' => $deletedTimetable['subject_code'],
        'section_number' => $deletedTimetable['section_number'],
        'room_name' => $deletedTimetable['room_name'],
        'day_of_week' => $deletedTimetable['day_of_week'],
        'start_time' => $deletedTimetable['start_time'],
        'end_time' => $deletedTimetable['end_time']
      ]);
    }

    $_SESSION['success_message'] = "Timetable entry deleted successfully!";
    header('Location: manage_timetable.php');
    exit;
  } catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: manage_timetable.php');
    exit;
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Timetable</title>
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
    #confirmationModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1100;
    }

    #confirmationModalContent {
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
      <h1>Timetable</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Timetable</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div id="confirmationModal" class="modal">
      <div class="modal-content" id="confirmationModalContent">
        <p id="confirmationMessage">Are you sure you want to delete this Timetable?</p>
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
            <h5 class="card-title"></h5>
            <!-- Add Timetable Form -->
            <form action="manage_timetable.php" method="POST" class="mb-4" onsubmit="return validateTimetableForm();">

              <!-- Select Department -->
              <div class="form-group">
                <label for="department_id">Select Department:</label>
                <select class="form-control" name="department_id" id="department_id" required onchange="fetchRelatedData()">
                  <option value="">Select Department</option>
                  <!-- Fetch Departments -->
                  <?php
                  $departments = $conn->query("SELECT * FROM sms3_departments");
                  while ($department = $departments->fetch_assoc()): ?>
                    <option value="<?= $department['id']; ?>"><?= $department['department_code']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>

              <!-- Select Section -->
              <div class="form-group mt-3">
                <label for="section_id">Select Section:</label>
                <select class="form-control" name="section_id" id="section_id" required>
                  <option value="">Select Section</option>
                  <!-- Sections will be populated dynamically based on the selected department -->
                </select>
              </div>

              <button type="submit" name="create_section_entry" class="btn btn-primary mt-3">Add Section Entry</button>
            </form>

          </div>
        </div>

        <div class="card">
          <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
            <h5 class="card-title">List of Timetables</h5>
            <table style="width: 100%; min-width: 800px;" class="table table-bordered">
              <thead>
                <tr>
                  <th>Department</th>
                  <th>Section</th>
                  <th>Timetable</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Fetch grouped timetables from the database
                $sql = "
                SELECT 
                    sec.id AS section_id,
                    d.department_code, 
                    sec.section_number, 
                    GROUP_CONCAT(DISTINCT s.subject_code SEPARATOR ', ') AS subjects,
                    GROUP_CONCAT(DISTINCT t.day_of_week SEPARATOR ', ') AS days,
                    GROUP_CONCAT(DISTINCT TIME_FORMAT(t.start_time, '%H:%i') SEPARATOR ', ') AS start_times,
                    GROUP_CONCAT(DISTINCT TIME_FORMAT(t.end_time, '%H:%i') SEPARATOR ', ') AS end_times,
                    GROUP_CONCAT(DISTINCT r.room_name SEPARATOR ', ') AS rooms
                FROM sms3_timetable t
                JOIN sms3_sections sec ON t.section_id = sec.id
                JOIN sms3_departments d ON sec.department_id = d.id
                LEFT JOIN sms3_subjects s ON t.subject_id = s.id
                LEFT JOIN sms3_rooms r ON t.room_id = r.id
                GROUP BY sec.id, d.department_code, sec.section_number
                ORDER BY d.department_code, sec.section_number;
              ";
                $timetables = $conn->query($sql);

                // Loop through grouped timetables
                while ($timetable = $timetables->fetch_assoc()):
                ?>
                  <tr>
                    <td><?= htmlspecialchars($timetable['department_code']); ?></td>
                    <td><?= htmlspecialchars($timetable['section_number']); ?></td>
                    <td>
                      <button
                        class="btn btn-info btn-sm"
                        onclick="viewTimetableDetails('<?= htmlspecialchars($timetable['section_number']); ?>', '<?= htmlspecialchars($timetable['department_code']); ?>')">
                        View Timetable
                      </button>
                    </td>
                    <td>
                      <a
                        href="manage_timetable.php?delete_section_id=<?= htmlspecialchars($timetable['section_id']); ?>"
                        class="btn btn-danger btn-sm delete-link"
                        data-section-id="<?= htmlspecialchars($timetable['section_id']); ?>">
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
    </section>

    <!-- Modal for Timetable Details -->
    <div class="modal fade" id="timetableModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Timetable Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="modal-body">
            <form id="timetableForm" action="manage_timetable.php" method="POST">
              <input type="hidden" name="section_id" id="modal_section_id">
              <table style="width: 100%; min-width: 800px;" class="table table-bordered" id="timetable-details">
                <thead>
                  <tr>
                    <th>Subject</th>
                    <th>Day</th>
                    <th>Room</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Timetable details will be populated dynamically -->
                </tbody>
              </table>
              <div class="d-flex justify-content-begin">
                <button type="button" class="btn btn-primary me-2" id="addRowButton">+</button>
                <button type="button" class="btn btn-danger" id="removeRowButton">-</button>
              </div>
              <div class="mt-3">
                <button type="submit" name="save_timetable_changes" class="btn btn-success">Save Changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Editing Timetable -->
    <div class="modal fade" id="editTimetableModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Timetable</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="editTimetableForm" action="manage_timetable.php" method="POST">
              <input type="hidden" name="timetable_id" id="editTimetableId">

              <!-- Subject -->
              <div class="form-group">
                <label for="editSubject">Subject:</label>
                <select class="form-control" name="subject_id" id="editSubject" required>
                  <option value="">Select Subject</option>
                  <!-- Populated dynamically -->
                </select>
              </div>

              <!-- Day -->
              <div class="form-group mt-3">
                <label for="editDay">Day:</label>
                <select class="form-control" name="day" id="editDay" required>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                  <option value="Saturday">Saturday</option>
                </select>
              </div>

              <!-- Room -->
              <div class="form-group mt-3">
                <label for="editRoom">Room:</label>
                <select class="form-control" name="room_id" id="editRoom" required>
                  <option value="">Select Room</option>
                  <!-- Populated dynamically -->
                </select>
              </div>

              <!-- Start Time -->
              <div class="form-group mt-3">
                <label for="editStartTime">Start Time:</label>
                <input type="time" class="form-control" name="start_time" id="editStartTime" required>
              </div>

              <!-- End Time -->
              <div class="form-group mt-3">
                <label for="editEndTime">End Time:</label>
                <input type="time" class="form-control" name="end_time" id="editEndTime" required>
              </div>

              <!-- Submit Button -->
              <button type="submit" name="update_single_timetable" class="btn btn-primary mt-3">Update Timetable</button>
              <button type="button" class="btn btn-secondary mt-3" id="cancelEditButton">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- JavaScript for AJAX -->
    <script>
      let currentDepartmentId = null;
      // Fetch and display timetable details in a modal
      function viewTimetableDetails(sectionNumber, departmentCode) {
        currentDepartmentId = departmentCode;
        fetch(`fetch_timetable_details.php?section_number=${sectionNumber}&department_code=${departmentCode}`)
          .then(response => response.json())
          .then(data => {
            const timetableDetails = document.getElementById('timetable-details').getElementsByTagName('tbody')[0];
            timetableDetails.innerHTML = ''; // Clear any previous content

            const sectionIdField = document.getElementById('modal_section_id');
            sectionIdField.value = data.section_id; // Now section_id is guaranteed to be available

            // Log to verify the value
            console.log("Section ID set to:", sectionIdField.value);

            // Log form data to verify
            const formData = new FormData(document.getElementById('timetableForm'));
            formData.forEach((value, key) => {
              console.log(key + ": " + value);
            });

            // If timetable data exists, populate it in the table
            if (data.timetable.length > 0) {
              data.timetable.forEach(row => {
                const newRow = `
          <tr>
            <td>${row.subject_code}</td>
            <td>${row.day_of_week}</td>
            <td>${row.room_name}</td>
            <td>${row.start_time}</td>
            <td>${row.end_time}</td>
            <td>
              <button class="btn btn-sm btn-warning" onclick="editTimetable(${row.id})">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="deleteTimetableRow(${row.id})">Delete</button>
            </td>
          </tr>`;
                timetableDetails.insertAdjacentHTML('beforeend', newRow);
              });
            } else {
              // If no timetable exists, ensure form is still valid (e.g., `section_id` set)
              console.log("No timetable data found for this section.");
            }

            const timetableModal = new bootstrap.Modal(document.getElementById('timetableModal'));
            timetableModal.show();
          });
      }

      // Delete individual timetable row
      function deleteTimetableRow(rowId) {
        if (!confirm("Are you sure you want to delete this timetable entry?")) {
          return;
        }

        fetch(`manage_timetable.php?delete_row_id=${rowId}`, {
            method: 'GET'
          })
          .then(response => {
            if (!response.ok) {
              throw new Error("Failed to delete timetable entry.");
            }
            alert("Timetable entry deleted successfully!");
            // Remove the row from the table
            const rowElement = document.querySelector(`tr[data-row-id='${rowId}']`);
            if (rowElement) {
              rowElement.remove();
            }
          })
          .catch(error => {
            console.error("Error deleting timetable entry:", error);
            alert("An error occurred while trying to delete the timetable entry.");
          });
      }


      document.addEventListener("DOMContentLoaded", function() {
        const timetableTableBody = document.querySelector("#timetable-details tbody");

        // Add a new row
        document.getElementById("addRowButton").addEventListener("click", () => {
          if (!currentDepartmentId) {
            alert("Please view a timetable to associate the department.");
            return;
          }

          const newRow = document.createElement("tr");
          newRow.innerHTML = `
        <td>
          <select class="form-control subject-dropdown" name="subjects[]" required>
            <option value="">Select Subject</option>
            <!-- Options populated dynamically -->
          </select>
        </td>
        <td>
          <select class="form-control" name="days[]" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
          </select>
        </td>
        <td>
          <select class="form-control room-dropdown" name="rooms[]" required>
            <option value="">Select Room</option>
            <!-- Options populated dynamically -->
          </select>
        </td>
        <td>
          <input type="time" class="form-control" name="start_times[]" required>
        </td>
        <td>
          <input type="time" class="form-control" name="end_times[]" required>
        </td>
      `;

          timetableTableBody.appendChild(newRow);
          populateDropdowns(newRow, currentDepartmentId); // Pass the currentDepartmentId dynamically.
        });

        // Remove a row
        document.getElementById("removeRowButton").addEventListener("click", () => {
          const rows = timetableTableBody.querySelectorAll("tr");
          if (rows.length > 0) {
            timetableTableBody.removeChild(rows[rows.length - 1]);
          }
        });

        // Populate Dropdowns for Subjects and Rooms
        async function populateDropdowns(row, departmentCode) {
          if (!departmentCode) {
            alert("Department Code is missing.");
            return;
          }

          try {
            // Fetch subjects
            const subjectResponse = await fetch(`fetch_subjects.php?department_code=${departmentCode}`);
            const subjectOptions = await subjectResponse.text();
            row.querySelector(".subject-dropdown").innerHTML = subjectOptions;

            // Fetch rooms
            const roomResponse = await fetch(`fetch_rooms.php?department_code=${departmentCode}`);
            const roomOptions = await roomResponse.text();
            row.querySelector(".room-dropdown").innerHTML = roomOptions;
          } catch (error) {
            console.error("Error fetching subjects or rooms:", error);
          }
        }

        // Handle row removal
        timetableTableBody.addEventListener("click", (event) => {
          if (event.target.classList.contains("remove-row")) {
            event.target.closest("tr").remove();
          }
        });
      });

      function showConfirmationModal1(message, onConfirm) {
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

        cancelDeleteBtn.onclick = () => {
          closeModal();
          // Show the viewTimetableDetails modal again if the confirmation is canceled
          var timetableModal = new bootstrap.Modal(document.getElementById('timetableModal'));
          timetableModal.show();
        };

        function closeModal() {
          modal.style.display = 'none';
        }
      }

      function editTimetable(timetableId) {
        fetch(`fetch_single_timetable_detail.php?id=${timetableId}`)
          .then(response => {
            if (!response.ok) {
              throw new Error("Failed to fetch timetable details.");
            }
            return response.json();
          })
          .then(data => {
            console.log("Fetched data:", data); // Log data for debugging
            if (!data || !data.id) {
              alert("Error: Timetable data not found.");
              return;
            }

            // Populate modal fields
            document.getElementById('editTimetableId').value = data.id;
            document.getElementById('editDay').value = data.day_of_week;
            document.getElementById('editStartTime').value = data.start_time;
            document.getElementById('editEndTime').value = data.end_time;

            // Populate the subjects dropdown
            fetch(`fetch_subjects.php?department_id=${data.department_id}`)
              .then(response => response.text())
              .then(subjectOptions => {
                const subjectDropdown = document.getElementById('editSubject');
                subjectDropdown.innerHTML = subjectOptions;
                subjectDropdown.value = data.subject_id;
              })
              .catch(error => console.error("Error fetching subjects:", error));

            // Populate the rooms dropdown
            fetch(`fetch_rooms.php?department_id=${data.department_id}`)
              .then(response => response.text())
              .then(roomOptions => {
                const roomDropdown = document.getElementById('editRoom');
                roomDropdown.innerHTML = roomOptions;
                roomDropdown.value = data.room_id;
              })
              .catch(error => console.error("Error fetching rooms:", error));

            // Show the edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editTimetableModal'));
            editModal.show();
          })
          .catch(error => {
            console.error("Error during fetch or modal population:", error);
            alert("An error occurred while trying to edit the timetable.");
          });
      }

      // Handle the Cancel button functionality
      document.getElementById('cancelEditButton').addEventListener('click', function() {
        // Hide the "Edit Timetable" modal
        var editTimetableModal = bootstrap.Modal.getInstance(document.getElementById('editTimetableModal'));
        editTimetableModal.hide();

        // Show the "View Timetable" modal again
        var timetableModal = new bootstrap.Modal(document.getElementById('timetableModal'));
        timetableModal.show();
      });

      function fetchRelatedData() {
        var departmentId = document.getElementById('department_id').value;

        // Clear the dropdowns if no department selected
        if (departmentId === "") {
          document.getElementById('section_id').innerHTML = '<option value="">Select Section</option>';
          document.getElementById('subject-time-list').innerHTML = '<button type="button" class="btn btn-secondary" onclick="addSubjectTime()">Add Subject, Room, and Time</button>';
          return;
        }

        // Fetch sections
        fetch('fetch_sections.php?department_id=' + departmentId)
          .then(response => response.text())
          .then(data => document.getElementById('section_id').innerHTML = data);

        // Fetch subjects (populated dynamically when a new subject row is added)
        fetch('fetch_subjects.php?department_id=' + departmentId)
          .then(response => response.text())
          .then(data => document.getElementById('subject_id').innerHTML = data);
      }

      // Validation to check if a section has duplicate subjects or overlapping times on the same day
      function validateTimetableForm() {
        var subjects = document.getElementsByName('subjects[]');
        var days = document.getElementsByName('days[]');
        var startTimes = document.getElementsByName('start_times[]');
        var endTimes = document.getElementsByName('end_times[]');

        var subjectDayCombination = {}; // Store unique combination of subject + day

        for (let i = 0; i < subjects.length; i++) {
          var subject = subjects[i].value;
          var day = days[i].value;
          var startTime = startTimes[i].value;
          var endTime = endTimes[i].value;

          var combinationKey = subject + "-" + day;

          // Check for duplicate subject on the same day
          if (subjectDayCombination[combinationKey]) {
            alert(`Duplicate subject "${subject}" on the same day.`);
            return false; // Prevent form submission
          }

          subjectDayCombination[combinationKey] = true;

          // Check for overlapping times
          for (let j = i + 1; j < startTimes.length; j++) {
            if (days[j].value === day && (startTimes[j].value < endTime && startTimes[i].value < endTimes[j].value)) {
              alert(`Overlapping times for subject "${subject}" on ${day}.`);
              return false; // Prevent form submission
            }
          }
        }
        return true; // Allow form submission
      }

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

      document.querySelectorAll('.delete-link').forEach(link => {
        link.addEventListener('click', function(event) {
          event.preventDefault();

          const sectionId = this.getAttribute('data-section-id');
          if (!sectionId) {
            alert("Invalid section ID.");
            return;
          }

          if (confirm(`Are you sure you want to delete all timetable entries for Section ID: ${sectionId}?`)) {
            fetch(`manage_timetable.php?delete_timetable_id=${sectionId}`, {
                method: 'GET'
              })
              .then(response => {
                if (!response.ok) {
                  throw new Error("Failed to delete timetable.");
                }
                alert("Entire timetable deleted successfully!");
                location.reload(); // Refresh the page
              })
              .catch(error => {
                console.error("Error deleting timetable:", error);
                alert("An error occurred while trying to delete the timetable.");
              });
          }
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

  </main><!-- End #main -->



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