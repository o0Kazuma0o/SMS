<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true, // Ensure you're using HTTPS
    'use_strict_mode' => true,
]);

require('database.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
        $_SESSION['error_message'] = 'Invalid username format. Only letters, numbers, dots, and underscores are allowed.';
        header('Location: login.php');
        exit;
    }

    // Ensure both fields are filled
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Please fill in both fields.';
        header('Location: login.php');
        exit;
    }

    // Function to check login in a specified table
    function checkLogin($conn, $table, $username, $password)
    {
        // Whitelist allowed table names
        $allowedTables = ['sms3_user', 'sms3_students'];
        if (!in_array($table, $allowedTables)) {
            die("Unauthorized table access.");
        }

        $nameField = $table === 'sms3_students'
            ? "CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS name"
            : "name";

        $sql = "SELECT *, $nameField FROM $table WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Database statement preparation failed: " . $conn->error);
            die("An error occurred. Please try again later.");
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user is found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    // Attempt login in different tables
    $user = checkLogin($conn, 'sms3_user', $username, $password);

    if (!$user) {
        $user = checkLogin($conn, 'sms3_students', $username, $password);
    }

    if ($user) {
        // Securely set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = htmlspecialchars($user['username']);
        $_SESSION['name'] = htmlspecialchars($user['name']);
        $_SESSION['role'] = htmlspecialchars($user['role']);
        $_SESSION['email'] = htmlspecialchars($user['email']);

        // Add student number for students
        if ($user['role'] === 'Student') {
            $_SESSION['student_number'] = htmlspecialchars($user['student_number']);
        }

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
            case 'Staff':
                header("Location: staff/Dashboard.php");
                break;
            case 'Student':
                header("Location: student/Dashboard.php");
                break;
            default:
                header("Location: login.php");
        }
        exit;
    } else {
        $_SESSION['error_message'] = 'Invalid username or password.';
        header('Location: login.php');
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

    #username,
    #password {
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
        display: block;
        /* Make it block level */
        background-color: #007BFF;
        /* Blue color for admission button */
        color: white;
        padding: 10px 0px;
        /* Match padding */
        border: none;
        /* No border */
        border-radius: 5px;
        /* Rounded corners */
        text-decoration: none;
        /* Remove underline */
        font-size: 16px;
        /* Match font size */
        width: 100%;
        /* Full width */
        cursor: pointer;
        /* Pointer cursor */
        margin-top: 10px;
        /* Space from the login button */
    }

    .admission-button:hover {
        background-color: #0056b3;
        /* Darker blue on hover */
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
        <form id="loginForm" action="login.php" method="post">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required oninput="validateUsername(this)" pattern="[a-zA-Z0-9._]+">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">LOGIN</button>
            <a href="verify_email.php" class="admission-button">ADMISSION</a>
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