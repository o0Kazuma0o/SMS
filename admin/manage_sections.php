<?php require('../database.php');
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
  <script>
    $(document).ready(function() {
        // AJAX for toggling semester without page reload
        $('#toggle-semester-btn').click(function(e) {
            e.preventDefault(); // Prevent form submission

            $.post("manage_sections.php", { ajax_toggle_semester: true }, function() {
                location.reload(); // Reload page content after toggling
            });
        });
    });
  </script>

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
      <h1>Section</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Section</li>
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
            <label for="semester">Semester:</label>
              <select class="form-control" name="semester" id="semester" required>
                <option value="1st Semester" <?= isset($edit_section) && $edit_section['semester'] == '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                <option value="2nd Semester" <?= isset($edit_section) && $edit_section['semester'] == '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
              </select>
            </div>
            <div class="form-group mt-2">
              <label for="capacity">Section Capacity:</label>
              <input type="number" class="form-control" name="capacity" id="capacity" required
              value="<?= isset($edit_section) ? $edit_section['capacity'] : ''; ?>" min="1" placeholder="Enter section capacity">
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
        <div class="card-body">
        <h5 class="card-title">Section List</h5>
          <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Section Number</th>
                    <th>Year Level</th>
                    <th>Semester</th>
                    <th>Capacity</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($section = $sections->fetch_assoc()): ?>
                <tr>
                    <td><?= $section['section_number']; ?></td>
                    <td><?= $section['year_level']; ?></td>
                    <td><?= $section['semester']; ?></td>
                    <td><?= $section['capacity']; ?></td>
                    <td><?= $section['department_code']; ?></td>
                    <td>
                        <a href="manage_sections.php?edit_section_id=<?= $section['id']; ?>" 
                        class="btn btn-info btn-sm">Edit</a>
                        <a href="manage_sections.php?delete_section_id=<?= $section['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this section?')">Delete</a>
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