<?php
session_start();
require('database.php');

//$timeoutMessage = '';
//if (isset($_GET['timeout']) && $_GET['timeout'] === 'true') {
//    $timeoutMessage = "Your session has expired due to inactivity. Please log in again.";
//}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
        $_SESSION['error_message'] = 'Invalid username format. Only letters, numbers, dots, and underscores are allowed.';
        header('Location: index.php');
        exit;
    }

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Please fill in both fields.';
        header('Location: index.php');
        exit;
    }

    // Function to check login in a specified table
    function checkLogin($conn, $table, $username, $password) {
        $sql = "SELECT * FROM $table WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            // If prepare() fails, display error message and exit
            die("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user is found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Password verification
            if (password_verify($password, $user['password'])) {
                return $user;
            } else {
                // Debugging output for password verification failure
                error_log("Password verification failed for user: " . $username);
            }
        } else {
            // Debugging output for user not found
            error_log("No user found with username: " . $username);
        }

        return false;
    }

    // Check in the `sms3_user` table for admin/staff roles
    $user = checkLogin($conn, 'sms3_user', $username, $password);

    if (!$user) {
        // Check in the `sms3_students` table for student role
        $user = checkLogin($conn, 'sms3_students', $username, $password);
    }

    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];

        // Redirect based on role
        switch ($user['role']) {
            case 'Admin':
                header("Location: admin/Dashboard.php");
                break;
            case 'Registrar':
                header("Location: registrar/Dashboard.php");
                break;
            case 'Superadmin':
                header("Location: Superdashboard.php");
                break;
            case 'Student':
                header("Location: student/Dashboard.php");
                break;
            default:
                header("Location: index.php"); // Redirect to a default page if role not matched
        }
        exit; // Ensure script stops after redirect
    } else {
        // If login fails, set an error message
        $_SESSION['error_message'] = 'Invalid username or password.';
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <link href="https://elc-public-images.s3.ap-southeast-1.amazonaws.com/bcp-olp-logo-mini2.png" rel="icon">

  <title>Login</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
</head>
<style>
    body {
    background-color: #f4f4f4;
    /*background-image: url('assets/img/bcp\ bg.jpg');*/
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    margin: 0;
}

.logo {
    text-align: center;
    margin-bottom: 5px;
}

.logo img {
    max-width: 30%;
    height: auto;
}

.logo p {
    margin-top: 10px;
    font-size: 1.2em;
    color: #333; 
}

.login-container {  
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 300px;
    text-align: center;
    border: 1px solid #999797;
    margin: 0 auto;
}

h2 {
    color: #333;
    margin-bottom: 20px;
}

label {
    display: block;
    text-align: left;
    color: #333;
    margin: 10px 0 5px;
}

#username, #password {
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border: 1px solid black;
}

input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

.forgot-password {
    text-align: right;
    margin-bottom: 20px;
}

.forgot-password a {
    color: #007BFF;
    text-decoration: none;
    font-size: 12px;
}

.forgot-password a:hover {
    text-decoration: underline;
}

button {
    background-color: #333;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    width: 100%;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

button:hover {
    background-color: #555;
}

.admission-button {
    display: block; /* Make it block level */
    background-color: #007BFF; /* Blue color for admission button */
    color: white;
    padding: 10px 0px; /* Match padding */
    border: none; /* No border */
    border-radius: 5px; /* Rounded corners */
    text-decoration: none; /* Remove underline */
    font-size: 16px; /* Match font size */
    width: 100%; /* Full width */
    cursor: pointer; /* Pointer cursor */
    margin-top: 10px; /* Space from the login button */
}

.admission-button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

.popup-message {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 15px;
        border-radius: 5px;
        font-size: 16px;
        color: #fff;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }
    .popup-message.success {
        background-color: green;
    }
    .popup-message.error {
        background-color: red;
    }
</style>
<body>
    <div class="logo">
        <img src="assets/img/bcp.png" alt="Logo">
        <p>Bestink College of the Philippines</p> 
    </div>
    
    <div class="login-container">
    <h2>Log Into Your Account</h2>
        <form id="loginForm" action="index.php" method="post">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required oninput="validateUsername(this)" pattern="[a-zA-Z0-9._]+" >

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">LOGIN</button>
            <a href="admission.php" class="admission-button">ADMISSION</a>
        </form>
    </div>

    <script>
        function validateUsername(input) {
        // Allow only letters, numbers, dots, and underscores
        input.value = input.value.replace(/[^a-zA-Z0-9._]/g, '');
        }

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

    // Trigger the popup based on the session message
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

</body>
</html>
