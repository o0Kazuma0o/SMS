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
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <style>
    .title {
        background-color: #1e3a8a;
        color: #6b7280; /* Gray color for the text */
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

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    input[type="number"] {
      appearance: textfield;
    }

    .form-row {
      margin-bottom: 15px;
    }

    .requirements-section {
      margin-top: 20px;
      padding: 15px; /* Adding padding for better spacing */
    }

    .requirements-section h2, .requirements-section h3 {
      color: #1e3a8a;
    }

    .requirements-section hr {
      border-top: 2px solid #1e3a8a;
    }

    .requirements-section ul {
      padding-left: 20px;
    }

    .requirements-section li {
      font-size: 15px;
      color: #6b7280; /* Gray text for list items */
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .requirements-section {
        margin-top: 40px;
        text-align: center; /* Center align content on smaller screens */
      }

      .requirements-section ul {
        list-style-type: none; /* Remove bullet points for mobile screens */
        padding-left: 0; /* Remove padding */
      }

      .requirements-section li {
        margin-bottom: 10px;
        font-size: 14px; /* Slightly reduce font size */
        color: #6b7280; /* Ensure gray color on mobile as well */
      }

      .requirements-section h4 {
        font-size: 15px;
      }
    }
  </style>


</head>

<body>

<?php if (isset($message)): ?>
  <div class="<?php echo $success ? 'alert alert-success' : 'alert alert-danger'; ?>">
    <?php echo $message; ?>
  </div>
<?php endif; ?>

  <main class="main">
    <div class="title text-center mb-1" style="background-color: #1e3a8a; color: white;">
      <!-- Placeholder for the school logo -->
      <img src="assets/img/bcp.png" alt="School Logo" width="75">
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
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" disabled>
                    Basic Information
                  </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" id="basic-info-form" method="POST" class="mb-4">
                      <!-- Row 1: Admission Type and Working Student -->
                      <div class="row form-row">
                        <div class="col-md-6">
                          <label for="program" class="form-label">Program</label>
                          <select class="form-select" id="program" name="program" required>
                            <option value=""disabled selected></option>
                            <!-- Options here -->
                            <option value="BSIT">Bachelor of Science in Information Technology</option>
                            <option value="BSIS">Bachelor of Science in Information Systems</option>
                            <option value="CRIM">Criminology</option>
                          </select>
                          <div class="text-danger" id="program-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="admissiontype" class="form-label">Admission Type</label>
                          <select class="form-select" id="admissiontype" name="admissiontype" required onchange="validateYearLevel()">
                            <option value="" disabled selected></option>
                            <!-- Options here -->
                            <option value="New Regular">New Regular</option>
                            <option value="Transferee">Transferee</option>
                            <option value="Returnee">Returnee</option>
                          </select>
                          <div class="text-danger" id="admissiontype-error"></div>
                          <br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="workingstudent" id="workingstudent" onchange="updateCheckboxValueInfo(this)"> 
                            Are you a Working Student?
                            <input type="hidden" name="workingstudent" id="workingstudent-hidden" value="No">
                          </label>
                        </div>
                        <div class="col-md-3">
                          <label for="yrlvl" class="form-label">Year Level</label>
                          <select class="form-select" id="yrlvl" name="yrlvl" required>
                            <option value="" disabled selected></option>
                            <!-- Options here -->
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                          </select>
                          <div class="text-danger" id="yrlvl-error"></div>
                        </div>
                      </div>
                      <!-- Row 2: Lastname, Firstname, Middlename, Suffix -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="lastname" class="form-label">Lastname</label>
                          <input type="text" class="form-control" id="lastname" name="lastname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="lastname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="firstname" class="form-label">Firstname</label>
                          <input type="text" class="form-control" id="firstname" name="firstname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="firstname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="middlename" class="form-label">Middlename</label>
                          <input type="text" class="form-control" id="middlename" name="middlename" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="middlename-error"></div>
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
                              <option value="" disabled selected></option>
                              <!-- Options here -->
                              <option value="Male">Male</option>
                              <option value="Female">Female</option>
                            </select>
                            <div class="text-danger" id="sex-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="civilstatus" class="form-label">Civil Status</label>
                          <select class="form-select" id="civilstatus" name="civilstatus" required>
                            <option value="" disabled selected></option>
                            <!-- Options here -->
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Separated">Separated</option>
                            <option value="Widowed">Widowed</option>
                          </select>
                          <div class="text-danger" id="civilstatus-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="religion" class="form-label">Religion</label>
                          <input type="text" class="form-control" id="religion" name="religion" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="religion-error"></div>
                        </div>
                      </div>
                      <!-- Row 4: Birthday, Email Address, Contact Number -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="birthday" class="form-label">Birthday</label>
                          <input type="date" class="form-control" id="birthday" name="birthday" required onkeydown="return false">
                          <div class="text-danger" id="birthday-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="email" class="form-label">Email Address</label>
                          <input type="email" class="form-control" id="email" name="email" required>
                          <div class="text-danger" id="email-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="contactnumber" class="form-label">Contact Number</label>
                          <input type="number" class="form-control" id="contactnumber" name="contactnumber" required pattern="[0-9]{10}" placeholder="11-digit phone number" oninput="validateContactNumber(this)">
                          <div class="text-danger" id="contactnumber-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="facebookmessenger" class="form-label">Facebook Name</label>
                          <input type="text" class="form-control" id="facebookmessenger" name="facebookname" required>
                          <div class="text-danger" id="facebookmessenger-error"></div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="validateBasicInfo()">Next</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" disabled>
                    Address
                  </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" id="address-form" method="post" class="mb-4">
                      <!-- Row 1: Address -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="address" class="form-label">Address #</label>
                          <input type="text" class="form-control" id="address" name="address" required>
                          <div class="text-danger" id="address-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="barangay" class="form-label">Barangay</label>
                          <input type="text" class="form-control" id="barangay" name="barangay" required>
                          <div class="text-danger" id="barangay-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="municipality" class="form-label">Municipality/City</label>
                          <input type="text" class="form-control" id="municipality" name="municipality" required>
                          <div class="text-danger" id="municipality-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="region" class="form-label">Region</label>
                          <select class="form-select" id="region" name="region" required>
                            <option value="" disabled selected></option>
                            <!-- Options here -->
                            <option value="NCR">NCR</option>
                            <option value="CAR">CAR</option>
                            <option value="BARMM">BARMM</option>
                            <option value="Region I - Ilocos">Region I - Ilocos</option>
                            <option value="Region II - Cagayan Valley">Region II - Cagayan Valley</option>
                            <option value="Region III - Central Luzon">Region III - Central Luzon</option>
                            <option value="Region IV - A - CALABARZON">Region IV - A - CALABARZON</option>
                            <option value="Region IV - B - MIMAROPA">Region IV - B - MIMAROPA</option>
                            <option value="Region V - Bicol">Region V - Bicol</option>
                            <option value="Region VI - Western Visayas">Region VI - Western Visayas</option>
                            <option value="Region VII - Central Visayas">Region VII - Central Visayas</option>
                            <option value="Region VIII - Southern Visayas">Region VIII - Southern Visayas</option>
                            <option value="Region IX - Zamboanga">Region IX - Zamboanga</option>
                            <option value="Region X - Northern Mindanao">Region X - Northern Mindanao</option>
                            <option value="Region XI - Davao">Region XI - Davao</option>
                            <option value="Region XII - SOCCSKSARGEN">Region XII - SOCCSKSARGEN</option>
                            <option value="Region XIII - Caraga">Region XIII - Caraga</option>
                            <!--Put Limit on list-->
                          </select>
                          <div class="text-danger" id="region-error"></div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="validateAddress()">Next</button>
                      </div>                      
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree" disabled>
                  Parent's/Guardian's Information
                  </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" id="guardian-form" method="post" class="mb-4">
                      <!-- Row 1: Father's Name -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="flastname" class="form-label">Father's Last Name</label>
                          <input type="text" class="form-control" id="flastname" name="father_lastname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="flastname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="ffirstname" class="form-label">Father's First Name</label>
                          <input type="text" class="form-control" id="ffirstname" name="father_firstname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="ffirstname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="fmiddlename" class="form-label">Father's Middle Name</label>
                          <input type="text" class="form-control" id="fmiddlename" name="father_middlename" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="fmiddlename-error"></div>
                        </div>
                      </div>
                      <!-- Row 2: Mother's Name -->
                      <div class="row form-row">
                        <h6>Mother's Maiden Name</h6>
                        <div class="col-md-3">
                          <label for="mlastname" class="form-label">Mother's Last Name</label>
                          <input type="text" class="form-control" id="mlastname" name="mother_lastname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="mlastname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="mfirstname" class="form-label">Mother's First Name</label>
                          <input type="text" class="form-control" id="mfirstname" name="mother_firstname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="mfirstname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="mmiddlename" class="form-label">Mother's Middle Name</label>
                          <input type="text" class="form-control" id="mmiddlename" name="mother_middlename" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="mmiddlename-error"></div>
                        </div>
                      </div>
                      <!-- Row 3: Guardian's Name -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="glastname" class="form-label">Guardian's Last Name</label>
                          <input type="text" class="form-control" id="glastname" name="guardian_lastname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="glastname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="gfirstname" class="form-label">Guardian's First Name</label>
                          <input type="text" class="form-control" id="gfirstname" name="guardian_firstname" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="gfirstname-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="gmiddlename" class="form-label">Guardian's Middle Name</label>
                          <input type="text" class="form-control" id="gmiddlename" name="guardian_middlename" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="gmiddlename-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="gcontactnumber" class="form-label">Contact Number</label>
                          <input type="number" class="form-control" id="gcontactnumber" name="gcontactnumber" required pattern="[0-9]{10}" placeholder="11-digit phone number" oninput="validateContactNumber(this)">
                          <div class="text-danger" id="gcontactnumber-error"></div>
                        </div>
                      </div>
                      <!-- Row 3: Guardian's Occupation -->
                      <div class="row form-row">
                        <div class="col-md-6">
                          <label for="occupation" class="form-label">Occupation</label>
                          <input type="text" class="form-control" id="occupation" name="occupation" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="occupation-error"></div>
                          <br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="member4ps" id="member4ps" onchange="updateCheckboxValue4ps(this)"> 
                            Parent / Guardian member of 4Ps?
                            <input type="hidden" name="member4ps" id="member4ps-hidden" value="No">
                          </label>
                        </div>
                      </div>
                      <div class="d-flex justify-content-end">
                       <button type="button" class="btn btn-primary" onclick="validateGuardianInfo()">Next</button> 
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour" disabled>
                    Educational Background
                  </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" id="education-form" method="post" class="mb-4">
                      <!-- Row 1: Primary School -->
                      <div class="row form-row">
                        <div class="col-md-9">
                          <label for="primary" class="form-label">Primary</label>
                          <input type="text" class="form-control" id="primary" name="primary" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="primary-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="pyear" class="form-label">Year Graduated</label>
                          <input type="number" class="form-control" id="pyear" name="pyear" required oninput="validateYearGraduated(this)">
                          <div class="text-danger" id="pyear-error"></div>
                        </div>
                      </div>
                      <!-- Row 2: Secondary School -->
                      <div class="row form-row">
                        <div class="col-md-9">
                          <label for="secondary" class="form-label">Secondary</label>
                          <input type="text" class="form-control" id="secondary" name="secondary" required oninput="validateLettersOnly(this)">
                          <div class="text-danger" id="secondary-error"></div>
                        </div>
                        <div class="col-md-3">
                          <label for="syear" class="form-label">Year Graduated</label>
                          <input type="number" class="form-control" id="syear" name="syear" required oninput="validateYearGraduated(this)">
                          <div class="text-danger" id="syear-error"></div>
                        </div>
                      </div>
                      <!-- Row 3: Last School -->
                      <div class="row form-row">
                        <div class="col-md-9">
                          <label for="lschool" class="form-label">Last School Attended</label>
                          <input type="text" class="form-control" id="lschool" name="lschool" required oninput="validateLettersOnly(this)">
                        </div>
                        <div class="col-md-3">
                          <label for="syear" class="form-label">Last School Year Attended</label>
                          <input type="number" class="form-control" id="lyear" name="lyear" required oninput="validateYearGraduated(this)">
                          <div class="text-danger" id="lyear-error"></div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="validateEducation()">Next</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive" disabled>
                    How did you hear our school?
                  </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" id="referral-form" method="POST" class="mb-4">
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="how" class="form-label">Options</label>
                          <select class="form-select" id="how" name="how" required>
                            <option value="" disabled selected></option>
                            <!-- Options here -->
                            <option value="Social Media">Social Media</option>
                            <option value="Refferal">Adviser/Referral/Others</option>
                            <option value="Walk-In">Walk-in/No Referral</option>
                          </select>
                          <div class="text-danger" id="how-error"></div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="validateReferral()">Next</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingSix">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix" disabled>
                    Summary
                  </button>
                </h2>
                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#admission">
                  <div class="accordion-body">
                    <form action="registration_basic_info.php" id="summary" method="POST" class="mb-4">
                      <!--Summary of all the information -->
                      
                    </form>
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
            <h3><strong>Requirements</strong></h3>
            <p>Original Copy of the following documents shall be submitted</p>
            
            <hr>
            
            <h5>College New/Freshmen</h5>
            <ul>
                <li>Form 138 (Report Card)</li>
                <li>Form 137</li>
                <li>Certificate of Good Moral</li>
                <li>PSA Authenticated Birth Certificate</li>
                <li>Passport Size ID Picture (White Background, Formal Attire) - 2pcs</li>
                <li>Barangay Clearance</li>
            </ul>

            <hr>

            <h5>College Transferee</h5>
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

  <script>
    const basicInfo = {};
    const addressInfo = {};
    const guardianInfo = {};
    const educationInfo = {};
    const referralInfo = {};

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simple email regex for validation

    // Prevent manual accordion collapsing
    const accordionHeaders = document.querySelectorAll('.accordion-button');
    accordionHeaders.forEach(button => {
      button.addEventListener('click', (event) => {
        event.preventDefault(); // Disable manual toggling of accordions
      });
    });

    // Get today's date
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    
    // Set maxDate to 16 years ago to ensure the user is at least 16 years old
    const maxDate = `${yyyy - 8}-${mm}-${dd}`; // 16 years ago from today
    const minDate = `${yyyy - 100}-${mm}-${dd}`; // 100 years ago (older limit)

    // Set date limits
    document.getElementById('birthday').setAttribute('max', maxDate);
    document.getElementById('birthday').setAttribute('min', minDate);

    function updateCheckboxValueInfo(checkbox) {
      const hiddenInput = document.getElementById('workingstudent-hidden');
      hiddenInput.value = checkbox.checked ? 'Yes' : 'No';
    }

    function updateCheckboxValue4ps(checkbox) {
      const hiddenInput = document.getElementById('member4ps-hidden');
      hiddenInput.value = checkbox.checked ? 'Yes' : 'No';
    }

    function validateYearLevel() {
      const admissionType = document.getElementById('admissiontype').value;
      const yearLevel = document.getElementById('yrlvl');

      if (admissionType === 'New Regular') {
        // Set Year Level to 1st Year and lock it visually
        yearLevel.innerHTML = '<option value="1st" selected>1st Year</option>';
        yearLevel.setAttribute('data-locked', 'true'); // Custom attribute to indicate it's locked
        yearLevel.style.pointerEvents = 'none'; // Disable user interaction
        yearLevel.style.backgroundColor = '#e9ecef'; // Make it appear disabled
      } else {
        // Unlock Year Level and restore all options for other admission types
        yearLevel.removeAttribute('data-locked');
        yearLevel.style.pointerEvents = 'auto';
        yearLevel.style.backgroundColor = '';
        yearLevel.innerHTML = `
          <option value="" disabled selected></option>
          <option value="1st">1st Year</option>
          <option value="2nd">2nd Year</option>
          <option value="3rd">3rd Year</option>
          <option value="4th">4th Year</option>
        `;
      }
    }

    function validateBasicInfo() {
      const form = document.getElementById('basic-info-form');
      let valid = true;

      document.querySelectorAll('.text-danger').forEach((error) => error.innerText = '');

      form.querySelectorAll('input').forEach((input) => {
        // Skip validation for "Suffix"
        if (input.id !== 'suffix' && input.hasAttribute('required')) {
          if (!input.value.trim()) {
            input.style.border = '2px solid red';
            document.getElementById(input.id + '-error').innerText = 'This field is required';
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      // Validate select fields
      form.querySelectorAll('select[required]').forEach((select) => {
        if (select.value === '') {
          select.style.border = '2px solid red';
          document.getElementById(select.id + '-error').innerText = 'This field is required';
          valid = false;
        } else {
          select.style.border = '';
        }
      });

      // Validate email format
      const email = document.getElementById('email');
      if (email.value && !emailRegex.test(email.value)) {
        document.getElementById('email-error').innerText = 'Please enter a valid email address';
        email.style.border = '2px solid red';
        valid = false;
      } else {
        email.style.border = '';
      }

      // Validate contact number (must be exactly 11 digits)
      const contactnumber = document.getElementById('contactnumber');
      if (contactnumber.value.length !== 11 || !/^\d{11}$/.test(contactnumber.value)) {
        document.getElementById('contactnumber-error').innerText = 'Contact number must be exactly 11 digits';
        contactnumber.style.border = '2px solid red';
        valid = false;
      } else {
        contactnumber.style.border = '';
      }

      const birthday = document.getElementById('birthday');
      const selectedDate = new Date(birthday.value);
      if (!birthday.value) {
        document.getElementById('birthday-error').innerText = 'Please select a valid date';
        birthday.style.border = '2px solid red';
        valid = false;
      } else if (selectedDate > new Date(maxDate)) {
        document.getElementById('birthday-error').innerText = 'You must be at least 16 years old';
        birthday.style.border = '2px solid red';
        valid = false;
      } else if (selectedDate < new Date(minDate)) {
        document.getElementById('birthday-error').innerText = 'Please select a more recent date';
        birthday.style.border = '2px solid red';
        valid = false;
      } else {
        birthday.style.border = '';
      }

      updateCheckboxValueInfo(document.getElementById('workingstudent'));

      if (valid) {
        // Collect form data
        const formData = new FormData(form);
        formData.forEach((value, key) => { basicInfo[key] = value; });
        // Move to the next accordion and unlock it
        document.querySelector('#headingOne button').disabled = false;
        document.querySelector('#headingTwo button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseTwo'), {toggle: true});
      } else {
        form.reportValidity();
      }
    }

    function validateAddress() {
      const form = document.getElementById('address-form');
      let valid = true;

      form.querySelectorAll('input').forEach((input) => {
        if (input.hasAttribute('required')) {
          if (!input.value.trim()) {
            input.style.border = '2px solid red';
            document.getElementById(input.id + '-error').innerText = 'This field is required';
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      // Validate select fields
      form.querySelectorAll('select[required]').forEach((select) => {
        if (select.value === '') {
          select.style.border = '2px solid red';
          document.getElementById(select.id + '-error').innerText = 'This field is required';
          valid = false;
        } else {
          select.style.border = '';
        }
      });

      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { addressInfo[key] = value; });
        document.querySelector('#headingThree button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseThree'), {toggle: true});
      } else {
        form.reportValidity();
      }
    }

    function validateGuardianInfo() {
      const form = document.getElementById('guardian-form');
      let valid = true;

      form.querySelectorAll('input').forEach((input) => {
        if (input.hasAttribute('required')) {
          if (!input.value.trim()) {
            input.style.border = '2px solid red';
            document.getElementById(input.id + '-error').innerText = 'This field is required';
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      // Validate select fields
      form.querySelectorAll('select[required]').forEach((select) => {
        if (select.value === '') {
          select.style.border = '2px solid red';
          document.getElementById(select.id + '-error').innerText = 'This field is required';
          valid = false;
        } else {
          select.style.border = '';
        }
      });

      updateCheckboxValue4ps(document.getElementById('member4ps'));

      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { guardianInfo[key] = value; });
        document.querySelector('#headingFour button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseFour'), {toggle: true});
      } else {
        form.reportValidity();
      }
    }

    function validateEducation() {
      const form = document.getElementById('education-form');
      let valid = true;

      form.querySelectorAll('input').forEach((input) => {
        if (input.hasAttribute('required')) {
          if (!input.value.trim()) {
            input.style.border = '2px solid red';
            document.getElementById(input.id + '-error').innerText = 'This field is required';
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      // Validate select fields
      form.querySelectorAll('select[required]').forEach((select) => {
        if (select.value === '') {
          select.style.border = '2px solid red';
          document.getElementById(select.id + '-error').innerText = 'This field is required';
          valid = false;
        } else {
          select.style.border = '';
        }
      });

    // Validate year graduated fields (pyear, syear, lyear)
    ['pyear', 'syear', 'lyear'].forEach((yearId) => {
      const yearInput = document.getElementById(yearId);
      const yearValue = parseInt(yearInput.value);
      const currentYear = new Date().getFullYear();
      
      if (yearInput.value.length !== 4 || isNaN(yearValue) || yearValue < currentYear - 100 || yearValue > currentYear) {
        document.getElementById(yearId + '-error').innerText = 'Please enter a valid 4-digit year not exceeding 100 years ago';
        yearInput.style.border = '2px solid red';
        valid = false;
      } else {
        yearInput.style.border = '';
        document.getElementById(yearId + '-error').innerText = ''; // Clear error
      }
    });


      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { educationInfo[key] = value; });
        document.querySelector('#headingFive button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseFive'), {toggle: true});
      } else {
        form.reportValidity();
      }
    }

    function validateReferral() {
      const form = document.getElementById('referral-form');
      let valid = true;

      // Validate select fields in the referral form
      form.querySelectorAll('select[required]').forEach((select) => {
        if (select.value === '') {
          select.style.border = '2px solid red';
          document.getElementById(select.id + '-error').innerText = 'This field is required';
          valid = false;
        } else {
          select.style.border = '';
          document.getElementById(select.id + '-error').innerText = ''; // Clear error message
        }
      });

      if (valid) {
        // Collect the referral info
        const formData = new FormData(form);
        formData.forEach((value, key) => { referralInfo[key] = value; });
        
        // Move to the summary accordion and enable it
        document.getElementById('headingSix').querySelector('button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseSix'), {toggle: true});
        
        // Generate the summary now that all sections are validated
        generateSummary();
      } else {
        form.reportValidity();
      }
    }

    function generateSummary() {
    const summaryElement = document.getElementById('summary');
    let summaryHtml = '';

    // Combine Last Name, First Name, and Middle Name
    const fullName = `${basicInfo.firstname} ${basicInfo.middlename} ${basicInfo.lastname}` + (basicInfo.suffix ? ` ${basicInfo.suffix}` : '');

    const fullAddress = `${addressInfo.address}, ${addressInfo.barangay}, ${addressInfo.municipality} - ${addressInfo.region}`;

    // Combine Parent and Guardian Names
    const fatherFullName = `${guardianInfo.father_lastname}, ${guardianInfo.father_firstname} ${guardianInfo.father_middlename}`;
    const motherFullName = `${guardianInfo.mother_lastname}, ${guardianInfo.mother_firstname} ${guardianInfo.mother_middlename}`;
    const guardianFullName = `${guardianInfo.guardian_lastname}, ${guardianInfo.guardian_firstname} ${guardianInfo.guardian_middlename}`;

    // Basic Information Section in Two Columns
    summaryHtml += `
      <h4>Basic Information</h4>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Full Name:</strong> ${fullName}</p>
          <p><strong>Sex:</strong> ${basicInfo.sex}</p>
          <p><strong>Birthday:</strong> ${basicInfo.birthday}</p>
          <p><strong>Contact Number:</strong> ${basicInfo.contactnumber}</p>
          <p><strong>Email:</strong> ${basicInfo.email}</p>
          <p><strong>Address:</strong> ${fullAddress}</p>
          <p><strong>Facebook Name:</strong> ${basicInfo.facebookname}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Admission Type:</strong> ${basicInfo.admissiontype}</p>
          <p><strong>Program:</strong> ${basicInfo.program}</p>
          <p><strong>Year Level:</strong> ${basicInfo.yrlvl}</p>
          <p><strong>Working Student:</strong> ${basicInfo.workingstudent}</p>
          <p><strong>Civil Status:</strong> ${basicInfo.civilstatus}</p>
          <p><strong>Religion:</strong> ${basicInfo.religion}</p>
        </div>
      </div>
      <hr>
    `;

    // Parent/Guardian Information in Two Columns
    summaryHtml += `
      <h4>Parent/Guardian Information</h4>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Father's Full Name:</strong> ${fatherFullName}</p>
          <p><strong>Mother's Full Name:</strong> ${motherFullName}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Guardian's Full Name:</strong> ${guardianFullName}</p>
          <p><strong>Guardian's Occupation:</strong> ${guardianInfo.occupation}</p>
          <p><strong>Guardian's Contact Number:</strong> ${guardianInfo.gcontactnumber}</p>
          <p><strong>Guardian's member of 4ps:</strong> ${guardianInfo.member4ps}</p>
        </div>
      </div>
      <hr>
    `;

    // Educational Background in Two Columns
    summaryHtml += `
      <h4>Educational Background</h4>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Last School Attended:</strong> ${educationInfo.lschool}</p>
          <p><strong>Last School Year Attended:</strong> ${educationInfo.syear}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Primary School Attended:</strong> ${educationInfo.primary}</p>
          <p><strong>Year Graduated:</strong> ${educationInfo.pyear}</p>
          <p><strong>Secondary School Attended:</strong> ${educationInfo.secondary}</p>
          <p><strong>Year Graduated:</strong> ${educationInfo.syear}</p>
        </div>
      </div>
      <hr>
    `;

    // Referral Section
    summaryHtml += `
      <h4>Referral</h4>
      <p><strong>How did you hear about our school?</strong> ${referralInfo.how}</p>
      <hr>
    `;

    // Add the Submit button
    summaryHtml += `
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-success" onclick="confirmAndSubmitData()">Submit</button>
      </div>
    `;

    // Display the generated HTML inside the summary element
    summaryElement.innerHTML = summaryHtml;
  }

    // Restrict text fields (names) to letters, spaces, apostrophes, and hyphens only
    function validateLettersOnly(input) {
      input.value = input.value.replace(/[^a-zA-Z\s'-]/g, ''); // Allow only letters, spaces, apostrophes, and hyphens
    }

    // Restrict contact number to digits only and validate it has exactly 11 digits
    function validateContactNumber(input) {
      input.value = input.value.replace(/\D/g, ''); // Allow only numbers

      if (input.value.length > 11) {
        input.value = input.value.slice(0, 11);
      }
    }

    // Restrict year graduated to 4 digits only and check for range
    function validateYearGraduated(input) {
      input.value = input.value.replace(/\D/g, ''); // Allow only numbers
      if (input.value.length > 4) {
        input.value = input.value.slice(0, 4); // Limit input to 4 digits
      }
    }

    function confirmAndSubmitData() {
      // Create a custom confirmation dialog
      const confirmationDialog = document.createElement('div');
      confirmationDialog.className = 'confirmation-dialog';
      
      confirmationDialog.innerHTML = `
      <div class="d-flex justify-content-center"></div>
        <div class="confirmation-dialog-content">
          <p>Are you sure you want to submit your application?</p>
          <button class="btn btn-success" id="confirmSubmit">Yes, Submit</button>
          <button class="btn btn-danger" id="cancelSubmit">Cancel</button>
        </div>
      </div>
      `;

      // Style the confirmation dialog
      confirmationDialog.style.position = 'fixed';
      confirmationDialog.style.top = '50%';
      confirmationDialog.style.left = '50%';
      confirmationDialog.style.transform = 'translate(-50%, -50%)';
      confirmationDialog.style.backgroundColor = '#fff';
      confirmationDialog.style.border = '1px solid #ccc';
      confirmationDialog.style.padding = '20px';
      confirmationDialog.style.zIndex = '1001';
      confirmationDialog.style.borderRadius = '8px';
      confirmationDialog.style.boxShadow = '0px 0px 10px rgba(0, 0, 0, 0.5)';

      // Overlay to dim the background
      const overlay = document.createElement('div');
      overlay.className = 'overlay';
      overlay.style.position = 'fixed';
      overlay.style.top = '0';
      overlay.style.left = '0';
      overlay.style.width = '100%';
      overlay.style.height = '100%';
      overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
      overlay.style.zIndex = '1000';

      // Append the dialog and overlay to the document body
      document.body.appendChild(overlay);
      document.body.appendChild(confirmationDialog);

      // Handle confirmation button click
      document.getElementById('confirmSubmit').addEventListener('click', () => {
        submitDataToDatabase();
        closeConfirmationDialog();
      });

      // Handle cancel button click
      document.getElementById('cancelSubmit').addEventListener('click', () => {
        closeConfirmationDialog();
      });

      // Function to close the confirmation dialog
      function closeConfirmationDialog() {
        document.body.removeChild(confirmationDialog);
        document.body.removeChild(overlay);
      }
    }

  function submitDataToDatabase() {
    // Collect all data from the form objects
    const data = {
      basicInfo: basicInfo,
      addressInfo: addressInfo,
      guardianInfo: guardianInfo,
      educationInfo: educationInfo,
      referralInfo: referralInfo
    };

    // Function to show a custom popup notification
    function showPopupMessage(message, type = 'success') {
      // Create the popup element
      const popup = document.createElement('div');
      popup.className = `popup-message ${type}`;
      popup.innerText = message;

      // Style the popup element
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

      // Add the popup to the document
      document.body.appendChild(popup);

      // Fade out after 3 seconds
      setTimeout(() => {
        popup.style.opacity = '0';
        // Remove the element after the transition ends
        setTimeout(() => {
          popup.remove();
        }, 500);
      }, 3000);
    }

    // Send an AJAX request to save the data to the database
    fetch('database_admission.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        showPopupMessage('Application submitted successfully!', 'success');
        // Optionally, redirect or reset the form here
      } else {
        showPopupMessage('Failed to submit the application. Please try again.', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showPopupMessage('An error occurred while submitting the application.', 'error');
    });
  }

  </script>

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