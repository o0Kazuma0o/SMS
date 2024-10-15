<?php
require('database.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

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

            // Debugging output for checking if user is found
            error_log("User found: " . print_r($user, true));

            // Plain text password verification (not recommended for production)
            if ($user['password'] === $password) {
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
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: admin/Adashboard.php');
        } elseif ($user['role'] === 'staff') {
            header('Location: staff_dashboard.php');
        } elseif ($user['role'] === 'student') {
            header('Location: student_dashboard.php');
        } else {
            $_SESSION['error_message'] = 'Invalid user role.';
            header('Location: index.php');
        }
        exit;
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
</style>
<body>
    <div class="logo">
        <img src="assets/img/bcp.png" alt="Logo">
        <p>Bestink College of the Philippines</p> 
    </div>
    
    <div class="login-container">
    <h2>Log Into Your Account</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
        <form id="loginForm" action="index.php" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">LOGIN</button>
            <button type="submit">ADMISSION</button>
        </form>
    </div>

</body>
</html>
