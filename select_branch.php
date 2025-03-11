<?php
session_start();

unset($_SESSION['selected_branch']);

require('database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['selected_branch'] = $_POST['branch'];
  header('Location: admission.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Select Branch - Bestlink College</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .branch-card {
      transition: all 0.3s ease;
      cursor: pointer;
      border: 2px solid transparent;
      overflow: hidden;
    }

    .branch-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .branch-card.selected {
      border-color: #1e3a8a;
      background-color: #f8f9fa;
    }

    .branch-image {
      height: 250px;
      overflow: hidden;
      position: relative;
    }

    .branch-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .branch-card:hover .branch-image img {
      transform: scale(1.05);
    }

    .branch-content {
      padding: 20px;
    }

    .branch-address {
      font-size: 0.9rem;
      color: #6c757d;
    }

    body {
      overflow-x: hidden;
      position: relative;
      background-color: #f8f9fa;
    }

    .title {
      background-color: #1e3a8a;
      color: white;
      padding: 20px;
      margin-bottom: 2rem;
      text-align: center;
      border-radius: 15px 15px 0 0;
      position: relative;
      z-index: 1;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .proceed-btn {
      background-color: #1e3a8a;
      color: white;
      padding: 12px 30px;
      font-size: 1.1rem;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .proceed-btn:hover {
      background-color: #15306d;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .branch-image {
        height: 200px;
      }

      .title h2 {
        font-size: 1.5rem;
      }
    }

    .error-message {
      display: none;
      /* Hidden by default */
      background-color: #ffebee;
      color: #c62828;
      padding: 10px;
      border-radius: 5px;
      margin-top: 10px;
      margin-bottom: 20px;
      text-align: center;
      border: 1px solid #c62828;
    }
  </style>
</head>

<body>
  <div style="position: absolute; top: 10px; left: 10px; z-index: 2;">
    <a href="login.php" style="text-decoration: none; background-color: #1e3a8a; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold;">
      &larr; Back
    </a>
  </div>
  <div>
    <div class="title text-center mb-10">
      <img src="assets/img/bcp.png" alt="School Logo" width="100">
      <h2 class="mt-3">Select Campus Branch</h2>
      <p class="text">Choose your preferred branch for admission</p>
    </div>

    <div id="errorMessage" class="error-message">
      Please select a branch before proceeding.
    </div>

    <form method="POST" id="branchForm">
      <div class="row g-4">
        <!-- Main Branch Card -->
        <div class="col-md-6">
          <div class="branch-card card" onclick="selectBranch('main')">
            <div class="row g-0">
              <div class="col-md-5">
                <div class="branch-image">
                  <img src="image/bg.jpg" alt="Main Campus">
                </div>
              </div>
              <div class="col-md-7">
                <div class="branch-content">
                  <h4 class="text-primary mb-3">Main Branch</h4>
                  <div class="branch-address">
                    <p class="mb-1">#1071 Brgy. Kaligayahan</p>
                    <p class="mb-1">Quirino Highway</p>
                    <p>Novaliches, Quezon City</p>
                  </div>
                  <input type="radio" name="branch" value="Main" hidden>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bulacan Branch Card -->
        <div class="col-md-6">
          <div class="branch-card card" onclick="selectBranch('bulacan')">
            <div class="row g-0">
              <div class="col-md-5">
                <div class="branch-image">
                  <img src="image/bestlink.jpg" alt="Bulacan Campus">
                </div>
              </div>
              <div class="col-md-7">
                <div class="branch-content">
                  <h4 class="text-primary mb-3">Bulacan Branch</h4>
                  <div class="branch-address">
                    <p class="mb-1">Lot 1 Ipo Road, Brgy. Minuyan Proper</p>
                    <p class="mb-1">City of San Jose del Monte</p>
                    <p>Bulacan</p>
                  </div>
                  <input type="radio" name="branch" value="Bulacan" hidden>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center mt-5">
        <button type="submit" class="btn proceed-btn">
          Proceed to Admission Form
        </button>
      </div>
    </form>
  </div>

  <script>
    function selectBranch(type) {
      // Remove all selections
      document.querySelectorAll('.branch-card').forEach(card => {
        card.classList.remove('selected');
      });

      // Add selection to clicked card
      const card = event.currentTarget;
      card.classList.add('selected');

      // Check the radio input
      const radio = card.querySelector('input[type="radio"]');
      radio.checked = true;

      // Hide the error message when a branch is selected
      document.getElementById('errorMessage').style.display = 'none';
    }

    // Form submission handler
    document.getElementById('branchForm').addEventListener('submit', function(event) {
      const selectedBranch = document.querySelector('input[name="branch"]:checked');
      if (!selectedBranch) {
        event.preventDefault(); // Prevent form submission
        // Show the error message
        document.getElementById('errorMessage').style.display = 'block';
      }
    });
  </script>
</body>

</html>