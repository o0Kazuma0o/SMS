<?php
require('../database.php');
require_once 'session.php';
checkAccess('Admin'); // Ensure only users with the 'admin' role can access this page

// Initialize the edit user variable
$edit_user = null;

// Edit user
if (isset($_GET['edit_user_id'])) {
    $edit_user_id = $_GET['edit_user_id'];

    // Fetch user details to pre-fill the form
    $stmt = $conn->prepare("SELECT * FROM sms3_user WHERE id = ?");
    $stmt->bind_param("i", $edit_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = $_POST['name'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    try {
        // Insert user if username and email are unique
        $stmt = $conn->prepare("INSERT INTO sms3_user (username, password, name, role, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $password, $name, $role, $phone, $email);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "User added successfully!";
        header('Location: manage_user.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = $e->getCode() === 1062 ? "Error: Duplicate entry for username or email." : "Error: " . $e->getMessage();
        header('Location: manage_user.php');
        exit;
    }
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    try {
        $stmt = $conn->prepare("UPDATE sms3_user SET username = ?, name = ?, role = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $username, $name, $role, $phone, $email, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "User updated successfully!";
        header('Location: manage_user.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = $e->getCode() === 1062 ? "Error: Duplicate entry for username or email." : "Error: " . $e->getMessage();
        header('Location: manage_user.php');
        exit;
    }
}

// Delete user
if (isset($_GET['delete_user_id'])) {
    $delete_id = $_GET['delete_user_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM sms3_user WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "User deleted successfully!";
        header('Location: manage_user.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = $e->getCode() === 1451 ? "Error: This user is still connected to other data." : "Error: " . $e->getMessage();
        header('Location: manage_user.php');
        exit;
    }
}

// Fetch all users
$users = $conn->query("SELECT * FROM sms3_user");
$sql = "SELECT id, username, name, role, phone, email FROM sms3_user";
$result = $conn->query($sql);
?>

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
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
        width: 300px;
    }
    .popup-message {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 15px;
        border-radius: 5px;
        color: #fff;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }
    .popup-message.success { background-color: green; }
    .popup-message.error { background-color: red; }
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

      <li class="nav-heading">Admission & Enrollment</li>

      <li class="nav-item">
        <a class="nav-link " href="admission.php">
          <i class="bi bi-grid"></i>
          <span>Admission</span>
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

      <li class="nav-heading">MANAGE USER</li>
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

      <hr class="sidebar-divider">
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div id="confirmationModal" class="modal">
      <div class="modal-content">
          <p id="confirmationMessage">Are you sure you want to delete this user?</p>
          <div class="modal-buttons">
              <button id="confirmDelete" class="btn btn-danger">Delete</button>
              <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
          </div>
      </div>
    </div>

    <section class="section dashboard">
    <div class="row">

      <!-- Add/Edit User Form -->
      <div class="card my-4">
        <div class="card-body">
          <h5 class="card-title"><?= isset($edit_user) ? 'Edit User' : 'Add User'; ?></h5>
          <form action="manage_user.php" method="POST" class="mb-4">
            <div class="form-group">
              <label for="username">Username:</label>
              <input type="text" class="form-control" name="username" id="username" required
              value="<?= isset($edit_user) ? $edit_user['username'] : ''; ?>">
            </div>
            <?php if (!isset($edit_user)): ?>
              <div class="form-group mt-2">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password" required>
              </div>
            <?php endif; ?>
            <div class="form-group mt-2">
              <label for="name">Name:</label>
              <input type="text" class="form-control" name="name" id="name" required
              value="<?= isset($edit_user) ? $edit_user['name'] : ''; ?>">
            </div>
            <div class="form-group mt-2">
              <label for="role">Role:</label>
              <select class="form-control" name="role" id="role" required>
                <option value="staff" <?= isset($edit_user) && $edit_user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="registrar" <?= isset($edit_user) && $edit_user['role'] == 'registrar' ? 'selected' : ''; ?>>Registrar</option>
                <option value="Admin" <?= isset($edit_user) && $edit_user['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="Superadmin" <?= isset($edit_user) && $edit_user['role'] == 'Superadmin' ? 'selected' : ''; ?>>Superadmin</option>
              </select>
            </div>
            <div class="form-group mt-2">
              <label for="phone">Phone:</label>
              <input type="text" class="form-control" name="phone" id="phone" 
              value="<?= isset($edit_user) ? $edit_user['phone'] : ''; ?>">
            </div>
            <div class="form-group mt-2">
              <label for="email">Email:</label>
              <input type="email" class="form-control" name="email" id="email" required
              value="<?= isset($edit_user) ? $edit_user['email'] : ''; ?>">
            </div>
            <?php if (isset($edit_user)): ?>
              <input type="hidden" name="user_id" value="<?= $edit_user['id']; ?>">
              <button type="submit" name="update_user" class="btn btn-warning mt-3">Update User</button>
            <?php else: ?>
              <button type="submit" name="add_user" class="btn btn-primary mt-3">Add User</button>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- User List Table -->
      <div class="card">
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;" class="card-body">
          <h5 class="card-title">User List</h5>
          <table style="width: 100%; min-width: 800px;" class="table datatable">
            <thead>
              <tr>
                <th>Username</th>
                <th>Name</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
              <tr>
                <td><?= $user['username']; ?></td>
                <td><?= $user['name']; ?></td>
                <td><?= $user['role']; ?></td>
                <td><?= $user['phone']; ?></td>
                <td><?= $user['email']; ?></td>
                <td>
                  <a href="manage_user.php?edit_user_id=<?= $user['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                  <a href="manage_user.php?delete_user_id=<?= $user['id']; ?>" class="btn btn-danger btn-sm delete-link" 
                      data-username="<?= $user['username']; ?>">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    </section>

  </main><!-- End #main -->

  <script>
    function showPopupMessage(message, type = 'success') {
        const popup = document.createElement('div');
        popup.className = `popup-message ${type}`;
        popup.innerText = message;
        document.body.appendChild(popup);

        popup.style.opacity = '1';
        setTimeout(() => {
            popup.style.opacity = '0';
            setTimeout(() => popup.remove(), 500);
        }, 3000);
    }

    // Confirmation modal for deletion
    document.querySelectorAll('.delete-link').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const deleteUrl = this.href;
            const username = this.getAttribute('data-username');

            const modal = document.getElementById('confirmationModal');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const confirmationMessage = document.getElementById('confirmationMessage');

            confirmationMessage.innerText = `Are you sure you want to delete the user: ${name}?`;
            modal.style.display = 'flex';

            confirmDeleteBtn.onclick = () => {
                window.location.href = deleteUrl;
                modal.style.display = 'none';
            };

            cancelDeleteBtn.onclick = () => modal.style.display = 'none';
        });
    });


    // Popup message function
    function showPopupMessage(message, type = 'success') {
        const popup = document.createElement('div');
        popup.className = `popup-message ${type}`;
        popup.innerText = message;
        document.body.appendChild(popup);

        popup.style.opacity = '1';
        setTimeout(() => {
            popup.style.opacity = '0';
            setTimeout(() => popup.remove(), 500);
        }, 3000);
    }

    // Show popup on load if a session message exists
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