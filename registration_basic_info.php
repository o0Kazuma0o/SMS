<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - Title</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="https://elc-public-images.s3.ap-southeast-1.amazonaws.com/bcp-olp-logo-mini2.png" rel="icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <style>
    .title {
        background-color: #1e3a8a;
        color: white;
        padding: 20px;
        margin-bottom: 2rem;
        text-align: center;
    }

    .requirements {
        text-align: center;
    }

    .register {
        padding: 1rem;
        margin-bottom: 20px;
    }

    .form-row {
      margin-bottom: 15px;
    }
  </style>


</head>

<body>

  <main class="main">
    <div class="title text-center mb-1" style="background-color: #1e3a8a; color: white;">
      <!-- Placeholder for the school logo -->
      <img src="path/to/school-logo.png" alt="School Logo" width="200">
      <h5>Bestlink College of the Philippines Enrollment Management System</h5>
      <h1>College Admission</h1>
    </div>
    <div class="row d-flex justify-content-center">
      <div class="card register col-lg-9 mt-1">
        <div class="card">
          <div class="card-body">
            <div class="accordion accordion-flush" id="admission">
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Basic Information
                  </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" method="POST" class="mb-4">
                      <!-- Row 1: Admission Type and Working Student -->
                      <div class="row form-row">
                        <div class="col-md-6">
                          <label for="program" class="form-label">Program</label>
                          <select class="form-select" id="program" name="program" required>
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="BSIT">Bachelor of Science in Information Technology</option>
                            <option value="BSIS">Bachelor of Science in Information Systems</option>
                            <option value="CRIM">Criminology</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label for="admission_type" class="form-label">Admission Type</label>
                          <select class="form-select" id="admission_type" name="admission_type" required>
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="Regular">New Regular</option>
                            <option value="Transferee">Transferee</option>
                            <option value="Returnee">Returnee</option>
                          </select>
                          <br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="working_student" id="working_student"> 
                            Are you a Working Student?
                          </label>
                        </div>
                        <div class="col-md-3">
                          <label for="yr_lvl" class="form-label">Year Level</label>
                          <select class="form-select" id="yr_lvl" name="yr_lvl" required>
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                          </select>
                        </div>
                      </div>
                      <!-- Row 2: Lastname, Firstname, Middlename, Suffix -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="lastname" class="form-label">Lastname</label>
                          <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="firstname" class="form-label">Firstname</label>
                          <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="middlename" class="form-label">Middlename</label>
                          <input type="text" class="form-control" id="middlename" name="middlename" required>
                        </div>
                        <div class="col-md-3">
                          <label for="suffix" class="form-label">Suffix</label>
                          <input type="text" class="form-control" id="suffix" name="suffix">
                        </div>
                      </div>
                      <!-- Row 3: Sex, Civil Status, Religion -->
                      <div class="row form-row">
                        <div class="col-md-3">
                            <label for="sex" class="form-label">Sex</label>
                            <select class="form-select" id="sex" name="sex" required>
                              <option value=""></option>
                              <!-- Options here -->
                              <option value="Male">Male</option>
                              <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                          <label for="civil_status" class="form-label">Civil Status</label>
                          <select class="form-select" id="civil_status" name="civil_status" required>
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Separated">Separated</option>
                            <option value="Widowed">Widowed</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label for="religion" class="form-label">Religion</label>
                          <input type="text" class="form-control" id="religion" name="religion" required>
                        </div>
                      </div>
                      <!-- Row 5: Birthday, Email Address, Contact Number -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="birthday" class="form-label">Birthday</label>
                          <input type="date" class="form-control" id="birthday" name="birthday" required>
                        </div>
                        <div class="col-md-3">
                          <label for="email" class="form-label">Email Address</label>
                          <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-3">
                          <label for="contact_number" class="form-label">Contact Number</label>
                          <input type="number" class="form-control" id="contact_number" name="contact_number" required pattern="[0-9]{10}" placeholder="10-digit phone number">
                        </div>
                        <div class="col-md-3">
                          <label for="facebook_messenger" class="form-label">Facebook Name</label>
                          <input type="text" class="form-control" id="facebook_messenger" name="facebook_messenger" required>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Address
                  </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#admission">
                  <div class="accordion-body">
                  <form action="registration_basic_info.php" method="post" class="mb-4">
                      <!-- Row 1: Address -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="address" class="form-label">Address #</label>
                          <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="col-md-3">
                          <label for="barangay" class="form-label">Barangay</label>
                          <input type="text" class="form-control" id="barangay" name="barangay" required>
                        </div>
                        <div class="col-md-3">
                          <label for="municipality" class="form-label">Municipality/City</label>
                          <input type="text" class="form-control" id="municipality" name="municipality" required>
                        </div>
                        <div class="col-md-3">
                          <label for="region" class="form-label">Region</label>
                          <select class="form-select" id="region" name="region" required>
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="NCR">NCR</option>
                            <option value="CAR">CAR</option>
                            <option value="BARMM">BARMM</option>
                            <option value="1">Region I - Ilocos</option>
                            <option value="2">Region II - Cagayan Valley</option>
                            <option value="3">Region III - Central Luzon</option>
                            <option value="4a">Region IV - A - CALABARZON</option>
                            <option value="4b">Region IV - B - MIMAROPA</option>
                            <option value="5">Region V - Bicol</option>
                            <option value="6">Region VI - Western Visayas</option>
                            <option value="7">Region VII - Central Visayas </option>
                            <option value="8">Region VIII - Southern Visayas</option>
                            <option value="9">Region IX - Zamboanga</option>
                            <option value="10">Region X - Northern Mindanao</option>
                            <option value="11">Region XI - Davao</option>
                            <option value="12">Region XII - SOCCSKSARGEN</option>
                            <option value="13">Region XIII - Caraga</option>
                            <!--Put Limit on list-->
                          </select>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Other Information
                  </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <strong>This section can be used to collect additional information.</strong>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Other Information
                  </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <strong>This section can be used to collect additional information.</strong>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    Other Information
                  </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <strong>This section can be used to collect additional information.</strong>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingSix">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    Other Information
                  </button>
                </h2>
                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <strong>This section can be used to collect additional information.</strong>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingSeven">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                    Other Information
                  </button>
                </h2>
                <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <strong>This section can be used to collect additional information.</strong>
                  </div>
                </div>
              </div>
            </div><!-- End Default Accordion Example -->
          </div>
        </div>
      </div> 
      <div class="card register col-lg-3 mt-1">
        <div class="card requirements">
          <div class="requirements-section">
            <h2>College</h2>
            <h3>College Requirements</h3>
            <p>Original Copy of the following documents shall be submitted to your respective branch:</p>
            
            <hr>
            
            <h4>College New/Freshmen</h4>
            <ul>
                <li>Form 138 (Report Card)</li>
                <li>Form 137</li>
                <li>Certificate of Good Moral</li>
                <li>PSA Authenticated Birth Certificate</li>
                <li>Passport Size ID Picture (White Background, Formal Attire) - 2pcs</li>
                <li>Barangay Clearance</li>
            </ul>

            <hr>

            <h4>College Transferee</h4>
            <ul>
                <li>Transcript of Records from Previous School</li>
                <li>Honorable Dismissal</li>
                <li>Certificate of Good Moral</li>
                <li>PSA Authenticated Birth Certificate</li>
                <li>Passport Size ID Picture (White Background, Formal Attire) - 2pcs</li>
                <li>Barangay Clearance</li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  </main>
  </><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>XXXXXX</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      BCP
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>