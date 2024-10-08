<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admission</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="https://elc-public-images.s3.ap-southeast-1.amazonaws.com/bcp-olp-logo-mini2.png" rel="icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="/SMS/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/SMS/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="/SMS/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="/SMS/assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="/SMS/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="/SMS/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="/SMS/assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="/SMS/assets/css/style.css" rel="stylesheet">

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
            <img src="/SMS/assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
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
      </li><!-- End System Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#enrollment-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Enrolled BSIT</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="enrollment-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="enroll1.php">
              <i class="bi bi-circle"></i><span>1st Year</span>
            </a>
          </li>
          <li>
            <a href="modules.html">
              <i class="bi bi-circle"></i><span>2nd Year</span>
            </a>
          </li>
          <li>
            <a href="modules.html">
              <i class="bi bi-circle"></i><span>3rd Year</span>
            </a>
          </li>
          <li>
            <a href="modules.html">
              <i class="bi bi-circle"></i><span>4th Year</span>
            </a>
          </li>
        </ul>
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
      <h1>Admission</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Adashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Admission</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Basic Information</h5>
              <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <!-- Table with stripped rows -->
                <table style="width: 100%; min-width: 800px;" class="table datatable">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th data-type="date" data-format="YYYY/DD/MM">Birthdate</th>
                      <th>Sex</th>
                      <th>Department</th>
                      <th>Year Level</th>
                      <th>Civil Status</th>
                      <th>Email</th>
                      <th>Contact #</th>
                      <th>Working Student</th>
                      <th>Address</th>
                      <th>Religion</th>
                      <th>Status</th>

                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Unity Pugh</td>
                      <td>2005/02/11</td>
                      <td>Male</td>
                      <td>BSIT</td>
                      <td>1st Year</td>
                      <td>Single</td>
                      <td>malupet@gmail.com</td>
                      <td>09123456789</td>
                      <td>Yes</td>
                      <td>Brgy. Gaya-Gaya, San Jose del Monte, Bulacan</td>
                      <td>Christian</td>
                      <td><span class="badge bg-warning">Pending</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- End Table with stripped rows -->
            </div>
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
  <script src="/SMS/assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="/SMS/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="/SMS/assets/vendor/chart.js/chart.umd.js"></script>
  <script src="/SMS/assets/vendor/echarts/echarts.min.js"></script>
  <script src="/SMS/assets/vendor/quill/quill.js"></script>
  <script src="/SMS/assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="/SMS/assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="/SMS/assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="/SMS/assets/js/main.js"></script>

</body>

</html>