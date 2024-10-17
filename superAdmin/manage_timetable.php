<?php require('../database.php');
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
      </li>

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
      <h1>Timetable</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Timetable</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <script>
    window.onload = function() {
      <?php if (isset($_SESSION['error_message'])): ?>
        alert('<?= $_SESSION['error_message']; ?>');
        <?php unset($_SESSION['error_message']); ?>
      <?php elseif (isset($_SESSION['success_message'])): ?>
        alert('<?= $_SESSION['success_message']; ?>');
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
    };
    </script>


    <section class="section dashboard">
    <div class="row">

      <div class="card">
        <div class="card-body">
        <h5 class="card-title">
          <?php if (isset($_GET['edit_timetable_id'])): ?>
          Edit Timetable
        <?php else: ?>
          Add Timetable
        <?php endif; ?>
        </h5>
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

            <!-- Select Multiple Rooms, Subjects and Times -->
            <div class="form-group mt-3">
              <label for="subjects">Select Subjects and Time:</label>
              <div id="subject-time-list">
                <!-- Room,Subject and Time fields will be dynamically added here -->
                <button type="button" class="btn btn-secondary mt-2" onclick="addSubjectTime()">Add Room, Subject and Time</button>
                <button type="button" class="btn btn-danger mt-2" id="clear-btn" onclick="clearLastSubjectTime()">Clear Last Entry</button>
              </div>
            </div>

            <button type="submit" name="create_timetable" class="btn btn-primary mt-3">Create Timetable</button>
          </form>

        </div>
      </div>

      <div class="card">
        <div class="card-body">
        <h5 class="card-title">List of Timetables</h5>
        <table class="table table-bordered">
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
                $sql = "SELECT t.id, d.department_code, sec.section_number,
                                GROUP_CONCAT(s.subject_code SEPARATOR ', ') as subjects,
                                GROUP_CONCAT(t.day_of_week SEPARATOR ', ') as days,
                                GROUP_CONCAT(t.start_time SEPARATOR ', ') as start_times,
                                GROUP_CONCAT(t.end_time SEPARATOR ', ') as end_times
                        FROM sms3_timetable t
                        JOIN sms3_subjects s ON t.subject_id = s.id
                        JOIN sms3_sections sec ON t.section_id = sec.id
                        JOIN sms3_rooms r ON t.room_id = r.id
                        JOIN sms3_departments d ON sec.department_id = d.id
                        GROUP BY d.department_code, sec.section_number, r.room_name";
                $timetables = $conn->query($sql);

                // Loop through grouped timetables
                while ($timetable = $timetables->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $timetable['department_code']; ?></td>
                    <td><?= $timetable['section_number']; ?></td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewTimetableDetails(<?= $timetable['id']; ?>)">View Timetable</button>
                    </td>
                    <td>
                    <a href="manage_timetable.php?delete_timetable_id=<?= $timetable['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete the entire timetable?')">Delete</a>
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
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Timetable Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <div class="modal-body">
              <table class="table table-bordered" id="timetable-details">
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
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Editing Timetable -->
    <div class="modal fade" id="editTimetableModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Timetable</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="editTimetableForm" action="manage_timetable.php" method="POST" onsubmit="return validateTimetableForm();">
              <!-- Hidden inputs to pass timetable and section IDs -->
              <input type="hidden" name="timetable_id" id="editTimetableId">

              <!-- Subject -->
              <div class="form-group">
                <label for="editSubject">Subject:</label>
                <input type="text" class="form-control" name="subject" id="editSubject" required>
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
                <input type="text" class="form-control" name="room" id="editRoom" required>
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

  // Fetch and display timetable details in a modal
  function viewTimetableDetails(timetableId) {
    fetch('fetch_timetable_details.php?timetable_id=' + timetableId)
      .then(response => response.json())
      .then(data => {
        var timetableDetails = document.getElementById('timetable-details').getElementsByTagName('tbody')[0];
        timetableDetails.innerHTML = ''; // Clear any previous content

        // Loop through the fetched data and add rows to the table
        data.forEach(row => {
          var newRow = `
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

        // Show the "View Timetable" modal
        var timetableModal = new bootstrap.Modal(document.getElementById('timetableModal'));
        timetableModal.show();
      });
}

  // Delete individual timetable row
  function deleteTimetableRow(rowId) {
    if (confirm("Are you sure you want to delete this timetable entry?")) {
      window.location.href = 'manage_timetable.php?delete_row_id=' + rowId;
    }
  }

  // Edit timetable details
  function editTimetable(timetableDetailId) {
    // Fetch the details of the timetable you want to edit
    fetch('fetch_single_timetable_detail.php?id=' + timetableDetailId)
      .then(response => response.json())
      .then(data => {
        // Populate the form with the existing timetable data
        document.getElementById('editTimetableId').value = data.id;
        document.getElementById('editSubject').value = data.subject_code;
        document.getElementById('editDay').value = data.day_of_week;
        document.getElementById('editRoom').value = data.room_name;
        document.getElementById('editStartTime').value = data.start_time;
        document.getElementById('editEndTime').value = data.end_time;

        // Hide the "View Timetable" modal
        var timetableModal = bootstrap.Modal.getInstance(document.getElementById('timetableModal'));
        timetableModal.hide();

        // Show the "Edit Timetable" modal
        var editTimetableModal = new bootstrap.Modal(document.getElementById('editTimetableModal'));
        editTimetableModal.show();
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

  // Add Subject, Room, Day, and Time dynamically
  function addSubjectTime() {
    var subjectTimeList = document.getElementById('subject-time-list');
    var departmentId = document.getElementById('department_id').value;

    if (!document.getElementById('section_id').value) {
        alert("Please select a department and section first.");
        return;
    }

    fetch('fetch_subjects.php?department_id=' + departmentId)
        .then(response => response.text())
        .then(subjectOptions => {
            fetch('fetch_rooms.php?department_id=' + departmentId)
            .then(response => response.text())
            .then(roomOptions => {
                var newEntry = `
                    <div class="form-group mt-2 subject-time-entry">
                        <label for="subject_id">Subject:</label>
                        <select class="form-control" name="subjects[]" required>
                          ${subjectOptions}
                        </select>
                        <label for="room_id" class="mt-2">Room:</label>
                        <select class="form-control" name="rooms[]" required>
                          ${roomOptions}
                        </select>
                        <label for="days[]" class="mt-2">Day:</label>
                        <select class="form-control mt-2" name="days[]" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                        <label for="start_times[]" class="mt-2">Start Time:</label>
                        <input type="time" class="form-control" name="start_times[]" required>
                        <label for="end_times[]" class="mt-2">End Time:</label>
                        <input type="time" class="form-control" name="end_times[]" required>
                    </div>
                    <br>
                `;
                subjectTimeList.insertAdjacentHTML('beforeend', newEntry);
            });
        })
        .catch(error => console.error('Error fetching subjects or rooms:', error));
}

  // Clear the last added subject, room, day, and time entry
  function clearLastSubjectTime() {
    var subjectTimeList = document.getElementById('subject-time-list');
    var entries = subjectTimeList.getElementsByClassName('subject-time-entry');

    if (entries.length > 0) {
        subjectTimeList.removeChild(entries[entries.length - 1]); // Remove the last added field group
    }
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
  </script>

  </main><!-- End #main -->

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