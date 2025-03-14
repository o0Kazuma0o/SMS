<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

// Fetch the total number of pending enrollments
$sql_enrollment = "SELECT COUNT(*) as total_pending_enrollments FROM sms3_pending_enrollment";
$result_enrollment = $conn->query($sql_enrollment);
$total_pending_enrollments = 0;

if ($result_enrollment && $row_enrollment = $result_enrollment->fetch_assoc()) {
  $total_pending_enrollments = $row_enrollment['total_pending_enrollments'];
}

// Fetch the total number of students
$sql_students = "SELECT COUNT(*) as total_students FROM sms3_students";
$result_students = $conn->query($sql_students);
$total_students = 0;

if ($result_students && $row_students = $result_students->fetch_assoc()) {
  $total_students = $row_students['total_students'];
}

// Fetch student count grouped by department
$sql_departments = "
    SELECT 
        d.department_name AS department_name, 
        COUNT(s.id) AS student_count
    FROM sms3_students s
    LEFT JOIN sms3_departments d ON s.department_id = d.id
    GROUP BY s.department_id
";
$result_departments = $conn->query($sql_departments);

$department_labels = [];
$student_counts = [];

if ($result_departments) {
  while ($row = $result_departments->fetch_assoc()) {
    $department_labels[] = $row['department_name'] ? $row['department_name'] : "Unassigned";
    $student_counts[] = $row['student_count'];
  }
}

// Fetch student enrollment status counts
$sql_enrollment_status = "
    SELECT 
        COUNT(CASE WHEN status = 'Enrolled' THEN 1 END) AS enrolled_count,
        COUNT(CASE WHEN status = 'Not Enrolled' THEN 1 END) AS not_enrolled_count
    FROM sms3_students
";
$result_enrollment_status = $conn->query($sql_enrollment_status);

$enrolled_count = 0;
$not_enrolled_count = 0;

if ($result_enrollment_status) {
  $row = $result_enrollment_status->fetch_assoc();
  $enrolled_count = $row['enrolled_count'];
  $not_enrolled_count = $row['not_enrolled_count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard</title>
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
      <!-- End System Nav -->

      <hr class="sidebar-divider">

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Enrollment Card -->
            <div class="col-xxl-6 col-md-6">
              <div class="card info-card revenue-card">

                <!--<div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div> -->

                <div class="card-body">
                  <h5 class="card-title">Enrollments <span>| Pending</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_pending_enrollments; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Pending Enrollments</span>
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Enrollment Card -->

            <!-- Total Students Card -->
            <div class="col-xxl-6 col-xl-12">

              <div class="card info-card customers-card">

                <div class="card-body">
                  <h5 class="card-title">Students <span>| Total</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_students; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Registered Students</span>
                    </div>
                  </div>
                </div>

              </div>

            </div><!-- End Customers Card -->

            <!-- Students by Department -->
            <div class="col-lg-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Students by Department</h5>

                  <!-- Pie Chart -->
                  <div id="departmentPieChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      const departmentLabels = <?php echo json_encode($department_labels); ?>;
                      const studentCounts = <?php echo json_encode($student_counts); ?>;

                      new ApexCharts(document.querySelector("#departmentPieChart"), {
                        series: studentCounts,
                        chart: {
                          height: 350,
                          type: 'pie',
                          toolbar: {
                            show: true
                          }
                        },
                        labels: departmentLabels,
                        title: {
                          text: "Students Distribution by Department",
                          align: "center"
                        },
                      }).render();
                    });
                  </script>
                  <!-- End Pie Chart -->

                </div>
              </div>
            </div>

            <!-- Enrollment Status -->
            <div class="col-lg-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Enrollment Status</h5>

                  <!-- Pie Chart -->
                  <div id="enrollmentPieChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      const statusLabels = ["Enrolled", "Not Enrolled"];
                      const statusCounts = [<?php echo $enrolled_count; ?>, <?php echo $not_enrolled_count; ?>];

                      new ApexCharts(document.querySelector("#enrollmentPieChart"), {
                        series: statusCounts,
                        chart: {
                          height: 350,
                          type: 'pie',
                          toolbar: {
                            show: true
                          }
                        },
                        labels: statusLabels,
                        title: {
                          text: "Students Enrollment Status",
                          align: "center"
                        },
                      }).render();
                    });
                  </script>
                  <!-- End Pie Chart -->

                </div>
              </div>
            </div>

          </div>
        </div><!-- End Left side columns -->


      </div>
    </section>

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