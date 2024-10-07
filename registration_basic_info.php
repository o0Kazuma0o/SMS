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
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="BSIT">Bachelor of Science in Information Technology</option>
                            <option value="BSIS">Bachelor of Science in Information Systems</option>
                            <option value="CRIM">Criminology</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label for="admissiontype" class="form-label">Admission Type</label>
                          <select class="form-select" id="admissiontype" name="admissiontype" required>
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="Regular">New Regular</option>
                            <option value="Transferee">Transferee</option>
                            <option value="Returnee">Returnee</option>
                          </select>
                          <br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="workingstudent" id="workingstudent"> 
                            Are you a Working Student?
                          </label>
                        </div>
                        <div class="col-md-3">
                          <label for="yrlvl" class="form-label">Year Level</label>
                          <select class="form-select" id="yrlvl" name="yrlvl" required>
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
                          <label for="civilstatus" class="form-label">Civil Status</label>
                          <select class="form-select" id="civilstatus" name="civilstatus" required>
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
                      <!-- Row 4: Birthday, Email Address, Contact Number -->
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
                          <label for="contactnumber" class="form-label">Contact Number</label>
                          <input type="number" class="form-control" id="contactnumber" name="contactnumber" required pattern="[0-9]{10}" placeholder="10-digit phone number">
                        </div>
                        <div class="col-md-3">
                          <label for="facebookmessenger" class="form-label">Facebook Name</label>
                          <input type="text" class="form-control" id="facebookmessenger" name="facebookmessenger" required>
                        </div>
                      </div>
                      <button type="button" class="btn btn-primary" onclick="validateBasicInfo()">Next</button>
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
                      <button type="button" class="btn btn-primary" onclick="validateAddress()">Next</button>
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
                          <input type="text" class="form-control" id="flastname" name="flastname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="ffirstname" class="form-label">Father's First Name</label>
                          <input type="text" class="form-control" id="ffirstname" name="ffirstname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="fmiddlename" class="form-label">Father's Middle Name</label>
                          <input type="text" class="form-control" id="fmiddlename" name="fmiddlename" required>
                        </div>
                      </div>
                      <!-- Row 2: Mother's Name -->
                      <div class="row form-row">
                        <h6>Mother's Maiden Name</h6>
                        <div class="col-md-3">
                          <label for="mlastname" class="form-label">Mother's Last Name</label>
                          <input type="text" class="form-control" id="mlastname" name="mlastname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="mfirstname" class="form-label">Mother's First Name</label>
                          <input type="text" class="form-control" id="mfirstname" name="mfirstname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="mmiddlename" class="form-label">Mother's Middle Name</label>
                          <input type="text" class="form-control" id="mmiddlename" name="mmiddlename" required>
                        </div>
                      </div>
                      <!-- Row 3: Guardian's Name -->
                      <div class="row form-row">
                        <div class="col-md-3">
                          <label for="glastname" class="form-label">Guardian's Last Name</label>
                          <input type="text" class="form-control" id="glastname" name="glastname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="gfirstname" class="form-label">Guardian's First Name</label>
                          <input type="text" class="form-control" id="gfirstname" name="gfirstname" required>
                        </div>
                        <div class="col-md-3">
                          <label for="gmiddlename" class="form-label">Guardian's Middle Name</label>
                          <input type="text" class="form-control" id="gmiddlename" name="gmiddlename" required>
                        </div>
                        <div class="col-md-3">
                          <label for="gcontactnumber" class="form-label">Contact Number</label>
                          <input type="number" class="form-control" id="gcontactnumber" name="gcontactnumber" required pattern="[0-9]{10}" placeholder="10-digit phone number">
                        </div>
                      </div>
                      <!-- Row 3: Guardian's Occupation -->
                      <div class="row form-row">
                        <div class="col-md-6">
                          <label for="occupation" class="form-label">Occupation</label>
                          <input type="text" class="form-control" id="occupation" name="occupation" required>
                          <br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="4ps" id="4ps"> 
                            Parent / Guardian member of 4Ps?
                          </label>
                        </div>
                      </div>
                      <button type="button" class="btn btn-primary" onclick="validateGuardianInfo()">Next</button>
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
                          <input type="text" class="form-control" id="primary" name="primary" required>
                        </div>
                        <div class="col-md-3">
                          <label for="pyear" class="form-label">Year Graduated</label>
                          <input type="number" class="form-control" id="pyear" name="pyear" required>
                        </div>
                      </div>
                      <!-- Row 2: Secondary School -->
                      <div class="row form-row">
                        <div class="col-md-9">
                          <label for="secondary" class="form-label">Secondary</label>
                          <input type="text" class="form-control" id="secondary" name="secondary" required>
                        </div>
                        <div class="col-md-3">
                          <label for="syear" class="form-label">Year Graduated</label>
                          <input type="number" class="form-control" id="syear" name="syear" required>
                        </div>
                      </div>
                      <!-- Row 3: Last School -->
                      <div class="row form-row">
                        <div class="col-md-9">
                          <label for="lschool" class="form-label">Last School Attended</label>
                          <input type="text" class="form-control" id="lschool" name="lschool" required>
                        </div>
                        <div class="col-md-3">
                          <label for="syear" class="form-label">Last School Year Attended</label>
                          <input type="number" class="form-control" id="syear" name="syear" required>
                        </div>
                      </div>
                      <button type="button" class="btn btn-primary" onclick="validateEducation()">Next</button>
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
                            <option value=""></option>
                            <!-- Options here -->
                            <option value="socmed">Social Media</option>
                            <option value="refer">Adviser/Referral/Others</option>
                            <option value="walkin">Walk-in/No Referral</option>
                          </select>
                        </div>
                      </div>
                      <button type="button" class="btn btn-primary" onclick="generateSummary()">Next</button>
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

    function validateBasicInfo() {
      const form = document.getElementById('basic-info-form');
      let valid = true;

      form.querySelectorAll('input').forEach((input) => {
        // Skip validation for "Suffix"
        if (input.id !== 'suffix' && input.hasAttribute('required')) {
          if (!input.value.trim()) {
            input.style.border = '2px solid red';
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      if (valid) {
        // Collect form data
        const formData = new FormData(form);
        formData.forEach((value, key) => { basicInfo[key] = value; });
        // Move to the next accordion and unlock it
        document.getElementById('headingTwo').querySelector('button').disabled = false;
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
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { addressInfo[key] = value; });
        document.getElementById('headingThree').querySelector('button').disabled = false;
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
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { guardianInfo[key] = value; });
        document.getElementById('headingFour').querySelector('button').disabled = false;
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
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { educationInfo[key] = value; });
        document.getElementById('headingFive').querySelector('button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseFive'), {toggle: true});
      } else {
        form.reportValidity();
      }
    }

    function generateSummary() {
      const form = document.getElementById('referral-form');
      let valid = true;

      form.querySelectorAll('input, select').forEach((input) => {
        if (input.hasAttribute('required')) {
          if (!input.value.trim()) {
            input.style.border = '2px solid red';
            valid = false;
          } else {
            input.style.border = '';
          }
        }
      });

      if (valid) {
        const formData = new FormData(form);
        formData.forEach((value, key) => { referralInfo[key] = value; });

        // Display Summary
        let summaryHtml = '<h4>Basic Information</h4><hr>';
        for (const [key, value] of Object.entries(basicInfo)) {
          summaryHtml += `<p><strong>${key}:</strong> ${value}</p>`;
        }

        summaryHtml += '<h4>Address</h4><hr>';
        for (const [key, value] of Object.entries(addressInfo)) {
          summaryHtml += `<p><strong>${key}:</strong> ${value}</p>`;
        }

        summaryHtml += '<h4>Parent/Guardian Information</h4><hr>';
        for (const [key, value] of Object.entries(guardianInfo)) {
          summaryHtml += `<p><strong>${key}:</strong> ${value}</p>`;
        }

        summaryHtml += '<h4>Educational Background</h4><hr>';
        for (const [key, value] of Object.entries(educationInfo)) {
          summaryHtml += `<p><strong>${key}:</strong> ${value}</p>`;
        }

        summaryHtml += '<h4>Referral</h4><hr>';
        for (const [key, value] of Object.entries(referralInfo)) {
          summaryHtml += `<p><strong>${key}:</strong> ${value}</p>`;
        }

        document.getElementById('summary').innerHTML = summaryHtml;
        document.getElementById('headingSix').querySelector('button').disabled = false;
        var nextAccordion = new bootstrap.Collapse(document.getElementById('collapseSix'), {toggle: true});
      } else {
        form.reportValidity();
      }
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