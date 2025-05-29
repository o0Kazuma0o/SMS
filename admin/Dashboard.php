<?php
require('../database.php');
require_once __DIR__ . '/../vendor/autoload.php'; // Adjust path based on your structure
require '../models/Predictivemodel.php';
require_once 'session.php';

checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

$propertyId = '478793835'; // Replace with your GA4 Property ID
$realtimeUsers = getRealtimeUsers($propertyId);
$totalVisitors = getTotalUsers($propertyId);

function initializeAnalytics()
{
  $KEY_FILE_LOCATION = __DIR__ . '/../bcp-analytics-api-13886752cb0a.json'; // Path to JSON key file

  $client = new Google\Client();
  $client->setApplicationName("Analytics Reporting");
  $client->setAuthConfig($KEY_FILE_LOCATION);
  $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
  return $client;
}

function getRealtimeUsers($propertyId)
{
  try {
    $client = initializeAnalytics();
    $service = new Google\Service\AnalyticsData($client);

    $request = new Google\Service\AnalyticsData\RunRealtimeReportRequest([
      'dimensions' => [new Google\Service\AnalyticsData\Dimension(['name' => 'country'])],
      'metrics' => [new Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])]
    ]);

    $response = $service->properties->runRealtimeReport("properties/$propertyId", $request);
    error_log('Realtime RAW Response: ' . print_r($response->toSimpleObject(), true));

    // Handle Realtime response
    if (!empty($response->rows)) {
      $firstRow = $response->rows[0];
      if (!empty($firstRow->metricValues)) {
        return $firstRow->metricValues[0]->value;
      }
    }

    error_log('GA4 Realtime: No active users found');
    return 0;
  } catch (Exception $e) {
    error_log('GA4 Realtime Error: ' . $e->getMessage());
    return 'N/A';
  }
}

function getTotalUsers($propertyId)
{
  try {
    $client = initializeAnalytics();
    $service = new Google\Service\AnalyticsData($client);

    $dateRange = new Google\Service\AnalyticsData\DateRange([
      'start_date' => '2020-01-01',
      'end_date' => 'today'
    ]);

    $request = new Google\Service\AnalyticsData\RunReportRequest([
      'dateRanges' => [$dateRange],
      'metrics' => [new Google\Service\AnalyticsData\Metric(['name' => 'totalUsers'])]
    ]);

    $response = $service->properties->runReport("properties/$propertyId", $request);
    error_log('Total Users RAW Response: ' . print_r($response->toSimpleObject(), true));

    if (!empty($response->rows)) {
      $firstRow = $response->rows[0];
      if (!empty($firstRow->metricValues)) {
        $totalUsers = $firstRow->metricValues[0]->value;
        error_log('GA4 Total Users: Found ' . $totalUsers . ' users');
        return (int)$totalUsers;
      }
    }

    error_log('GA4 Total Users: No users found');
    return 0;
  } catch (Exception $e) {
    error_log('GA4 Total Users Error: ' . $e->getMessage());
    return 'N/A';
  }
}

// Fetch historical admission data for forecasting
$sql_forecast = "
    SELECT 
        YEAR(created_at) AS year,
        MONTH(created_at) AS month,
        COUNT(*) AS admissions
    FROM sms3_admissions_data
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)
";
$result_forecast = $conn->query($sql_forecast);
$historicalData = [];
while ($row = $result_forecast->fetch_assoc()) {
  $historicalData[] = [
    'year' => $row['year'],
    'month' => $row['month'],
    'admissions' => $row['admissions']
  ];
}

// Initialize the predictive model
$model = new SARIMAModel($historicalData, 3);
$forecast = $model->forecast();

// Fetch the total number of pending admissions
$sql = "SELECT COUNT(*) as total_pending FROM sms3_pending_admission WHERE status = 'Pending'";
$result = $conn->query($sql);
$total_pending = 0;

if ($result && $row = $result->fetch_assoc()) {
  $total_pending = $row['total_pending'];
}

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
        d.department_code AS department_code, 
        COUNT(s.id) AS student_count
    FROM sms3_departments d
    LEFT JOIN sms3_students s ON s.department_id = d.id
    GROUP BY d.id
    ORDER BY d.department_code ASC
";
$result_departments = $conn->query($sql_departments);

$department_labels = [];
$student_counts = [];

if ($result_departments) {
  while ($row = $result_departments->fetch_assoc()) {
    $department_labels[] = $row['department_code'] ? $row['department_code'] : "Unassigned";
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
      <!-- End System Nav -->

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

            <!-- Admission Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">

                <div class="card-body">
                  <h5 class="card-title">Admissions <span>| Pending</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_pending; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Pending Admissions</span>
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Admission Card -->

            <!-- Enrollment Card -->
            <div class="col-xxl-4 col-md-6">
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
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                <div class="card-body">
                  <h5 class="card-title">Students <span>| Total</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_students; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Students</span>
                    </div>
                  </div>
                </div>

              </div>

            </div><!-- End Customers Card -->

            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Users</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= is_numeric($realtimeUsers) ? number_format((float)$realtimeUsers) : $realtimeUsers ?></h6>
                      <span class="text-muted small pt-2 ps-1">Visited the website 30 mins. ago</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Users Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <!-- <div class="filter">
                    <select class="form-select" id="timeRangeFilter">
                      <option value="all_time">All Time</option>
                      <option value="today">Today</option>
                      <option value="this_week">This Week</option>
                      <option value="this_month">This Month</option>
                      <option value="this_year">This Year</option>
                    </select>
                  </div> -->
                  <h5 class="card-title">Total Users</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="totalUsersValue"><?= is_numeric($totalVisitors) ? number_format((float)$totalVisitors) : $totalVisitors ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Users Visited The Website</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Generate Report -->
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Generate Report</h5>
                  <form class="row g-3" method="get" action="generate_report.php" target="_blank" style="margin-bottom: 10px;">
                    <div class="col-md-3" id="startDateGroup">
                      <label for="start_date" class="form-label">Start Date</label>
                      <input type="date" class="form-control" id="start_date" name="start_date" required value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-3" id="endDateGroup">
                      <label for="end_date" class="form-label">End Date</label>
                      <input type="date" class="form-control" id="end_date" name="end_date" required value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                      <label for="report_type" class="form-label">Report Type</label>
                      <select class="form-select" id="report_type" name="report_type" required>
                        <option value="range" <?php if (!isset($_GET['report_type']) || $_GET['report_type'] == 'range') echo 'selected'; ?>>Custom Range</option>
                        <option value="daily" <?php if (isset($_GET['report_type']) && $_GET['report_type'] == 'daily') echo 'selected'; ?>>Daily Report</option>
                        <option value="monthly" <?php if (isset($_GET['report_type']) && $_GET['report_type'] == 'monthly') echo 'selected'; ?>>Monthly Report</option>
                      </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                      <button type="submit" class="btn btn-primary w-100">Download PDF Report</button>
                    </div>
                  </form>
                  <script>
                    function toggleDatePickers() {
                      const reportType = document.getElementById('report_type').value;
                      const startDateGroup = document.getElementById('startDateGroup');
                      const endDateGroup = document.getElementById('endDateGroup');
                      if (reportType === 'daily' || reportType === 'monthly') {
                        startDateGroup.style.display = 'none';
                        endDateGroup.style.display = 'none';
                      } else {
                        startDateGroup.style.display = '';
                        endDateGroup.style.display = '';
                      }
                    }
                    document.getElementById('report_type').addEventListener('change', toggleDatePickers);
                    window.addEventListener('DOMContentLoaded', toggleDatePickers);
                  </script>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Backup Data</h5>
                  <a href="backup_data.php" class="btn btn-warning">Backup Now</a>
                </div>
              </div>
            </div>

            <!-- Forecasting -->
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Admission Forecast</h5>

                  <div id="lineChart" style="min-height: 400px;" class="echart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      const historicalData = <?php echo json_encode($historicalData); ?>;
                      const forecast = <?php echo json_encode($forecast); ?>;

                      if (historicalData.length === 0 || forecast.length === 0) {
                        document.getElementById('lineChart').innerHTML = '<div style="text-align: center; padding: 20px;">No data available for forecasting.</div>';
                        return;
                      }

                      // Prepare data for chart
                      const months = historicalData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);
                      const historicalValues = historicalData.map(item => item.admissions);
                      const forecastValues = forecast.map(item => item.predicted_admissions);
                      const forecastMonths = forecast.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);

                      // Create date formatter
                      const formatMonth = (year, month) => {
                        return `${year}-${String(month).padStart(2, '0')}`;
                      };

                      echarts.init(document.querySelector("#lineChart")).setOption({
                        tooltip: {
                          trigger: 'item'
                        },
                        legend: {
                          data: ['Historical', 'Forecast']
                        },
                        xAxis: {
                          type: 'category',
                          data: [...months, ...forecastMonths]
                        },
                        yAxis: {
                          type: 'value'
                        },
                        series: [{
                            name: 'Historical',
                            type: 'line',
                            smooth: true,
                            data: historicalValues
                          },
                          {
                            name: 'Forecast',
                            type: 'line',
                            smooth: true,
                            data: [...Array(months.length).fill(null), ...forecastValues],
                          }
                        ]
                      });

                      // Handle window resize
                      window.addEventListener('resize', () => {
                        chart.resize();
                      });
                    });
                  </script>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Students by Department</h5>

                  <!-- Pie Chart -->
                  <div id="departmentPieChart" style="min-height: 400px;" class="echart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      const departmentCodes = <?php echo json_encode($department_labels); ?>;
                      const studentCounts = <?php echo json_encode($student_counts); ?>;

                      // Initialize echarts instance
                      const chart = echarts.init(document.querySelector("#departmentPieChart"));

                      // Set chart options
                      chart.setOption({
                        tooltip: {
                          trigger: 'item'
                        },
                        legend: {
                          orient: 'vertical',
                          left: 'left'
                        },
                        series: [{
                          name: 'Students',
                          type: 'pie',
                          radius: '50%',
                          data: departmentCodes.map((code, index) => ({
                            value: studentCounts[index],
                            name: code
                          })),
                          emphasis: {
                            itemStyle: {
                              shadowBlur: 10,
                              shadowOffsetX: 0,
                              shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                          }
                        }]
                      });

                      // Handle window resize to maintain chart responsiveness
                      window.addEventListener('resize', () => {
                        chart.resize();
                      });
                    });
                  </script>
                  <!-- End Pie Chart -->

                </div>
              </div>
            </div>

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
                          height: 400,
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

  <script>
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